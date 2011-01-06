<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Field Distribution Report', true));
?>

<div class="leagues fields">
<h2><?php  echo __('Field Distribution Report', true) . ': ' . $league['League']['long_name'];?></h2>
<table>
<thead>
<?php // TODO: Use a league element ?>
<tr>
	<th rowspan="2"><?php __('Team'); ?></th>
	<th rowspan="2"><?php __('Rating'); ?></th>
<?php
$region_prefs = Configure::read('feature.region_preference');
if ($region_prefs) :
?>
	<th rowspan="2"><?php __('Region Preference', true); ?></th>

<?php
endif;

$count = 0;
$last_region = null;
foreach ($fields as $field) {
$heading[] = $this->Html->link ($field['Field']['code'],
					array('controller' => 'fields', 'action' => 'view', 'field' => $field['Field']['id']),
					array('title' => $field['Field']['name'])) .
			' ' . $this->ZuluruTime->time ($field['GameSlot']['game_start']);
	if ($last_region == $field['Region']['name']) {
		++ $count;
	} else {
		if ($count) {
			echo $this->Html->tag ('th', $last_region, array('colspan' => $count));
		}
		$last_region = $field['Region']['name'];
		$count = 1;
	}
}
if ($count) {
	echo $this->Html->tag ('th', __($last_region, true), array('colspan' => $count));
}
?>
	<th rowspan="2"><?php __('Games', true); ?></th>
</tr>

<?php echo $this->Html->tableHeaders ($heading); ?>
</thead>
<tbody>

<?php
// Count number of games per field for each team
$team_count = array();
$field_count = array();
foreach ($league['Game'] as $game) {
	foreach (array('home_team', 'away_team') as $team) {
		if (!array_key_exists ($game[$team], $team_count)) {
			$team_count[$game[$team]] = array();
		}
		if (!array_key_exists ($game['GameSlot']['Field']['code'], $team_count[$game[$team]])) {
			$team_count[$game[$team]][$game['GameSlot']['Field']['code']] = array();
		}
		if (!array_key_exists ($game['GameSlot']['game_start'], $team_count[$game[$team]][$game['GameSlot']['Field']['code']])) {
			$team_count[$game[$team]][$game['GameSlot']['Field']['code']][$game['GameSlot']['game_start']] = 0;
		}
		++ $team_count[$game[$team]][$game['GameSlot']['Field']['code']][$game['GameSlot']['game_start']];
	}
	if (!array_key_exists ($game['GameSlot']['Field']['code'], $field_count)) {
		$field_count[$game['GameSlot']['Field']['code']] = array();
	}
	if (!array_key_exists ($game['GameSlot']['game_start'], $field_count[$game['GameSlot']['Field']['code']])) {
		$field_count[$game['GameSlot']['Field']['code']][$game['GameSlot']['game_start']] = 0;
	}
	++ $field_count[$game['GameSlot']['Field']['code']][$game['GameSlot']['game_start']];
}
$numteams = count ($team_count);

$rows = array();
foreach ($league['Team'] as $team) {
	$id = $team['id'];
	$row = array ($this->Html->link ($team['name'], array('controller' => 'teams', 'action' => 'view', 'id' => $team['id'])),
					$team['rating']);
	if ($region_prefs) {
		$row[] = $team['region_preference'];
	}

	$total = 0;
	foreach ($fields as $field) {
		if (array_key_exists ($field['Field']['code'], $team_count[$id]) &&
			array_key_exists ($field['GameSlot']['game_start'], $team_count[$id][$field['Field']['code']]))
		{
			$games = $team_count[$id][$field['Field']['code']][$field['GameSlot']['game_start']];
		} else {
			$games = 0;
		}
		$total += $games;

		if (array_key_exists ($field['Field']['code'], $field_count) &&
			array_key_exists ($field['GameSlot']['game_start'], $field_count[$field['Field']['code']]))
		{
			$avg = $field_count[$field['Field']['code']][$field['GameSlot']['game_start']] / $numteams * 2;
		} else {
			$avg = 0;
		}

		if (abs ($avg - $games) > 1.5) {
			$row[] = array($games, array('class' => 'error-message'));
		} else {
			$row[] = $games;
		}
	}
	$row[] = $total;
	$rows[] = $row;
}

// Output totals line
$total_row = array(array(__('Total games', true), array('colspan' => 2)));
$avg_row = array(array(__('Average', true), array('colspan' => 2)));
foreach ($fields as $field) {
	if (array_key_exists ($field['Field']['code'], $field_count) &&
		array_key_exists ($field['GameSlot']['game_start'], $field_count[$field['Field']['code']]))
	{
		$total = $field_count[$field['Field']['code']][$field['GameSlot']['game_start']];
	} else {
		$total = 0;
	}
	$total_row[] = $total;
	$avg_row[] = sprintf ('%0.1f', $total / $numteams * 2);	// Each game has 2 teams participating
}
$total = $total_row[] = array_sum ($total_row);
$avg_row[] = sprintf ('%0.1f', $total / $numteams * 2);
$rows[] = $total_row;
$rows[] = $avg_row;

echo $this->Html->tableCells ($rows, array(), array('class' => 'altrow'));
?>
</tbody>
<?php
array_unshift ($heading, __('Rating', true));
array_unshift ($heading, __('Team', true));

echo $this->Html->tableHeaders ($heading);
?>
</table>

</div>
