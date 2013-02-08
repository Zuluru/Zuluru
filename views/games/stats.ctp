<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Game', true) . ' ' . $game['Game']['id']);
if (isset($team_id)) {
	$this->Html->addCrumb ($team['name']);
}
$this->Html->addCrumb (__('Stats', true));
?>

<div class="games stats">
<h2><?php __('Game Stats');?></h2>

<dl><?php $i = 0; $class = ' class="altrow"';?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('League', true) . '/' . __('Division', true); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->Html->link($game['Division']['full_league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $game['Division']['id'])); ?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Home Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->element('teams/block', array('team' => $game['HomeTeam']));
		if (array_key_exists ('home_dependency', $game['Game'])) {
			echo " ({$game['Game']['home_dependency']})";
		}
		?>

	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Away Team'); ?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->element('teams/block', array('team' => $game['AwayTeam']));
		if (array_key_exists ('away_dependency', $game['Game'])) {
			echo " ({$game['Game']['away_dependency']})";
		}
		?>

	</dd>
<?php if (Game::_is_finalized($game)): ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Score');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->ZuluruGame->displayScore($game, $game['Division']['League']); ?>

	</dd>
<?php endif; ?>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date and Time');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php
		echo $this->ZuluruTime->date ($game['GameSlot']['game_date']) . ', ' .
			$this->ZuluruTime->time ($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time ($game['GameSlot']['display_game_end']);
		?>
	</dd>
	<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Location');?></dt>
	<dd<?php if ($i++ % 2 == 0) echo $class;?>>
		<?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'], 'display_field' => 'long_name')); ?>

	</dd>
</dl>
</div>

<div class="related">
<?php
if (isset($team_id)) {
	$teams = array($team);
} else {
	$teams = array($team, $opponent);
}
foreach ($teams as $team):
	if (!isset($team_id)) {
		echo $this->Html->tag('h3', $team['name']);
	}
?>
	<table class="list tablesorter">
	<thead>
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Gender'); ?></th>
<?php foreach ($game['Division']['League']['StatType'] as $stat_type): ?>
		<th class="<?php echo $stat_type['class']; ?>"><?php echo $this->Html->tag('span', __($stat_type['abbr'], true), array('title' => $stat_type['name'])); ?></th>
<?php endforeach; ?>
	</tr>
	</thead>
	<tbody>
	<?php
		$totals = array_fill_keys(Set::extract('/Division/League/StatType/id', $game), 0);
		foreach ($team['Person'] as $person):
			$person_stats = Set::extract("/Stat[person_id={$person['Person']['id']}]", $game);
			if (empty($person_stats)) {
				continue;
			}
	?>
	<tr>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td><?php __($person['Person']['gender']); ?></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat_type): ?>
		<td class="<?php echo $stat_type['class']; ?>"><?php
			$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
			if (!empty($value)) {
				echo $value[0];
				if (array_key_exists($stat_type['id'], $totals)) {
					$totals[$stat_type['id']] += $value[0];
				}
			} else {
				echo 0;
			}
		?></td>
<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
	<?php
	$person_stats = Set::extract("/Stat[person_id=0][team_id={$team['id']}]", $game);
	if (!empty($person_stats)):
	?>
	<tr>
		<td><?php __('Subs'); ?></td>
		<td></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat_type): ?>
		<td class="<?php echo $stat_type['class']; ?>"><?php
			$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
			if (!empty($value)) {
				echo $value[0];
				if (array_key_exists($stat_type['id'], $totals)) {
					$totals[$stat_type['id']] += $value[0];
				}
			} else {
				echo 0;
			}
		?></td>
<?php endforeach; ?>
	</tr>
<?php endif; ?>
	</tbody>
	<tfoot>
	<tr>
		<td><?php __('Total'); ?></td>
		<td></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat_type): ?>
		<td><?php
			if (array_key_exists($stat_type['id'], $totals)) {
				if (!empty($totals[$stat_type['id']])) {
					echo $totals[$stat_type['id']];
				} else {
					echo 0;
				}
			}
		?></td>
<?php endforeach; ?>
	</tr>
	</tfoot>
</table>
<?php endforeach; ?>

</div>

<?php
// Make the table sortable
$this->ZuluruHtml->script (array('jquery.tablesorter.min'), array('inline' => false));
$this->ZuluruHtml->css('jquery.tablesorter', null, array('inline' => false));
$this->Js->buffer ("
	jQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});
");
?>
