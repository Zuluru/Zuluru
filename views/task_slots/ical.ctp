<?php
$timezone = Configure::read('timezone.name');
$uid_prefix = 'T';
$task_slot = $taskSlot['TaskSlot']['id'];

// Reformat the data as the element expects
$task = $taskSlot['TaskSlot'];
$task['Task'] = $taskSlot['Task'];

echo $this->element('tasks/ical', compact('task_slot', 'task', 'timezone', 'uid_prefix'));
?>
