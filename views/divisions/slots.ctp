<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb (sprintf(__('Division %s Availability Report', true), Configure::read('sport.field_cap')));
$this->Html->addCrumb ($division['Division']['full_league_name']);
?>

<div class="divisions slots">
<h2><?php echo sprintf(__('Division %s Availability Report', true), Configure::read('sport.field_cap')) . ': ' . $division['Division']['full_league_name'];?></h2>

<p>Select a date below on which to view all available gameslots:</p>
<?php
echo $this->Form->create(false, array('url' => Router::normalize($this->here)));
echo $this->Form->input('date', array(
		'label' => false,
		'options' => $dates,
));
echo $this->Js->submit(__('View', true), array('url' => Router::normalize($this->here), 'update' => '#SlotResults'));
echo $this->Form->end();
?>

<div id="SlotResults">
<?php endif; ?>

<?php if (isset ($slots)): ?>
<p><?php echo $this->ZuluruTime->fulldate($date); ?></p>
<table class="list">
	<tr>
		<th>ID</th>
		<th><?php __(Configure::read('sport.field_cap')); ?></th>
		<th>Start Time</th>
		<th>Game</th>
		<th>Division</th>
<?php if ($is_tournament): ?>
		<th>Pool</th>
<?php endif; ?>
		<th>Home</th>
		<th>Away</th>
		<th><?php __(Configure::read('sport.field_cap')); ?> Region</th>
<?php if (Configure::read('feature.region_preference')): ?>
		<th>Home Pref</th>
<?php endif; ?>
	</tr>
<?php $unused = 0; ?>
<?php foreach ($slots as $slot): ?>
	<tr>
		<td><?php __($slot['GameSlot']['id']); ?></td>
		<td><?php echo $this->element('fields/block', array('field' => $slot['Field'])); ?></td>
		<td><?php echo $this->ZuluruTime->time ($slot['GameSlot']['game_start']); ?></td>
<?php if (!$slot['Game']['id']): ?>
<?php ++$unused; ?>
		<td colspan="4">---- <?php printf(__('%s open', true), Configure::read('sport.field')); ?> ----</td>
<?php else:
		Game::_readDependencies($slot['Game']);
?>
		<td><?php echo $this->Html->link ($slot['Game']['id'],
				array('controller' => 'games', 'action' => 'view', 'game' => $slot['Game']['id'])); ?></td>
		<td><?php echo $slot['Game']['Division']['name']; ?></td>
<?php if ($is_tournament): ?>
		<td><?php
		echo $slot['Game']['Pool']['name'];
		if ($slot['Game']['Pool']['type'] != 'crossover') {
			echo " (round&nbsp;{$slot['Game']['round']})";
		}
		?></td>
<?php endif; ?>
		<td><?php
		if (empty($slot['Game']['home_team'])) {
			echo $slot['Game']['home_dependency'];
		} else {
			echo $this->element('teams/block', array('team' => $slot['Game']['HomeTeam'], 'max_length' => 16, 'show_shirt' => false));
		}
		?></td>
		<td><?php
		if (empty($slot['Game']['away_team'])) {
			echo $slot['Game']['away_dependency'];
		} else {
			echo $this->element('teams/block', array('team' => $slot['Game']['AwayTeam'], 'max_length' => 16, 'show_shirt' => false));
		}
		?></td>
<?php endif; ?>
		<td><?php __($slot['Field']['Facility']['Region']['name']); ?></td>
<?php if (Configure::read('feature.region_preference')): ?>
		<td><?php
		if ($slot['Game']['id'] && !empty($slot['Game']['HomeTeam']['Region'])) {
			__($slot['Game']['HomeTeam']['Region']['name']);
		}
		?></td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
</table>
<?php printf (__('There are %s %s available for use this week, currently %s of these are unused.', true), count($slots), Configure::read('sport.fields'), $unused); ?>
<?php endif; ?>

<?php if (!$this->params['isAjax']): ?>
</div>

</div>
<?php endif; ?>
