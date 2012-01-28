<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Spirit', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams spirit">
<h2><?php echo __('Team Spirit', true) . ': ' . $team['Team']['name'];?></h2>

<?php
// TODO: Include score column only if numeric scoring is on,
// include other columns including calculation if survey is on
$header = array(
		__('Game', true),
		__('Entry By', true),
		__('Score', true),
);

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
					$entry['entered_sotg'],
			);
			foreach ($spirit_obj->questions as $question => $detail) {
				if ($detail['type'] != 'text') {
					$row[] = $this->element ('spirit/symbol', array(
							'spirit_obj' => $spirit_obj,
							'type' => $team['Division']['League']['display_sotg'],
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

echo $this->Html->tag ('table', $this->Html->tableHeaders ($header) . $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>

</div>