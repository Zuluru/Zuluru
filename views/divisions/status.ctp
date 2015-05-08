<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Status Report', true));
?>

<div class="divisions status_report">
<h2><?php  echo __('Status Report', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
if ($playoffs_included) {
	echo $this->Html->para('warning-message', __('Note that this report includes only regular season games.', true));
}
?>
<table class="list tablesorter">
<thead>
<tr>
	<th class="header" rowspan="2"><?php __('Team'); ?></th>
	<th class="header" rowspan="2"><?php __('Home %'); ?></th>
<?php if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')): ?>
	<th class="header" rowspan="2"><?php __('Preference %'); ?></th>
<?php endif; ?>
	<th class="sorter-false" colspan="<?php echo count($regions_used) + 2; ?>"><?php __('Games Played'); ?></th>
	<th class="header" rowspan="2"><?php __('Opponents'); ?></th>
	<th class="sorter-false" rowspan="2"><?php __('Repeat Opponents'); ?></th>
</tr>

<tr>
	<th class="header"><?php __('Total'); ?></th>
	<th class="header"><?php __('Home'); ?></th>
	<?php foreach (array_keys($regions_used) as $region_id): ?>
	<th class="header"><?php __($regions[$region_id]); ?></th>
	<?php endforeach; ?>
</tr>
</thead>

<tbody>
<?php
$total = 0;
foreach ($division['Team'] as $team):
	$team_stats = $stats[$team['id']];
?>
	<tr>
		<td><?php echo $this->element('teams/block', array('team' => $team, 'show_shirt' => false, 'options' => array('max_length' => 16))); ?></td>
		<td><?php
		if ($team_stats['games'] > 0) {
			$ratio = sprintf('%.1f', round($team_stats['home_games'] * 100 / $team_stats['games'], 1));
			// When checking for a home games deficit, we don't want to flag it if the deficit
			// can be made up in a single game, i.e. there's no way for it to be exactly 50%
			// and they're just one game on the wrong side of it. So, when the game total is
			// odd, we want to add one to both for the comparison. As it happens, adding one
			// to both when it's an even total can't possibly change whether we're under 50%,
			// so we'll just do it that way all the time.
			$check_ratio = ($team_stats['home_games'] + 1) / ($team_stats['games'] + 1);
			if ($check_ratio < 0.5) {
				echo $this->Html->tag('span', $ratio, array('class' => 'warning-message'));
			} else {
				echo $ratio;
			}
		}
		?></td>
	<?php if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')): ?>
		<td><?php
		if ($team_stats['games'] > 0) {
			$ratio = sprintf('%.1f', round($team_stats['field_rank'] * 100 / $team_stats['games'], 1));
			if ($ratio < 50) {
				echo $this->Html->tag('span', $ratio, array('class' => 'warning-message'));
			} else {
				echo $ratio;
			}
			$total += $ratio;
		}
		?></td>
	<?php endif; ?>
		<td><?php echo $team_stats['games']; ?></td>
		<td><?php echo $team_stats['home_games']; ?></td>
	<?php foreach (array_keys($regions_used) as $region_id): ?>
		<td><?php if (array_key_exists($region_id, $team_stats['region_games'])) echo $team_stats['region_games'][$region_id]; ?></td>
	<?php endforeach; ?>
		<td><?php echo count($team_stats['opponents']); ?></td>
		<td><?php
		$repeats = array();
		foreach ($team_stats['opponents'] as $opponent_id => $count) {
			if ($count > 2) {
				$repeats[] = $this->Html->tag('span',
						$this->element('teams/block', array('team' => $division['Team'][$opponent_id], 'show_shirt' => false, 'options' => array('max_length' => 16))),
						array('class' => 'warning-message'));
			} else if ($count > 1) {
				$repeats[] = $this->element('teams/block', array('team' => $division['Team'][$opponent_id], 'show_shirt' => false, 'options' => array('max_length' => 16)));
			}
		}
		echo implode(',<br />', $repeats);
		?></td>
	</tr>
<?php
endforeach;

if (Configure::read('feature.facility_preference') || Configure::read('feature.region_preference')):
?>
<tr>
	<td><?php __('Average:'); ?></td>
	<td></td>
	<td><?php printf('%.1f', round($total / count($division['Team']), 1)); ?></td>
	<td colspan="<?php echo count($regions_used) + 4; ?>"></td>
</tr>
<?php
endif;
?>
</tbody>
</table>
</div>

<?php
// Make the table sortable
$this->ZuluruHtml->script (array('jquery.tablesorter.min.js'), array('inline' => false));
$this->ZuluruHtml->css('jquery.tablesorter.css', null, array('inline' => false));
$this->Js->buffer ("
	jQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});
");
?>
