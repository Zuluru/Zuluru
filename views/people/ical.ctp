<?php
$timezone = Configure::read('timezone.name');
$uid_prefix = 'P';
if (isset ($games)) {
	foreach ($games as $game) {
		$game_id = $game['Game']['id'];
		echo $this->element('games/ical', compact('game_id', 'team_id', 'game', 'timezone', 'uid_prefix'));
	}
}
$uid_prefix = 'T';
if (isset ($tasks)) {
	foreach ($tasks as $task) {
		$task_slot = $task['id'];
		echo $this->element('tasks/ical', compact('task_slot', 'task', 'timezone', 'uid_prefix'));
	}
}
?>
