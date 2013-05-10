<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Spirit', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams spirit">
<h2><?php echo __('Team Spirit', true) . ': ' . $team['Team']['name'];?></h2>

<?php
$header = array(
		__('Game', true),
		__('Entry By', true),
);
if ($team['Division']['League']['numeric_sotg']) {
	$header[] = __('Entered', true);
}
if ($team['Division']['League']['sotg_questions'] != 'none') {
	$header[] = __('Assigned', true);
}

// TODO: Move display details into an element to share between this, division spirit report, maybe others
foreach ($spirit_obj->questions as $detail) {
	if ($detail['type'] != 'text') {
		$header[] = __($detail['name'], true);
	}
}

$rows = array();
foreach ($team['Game'] as $game) {
	foreach ($game['SpiritEntry'] as $entry) {
		if ($entry['team_id'] == $team['Team']['id']) {
			if ($entry['created_team_id'] == $game['HomeTeam']['id']) {
				$which = 'HomeTeam';
			} else {
				$which = 'AwayTeam';
			}
			$row = array(
					$this->Html->link ($game['Game']['id'], array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id'])) . ' ' .
						$this->ZuluruTime->date ($game['GameSlot']['game_date']),
					$this->element('teams/block', array('team' => $game[$which], 'show_shirt' => false)),
			);
			if ($team['Division']['League']['numeric_sotg']) {
				$row[] = $entry['entered_sotg'];
			}
			if ($team['Division']['League']['sotg_questions'] != 'none') {
				$row[] = $spirit_obj->calculate($entry);
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				if ($detail['type'] != 'text') {
					$row[] = $this->element ('spirit/symbol', array(
							'spirit_obj' => $spirit_obj,
							'league' => $team['Division']['League'],
							'question' => $question,
							'is_coordinator' => true,	// only ones allowed to even run this report
							'entry' => $entry,
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
			if (!empty ($entry['highlights'])) {
				$rows[] = array(
						array(__('Highlight for entry above:', true), array('colspan' => 2)),
						array($entry['highlights'], array('colspan' => $colcount - 2)),
				);
			}
		}
	}
}

echo $this->Html->tag ('table', $this->Html->tableHeaders ($header) . $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>

</div>