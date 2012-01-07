<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Spirit Report', true));
?>

<div class="divisions spirit">
<h3><?php  echo __('Spirit Report', true) . ': ' . $division['Division']['full_league_name'];?></h3>

<?php
$rows = $team_records = array();
$questions = array('entered_sotg', 'assigned_sotg');
foreach ($spirit_obj->questions as $question => $detail) {
	if ($detail['type'] != 'text') {
		$questions[] = $question;
	}
}

foreach ($division['Game'] as $game) {
	foreach (array('HomeTeam', 'AwayTeam') as $team) {
		$id = $game[$team]['id'];
		if (!array_key_exists ($id, $team_records)) {
			$team_records[$id] = array(
				'details' => $game[$team],
				'summary' => array_fill_keys ($questions, null),
				'games' => 0,
			);
		}
		// TODO: Pull the code from downloadable version here, to handle defaults, etc.
		foreach ($game['SpiritEntry'] as $entry) {
			if ($entry['team_id'] == $id) {
				$entry['assigned_sotg'] = $spirit_obj->calculate ($entry);
				++ $team_records[$id]['games'];
				foreach ($questions as $question) {
					$team_records[$id]['summary'][$question] += $entry[$question];
				}
			}
		}
	}
}
$team_count = count($team_records);

foreach ($team_records as $id => $team) {
	if ($team['games'] > 0) {
		$team_records[$id]['summary']['entered_sotg'] /= $team['games'];
		$team_records[$id]['summary']['assigned_sotg'] /= $team['games'];
	}
}

function compareSpirit($a,$b) {
	if ($a['summary']['entered_sotg'] > $b['summary']['entered_sotg']) {
		return -1;
	} else if ($a['summary']['entered_sotg'] < $b['summary']['entered_sotg']) {
		return 1;
	} else if ($a['summary']['assigned_sotg'] > $b['summary']['assigned_sotg']) {
		return -1;
	} else if ($a['summary']['assigned_sotg'] < $b['summary']['assigned_sotg']) {
		return 1;
	} else if ($a['details']['name'] < $b['details']['name']) {
		return -1;
	} else if ($a['details']['name'] > $b['details']['name']) {
		return 1;
	}
	return 0;
}
usort ($team_records, 'compareSpirit');
?>

<h3><?php __('Team Spirit Summary'); ?></h3>

<?php
$header = array(__('Team', true), __('Average Spirit', true), __('Assigned Spirit', true));
foreach ($spirit_obj->questions as $question => $detail) {
	if ($detail['type'] != 'text') {
		$header[] = $detail['name'];
	}
}

$rows = $overall = array();
foreach ($team_records as $team) {
	$row = array(
		$this->element('teams/block', array('team' => $team['details'])),
		$this->element ('spirit/symbol', array(
				'spirit_obj' => $spirit_obj,
				'type' => $division['League']['display_sotg'],
				'is_coordinator' => true,	// only ones allowed to even run this report
				'value' => $team['summary']['entered_sotg'],
		)),
		$this->element ('spirit/symbol', array(
				'spirit_obj' => $spirit_obj,
				'type' => $division['League']['display_sotg'],
				'is_coordinator' => true,
				'value' => $team['summary']['assigned_sotg'],
		)),
	);
	$overall['entered_sotg'][] = $team['summary']['entered_sotg'];
	$overall['assigned_sotg'][] = $team['summary']['assigned_sotg'];

	// This is to avoid divide-by-zero errors. No harm, since the numerators
	// will all be 0 as well if they didn't have any games...
	if ($team['games'] == 0) {
		$team['games'] = 1;
	}

	foreach ($spirit_obj->questions as $question => $detail) {
		if ($detail['type'] != 'text') {
			$row[] = $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'type' => $division['League']['display_sotg'],
					'question' => $question,
					'is_coordinator' => true,	// only ones allowed to even run this report
					'value' => $team['summary'][$question] / $team['games'],
			));
			$overall[$question][] = $team['summary'][$question] / $team['games'];
		}
	}
	$rows[] = $row;
}

$average = array(array(__('Division average', true), array('class' => 'summary')));
$stddev = array(array(__('Division std dev', true), array('class' => 'summary')));
foreach ($overall as $question => $col) {
	$average[] = array($this->element ('spirit/symbol', array(
			'spirit_obj' => $spirit_obj,
			'type' => $division['League']['display_sotg'],
			'question' => $question,
			'is_coordinator' => true,	// only ones allowed to even run this report
			'value' => array_sum ($col) / $team_count,
	)), array('class' => 'summary'));
	$stddev[] = array(sprintf ('%0.2f', stats_standard_deviation ($col)), array('class' => 'summary'));
}
$rows[] = $average;
$rows[] = $stddev;

echo $this->Html->tag ('table', $this->Html->tableHeaders ($header) . $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));

$bins = array_count_values (array_map ('intval', $overall['entered_sotg']));
?>

<h2><?php __('Distribution of team average spirit scores'); ?></h2>

<?php
$header = array(__('Spirit score', true), __('Number of teams', true), __('Percentage of division', true));

$max = $spirit_obj->max();
$rows = array(array ($max, @$bins[$max], floor (@$bins[$max] / $team_count * 100)));
for ($i = $max-1; $i >= 0; --$i) {
	$rows[] = array ($i . '-' . ($i + 1), @$bins[$i], floor (@$bins[$i] / $team_count * 100));
}

echo $this->Html->tag ('table', $this->Html->tableHeaders ($header) . $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>

<h2><?php __('Spirit reports per game'); ?></h2>

<?php
// TODO: Include score column only if numeric scoring is on,
// include other columns including calculation if survey is on
$header = array(
		__('Game', true),
		__('Entry By', true),
		__('Given To', true),
		__('Score', true),
);

foreach ($spirit_obj->questions as $detail) {
	if ($detail['type'] != 'text') {
		$header[] = __($detail['name'], true);
	}
}

$rows = array();
foreach ($division['Game'] as $game) {
	foreach (array('HomeTeam' => 'AwayTeam', 'AwayTeam' => 'HomeTeam') as $team => $opp) {
		foreach ($game['SpiritEntry'] as $entry) {
			if ($entry['created_team_id'] == $game[$team]['id']) {
				$row = array(
						$this->Html->link ($game['id'], array('controller' => 'games', 'action' => 'view', 'game' => $game['id'])) . ' ' .
							$this->ZuluruTime->date ($game['GameSlot']['game_date']),
						$this->Html->link ($game[$team]['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game[$team]['id'])),
						$this->Html->link ($game[$opp]['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game[$opp]['id'])),
						$entry['entered_sotg'],
				);
				foreach ($spirit_obj->questions as $question => $detail) {
					if ($detail['type'] != 'text') {
						$row[] = $this->element ('spirit/symbol', array(
								'spirit_obj' => $spirit_obj,
								'type' => $division['League']['display_sotg'],
								'question' => $question,
								'is_coordinator' => true,	// only ones allowed to even run this report
								'value' => $entry[$question],
						));
					}
				}
				$rows[] = $row;
				$colcount = count($row);
				if (!empty ($entry['comments'])) {
					$rows[] = array(
							array(__('Comment for entry above:', true), array('colspan' => 2)),
							array($entry['comments'], array('colspan' => $colcount - 2)),
					);
				}
			}
		}
	}
}

echo $this->Html->tag ('table', $this->Html->tableHeaders ($header) . $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>

</div>
