<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Field Distribution Report', true));
?>

<div class="leagues field_distribution">
<h2><?php  echo __('Field Distribution Report', true) . ': ' . $league['League']['long_name'];?></h2>
<?php
if (isset ($published)) {
	echo $this->Html->para(null,
			sprintf(__('This report includes only games that are published. You may also see it %s.', true),
				$this->Html->link(__('including all games', true), array('action' => 'fields', 'league' => $league['League']['id'])))
	);
} else {
	echo $this->Html->para(null,
		sprintf(__('This report includes all games. You may also see it %s.', true),
				$this->Html->link(__('including only games that are published', true), array('action' => 'fields', 'league' => $league['League']['id'], 'published' => true)))
	);
}
?>
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

$regions = count (array_unique (Set::extract ('/Region/name', $fields)));
$count = 0;
$last_region = null;
foreach ($fields as $field) {
	if ($last_region == $field['Region']['name']) {
		++ $count;
	} else {
		if ($count) {
			if ($regions > 1) {
				$heading[] = __('Sub total', true);
				++ $count;
			}
			echo $this->Html->tag ('th', $last_region, array('colspan' => $count));
		}
		$last_region = $field['Region']['name'];
		$count = 1;
	}
	$heading[] = $this->Html->link ($field['Field']['code'],
					array('controller' => 'fields', 'action' => 'view', 'field' => $field['Field']['id']),
					array('title' => $field['Field']['name'])) .
			' ' . $this->ZuluruTime->time ($field['GameSlot']['game_start']);
}
if ($count) {
	if ($regions > 1) {
		$heading[] = __('Sub total', true);
		++ $count;
	}
	echo $this->Html->tag ('th', __($last_region, true), array('colspan' => $count));
}
?>
	<th rowspan="2"><?php __('Total'); ?></th>
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
	$row = array ($this->Html->link ($team['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['id'])),
					$team['rating']);
	if ($region_prefs) {
		$row[] = $team['region_preference'];
	}

	$last_region = null;
	$total = 0;
	foreach ($fields as $field) {
		if ($regions > 1 && $last_region != $field['Region']['name']) {
			if ($last_region !== null) {
				$row[] = array($region_total, array('class' => 'sub-total'));
			}
			$region_total = 0;
			$last_region = $field['Region']['name'];
		}

		if (array_key_exists ($id, $team_count) &&
			array_key_exists ($field['Field']['code'], $team_count[$id]) &&
			array_key_exists ($field['GameSlot']['game_start'], $team_count[$id][$field['Field']['code']]))
		{
			$games = $team_count[$id][$field['Field']['code']][$field['GameSlot']['game_start']];
		} else {
			$games = 0;
		}
		$total += $games;
		if ($regions > 1) {
			$region_total += $games;
		}

		if (array_key_exists ($field['Field']['code'], $field_count) &&
			array_key_exists ($field['GameSlot']['game_start'], $field_count[$field['Field']['code']]))
		{
			$avg = $field_count[$field['Field']['code']][$field['GameSlot']['game_start']] / $numteams * 2;
		} else {
			$avg = 0;
		}

		if (abs ($avg - $games) > 1.5) {
			$row[] = array($games, array('class' => 'field-usage-highlight'));
		} else {
			$row[] = $games;
		}
	}

	if ($regions > 1) {
		$row[] = array($region_total, array('class' => 'sub-total'));
	}

	$row[] = $total;
	$rows[] = $row;
}

// Output totals line
$total_row = array(array(__('Total games', true), array('colspan' => 2)));
$avg_row = array(array(__('Average', true), array('colspan' => 2)));
$region_total = 0;
$last_region = null;
foreach ($fields as $field) {
	if ($regions > 1 && $last_region != $field['Region']['name']) {
		if ($last_region !== null) {
			$total_row[] = array($region_total, array('class' => 'sub-total'));
			$avg_row[] = array(sprintf ('%0.1f', $region_total / $numteams * 2), array('class' => 'sub-total'));	// Each game has 2 teams participating
		}
		$region_total = 0;
		$last_region = $field['Region']['name'];
	}

	if (array_key_exists ($field['Field']['code'], $field_count) &&
		array_key_exists ($field['GameSlot']['game_start'], $field_count[$field['Field']['code']]))
	{
		$total = $field_count[$field['Field']['code']][$field['GameSlot']['game_start']];
	} else {
		$total = 0;
	}
	$region_total += $total;
	$total_row[] = $total;
	$avg_row[] = sprintf ('%0.1f', $total / $numteams * 2);
}

if ($regions > 1) {
	$total_row[] = array($region_total, array('class' => 'sub-total'));
	$avg_row[] = array(sprintf ('%0.1f', $region_total / $numteams * 2), array('class' => 'sub-total'));
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
$heading[] = __('Total', true);

echo $this->Html->tableHeaders ($heading);
?>
</table>

</div>
