<?php
$league = null;

foreach ($games as $game) {
	// Check for a new league
	if ($game['Division']['League']['id'] != $league) {
		$league = $game['Division']['League']['id'];
		echo $this->Html->tag('h2', $game['Division']['League']['name']);
	}

	if ($game['finalized'] === true) {
		$op = __('Finalized', true);
	} else if ($game['emailed'] === true) {
		$op = __('Emailed', true);
	} else {
		$op = __('DID NOT finalize or email', true);
	}

	echo $this->Html->para(null,
		$op . ' ' .
		$this->ZuluruTime->date ($game['GameSlot']['game_date']) . ' ' .
		$this->ZuluruTime->time ($game['GameSlot']['game_start']) . ' ' .
		__('game', true) . ' ' .
		$this->Html->link($game['Game']['id'], $this->Html->url (array('action' => 'view', 'game' => $game['Game']['id']), true)) . ' ' .
		__('between', true) . ' ' .
		$this->Html->link($game['HomeTeam']['name'], $this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']), true)) . ' ' .
		__('and', true) . ' ' .
		$this->Html->link($game['AwayTeam']['name'], $this->Html->url (array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']), true)) . ', ' .
		__('status', true) . ' ' .
		__($game['Game']['status'], true)
	);
}

echo $this->Html->tag('h2', __('Attendance', true));

echo $this->Html->para(null, sprintf(__('%s attendance reminders sent', true), $remind_count));
echo $this->Html->para(null, sprintf(__('%s attendance summaries sent', true), $summary_count));

?>
