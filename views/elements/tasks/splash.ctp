<?php if (!empty($tasks)): ?>
<table class="list">
<tr>
	<th><?php __('Tasks'); ?></th>
	<th><?php __('Time'); ?></th>
	<th><?php __('Report To'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
$i = 0;
foreach ($tasks as $task):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
<tr<?php echo $class;?>>
	<td class="splash_item"><?php
	echo $this->Html->link($task['Task']['name'], array('controller' => 'tasks', 'action' => 'view', 'task' => $task['Task']['id']));
	?></td>
	<td class="splash_item"><?php
	echo $this->ZuluruTime->day($task['TaskSlot']['task_date']) . ', ' .
			$this->ZuluruTime->time($task['TaskSlot']['task_start']) . '-' .
			$this->ZuluruTime->time($task['TaskSlot']['task_end'])
	?></td>
	<td class="splash_item"><?php
	echo $this->element('people/block', array('person' => $task['Task']['Person']));
	?></td>
	<td class="actions"><?php
	echo $this->Html->link(
			__('iCal', true),
			array('controller' => 'task_slots', 'action' => 'ical', $task['TaskSlot']['id'], 'task.ics'));

	?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

