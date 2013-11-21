<?php
$timezone = Configure::read('timezone.name');

// Outlook only imports the last item from an ICS file. We'll consider games more important than events and put them last.
if (isset($events) && !empty($events)) {
	$uid_prefix = 'E';
	foreach ($events as $event) {
		$event_id = $event['TeamEvent']['id'];
		echo $this->element('team_events/ical', compact('event_id', 'event', 'timezone', 'uid_prefix'));
	}
}

if (isset($games) && !empty($games)) {
	$uid_prefix = '';
	foreach ($games as $game) {
		$game_id = $game['Game']['id'];
		echo $this->element('games/ical', compact('game_id', 'team_id', 'game', 'timezone', 'uid_prefix'));
	}
}
?>
