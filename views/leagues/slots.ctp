<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (sprintf(__('League %s Availability Report', true), Configure::read('sport.field_cap')));
$this->Html->addCrumb ($league['League']['full_name']);
?>

<div class="leagues slots">
<h2><?php echo sprintf(__('League %s Availability Report', true), Configure::read('sport.field_cap')) . ': ' . $league['League']['full_name'];?></h2>

<p><?php __('Select a date below on which to view all available gameslots:'); ?></p>
<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
echo $this->Form->input('date', array(
		'label' => false,
		'options' => $dates,
));
$spinner = $this->ZuluruHtml->icon('spinner.gif');
echo $this->Js->submit(__('View', true), array(
		'url' => Router::normalize($this->here),
		'update' => '#SlotResults',
		'beforeSend' => "jQuery('#SlotResults').html('$spinner');",
));
echo $this->Form->end();
?>

<div id="SlotResults">
<?php endif; ?>

<?php if (isset ($slots)): ?>
<p><?php echo $this->ZuluruTime->fulldate($date); ?></p>
<table class="list">
	<?php
	$schedule_types = array_unique(Set::extract('/Division/schedule_type', $league));
	$competition = (count($schedule_types) == 1 && $schedule_types[0] == 'competition');
	?>
	<tr>
		<th><?php __('ID'); ?></th>
		<th><?php __(Configure::read('sport.field_cap')); ?></th>
		<th><?php printf(__('%s Region', true), __(Configure::read('sport.field_cap'))); ?></th>
		<th><?php __('Start Time'); ?></th>
		<th><?php __('Game'); ?></th>
		<th><?php __('Division'); ?></th>
<?php if ($is_tournament): ?>
		<th><?php __('Pool'); ?></th>
<?php endif; ?>
		<th><?php echo $competition ? __('Team', true) : __('Home', true); ?></th>
<?php if (!$competition): ?>
		<th><?php __('Away'); ?></th>
<?php endif; ?>
<?php if (Configure::read('feature.region_preference')): ?>
		<th><?php __('Home Pref'); ?></th>
<?php endif; ?>
	</tr>
<?php
$unused = 0;
foreach ($slots as $slot):
	$rows = max(count($slot['Game']), 1);
	$cols = 3 + $is_tournament + !$competition + Configure::read('feature.region_preference');
?>
	<tr>
		<td rowspan="<?php echo $rows; ?>"><?php echo $slot['GameSlot']['id']; ?></td>
		<td rowspan="<?php echo $rows; ?>"><?php echo $this->element('fields/block', array('field' => $slot['Field'])); ?></td>
		<td rowspan="<?php echo $rows; ?>"><?php __($slot['Field']['Facility']['Region']['name']); ?></td>
		<td rowspan="<?php echo $rows; ?>"><?php echo $this->ZuluruTime->time ($slot['GameSlot']['game_start']); ?></td>
<?php if (empty($slot['Game'])): ?>
<?php ++$unused; ?>
		<td colspan="<?php echo $cols; ?>">---- <?php printf(__('%s open', true), Configure::read('sport.field')); ?> ----</td>
<?php else:
		$first = true;
		foreach ($slot['Game'] as $game) {
			Game::_readDependencies($game);
			if (!$first) {
				echo '<tr>';
			}
?>
		<td><?php echo $this->Html->link($game['id'],
					array('controller' => 'games', 'action' => 'view', 'game' => $game['id'])); ?></td>
		<td><?php echo $this->element('divisions/block', array('division' => $game['Division'])); ?></td>
<?php if ($is_tournament): ?>
		<td><?php
			echo $game['Pool']['name'];
			if ($game['Pool']['type'] != 'crossover') {
				printf(__(' (round&nbsp;%s)', true), $game['round']);
			}
		?></td>
<?php endif; ?>
		<td><?php
			if (empty($game['home_team'])) {
				if (array_key_exists ('home_dependency', $game)) {
					echo $game['home_dependency'];
				} else {
					__('Unassigned');
				}
			} else {
				echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'max_length' => 16, 'show_shirt' => false));
			}
		?></td>
<?php if (!$competition): ?>
		<td><?php
			if (empty($game['away_team'])) {
				if (array_key_exists ('away_dependency', $game)) {
					echo $game['away_dependency'];
				} else {
					__('Unassigned');
				}
			} else {
				echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'max_length' => 16, 'show_shirt' => false));
			}
		?></td>
<?php endif; ?>
<?php if (Configure::read('feature.region_preference')): ?>
		<td><?php
		if ($game['id'] && !empty($game['HomeTeam']['Region'])) {
			__($game['HomeTeam']['Region']['name']);
		}
		?></td>
<?php endif; ?>
<?php
			if ($first) {
				$first = false;
			} else {
				echo '</tr>';
			}
		}
	endif; ?>
	</tr>
<?php endforeach; ?>
</table>
<?php printf (__('There are %s %s available for use, currently %s of these are unused.', true), count($slots), Configure::read('sport.fields'), $unused); ?>
<?php endif; ?>

<?php if (!$this->params['isAjax']): ?>
</div>

</div>
<?php endif; ?>
