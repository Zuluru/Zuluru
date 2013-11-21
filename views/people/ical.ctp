<?php
$timezone = Configure::read('timezone.name');
if (isset ($games)) {
	$uid_prefix = 'P';
	foreach ($games as $game) {
		$game_id = $game['Game']['id'];
		echo $this->element('games/ical', compact('game_id', 'team_id', 'game', 'timezone', 'uid_prefix'));
	}
}

if (isset ($tasks)) {
	$uid_prefix = 'T';
	foreach ($tasks as $task) {
		$task_slot = $task['TaskSlot']['id'];
		echo $this->element('tasks/ical', compact('task_slot', 'task', 'timezone', 'uid_prefix'));
	}
}

if (isset($events) && !empty($events)) {
	$uid_prefix = 'E';
	foreach ($events as $event) {
		$event_id = $event['TeamEvent']['id'];
		echo $this->element('team_events/ical', compact('event_id', 'event', 'timezone', 'uid_prefix'));
	}
}
?>
