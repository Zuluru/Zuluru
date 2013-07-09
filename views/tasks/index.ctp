<?php
$this->Html->addCrumb (__('Tasks', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="tasks index">
<h2><?php __('Tasks');?></h2>
<table class="list">
	<tr>
		<th><?php __('Task'); ?></th>
		<th><?php __('Category'); ?></th>
		<th><?php __('Reporting To'); ?></th>
		<?php if ($is_admin || $is_manager): ?>
		<th><?php __('Auto-Approve'); ?></th>
		<th><?php __('Allow Signup'); ?></th>
		<?php endif; ?>
		<th class="actions"><?php __('Actions'); ?></th>
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
		<td><?php echo $task['Task']['name']; ?></td>
		<td><?php echo $this->Html->link($task['Category']['name'], array('controller' => 'categories', 'action' => 'view', 'category' => $task['Category']['id'])); ?></td>
		<?php if ($is_admin || $is_manager): ?>
		<td><?php __($task['Task']['auto_approve'] ? 'Yes' : 'No'); ?></td>
		<td><?php __($task['Task']['allow_signup'] ? 'Yes' : 'No'); ?></td>
		<?php endif; ?>
		<td><?php echo $this->element('people/block', array('person' => $task['Person'])); ?></td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'task' => $task['Task']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		if ($is_admin || $is_manager) {
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('action' => 'edit', 'task' => $task['Task']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'delete', 'task' => $task['Task']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $task['Task']['id'])));
			echo $this->ZuluruHtml->iconLink('schedule_add_24.png',
				array('controller' => 'task_slots', 'action' => 'add', 'task' => $task['Task']['id']),
				array('alt' => __('Add Slots', true), 'title' => __('Add Slots', true)));
		}
		?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<?php if ($is_admin || $is_manager): ?>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
			array('action' => 'add'),
			array('alt' => __('Add', true), 'title' => __('Add Task', true))));
		?>
	</ul>
</div>
<?php endif; ?>