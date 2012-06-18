<?php
if (isset($games) && !empty($games)) {
	$timezone = Configure::read('timezone.name');
	$uid_prefix = '';
	foreach ($games as $game) {
		$game_id = $game['Game']['id'];
		echo $this->element('games/ical', compact('game_id', 'team_id', 'game', 'timezone', 'uid_prefix'));
	}
}
?>
