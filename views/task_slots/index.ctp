<?php
$this->Html->addCrumb (__('Task Slots', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="taskSlots index">
<h2><?php __('Task Slots');?></h2>
<table class="list">
	<tr>
		<th><?php __('Task Id'); ?></th>
		<th><?php __('Task Date'); ?></th>
		<th><?php __('Task Start'); ?></th>
		<th><?php __('Task End'); ?></th>
		<th><?php __('Person Id'); ?></th>
		<th><?php __('Approved'); ?></th>
		<th><?php __('Approved By'); ?></th>
		<th class="actions"><?php __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($taskSlots as $taskSlot):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($taskSlot['Task']['name'], array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id'])); ?>
		</td>
		<td><?php echo $taskSlot['TaskSlot']['task_date']; ?>&nbsp;</td>
		<td><?php echo $taskSlot['TaskSlot']['task_start']; ?>&nbsp;</td>
		<td><?php echo $taskSlot['TaskSlot']['task_end']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($taskSlot['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $taskSlot['Person']['id'])); ?>
		</td>
		<td><?php echo $taskSlot['TaskSlot']['approved']; ?>&nbsp;</td>
		<td><?php echo $taskSlot['TaskSlot']['approved_by']; ?>&nbsp;</td>
		<td class="actions">
		<?php
		echo $this->ZuluruHtml->iconLink('view_24.png',
			array('action' => 'view', 'taskSlot' => $taskSlot['TaskSlot']['id']),
			array('alt' => __('View', true), 'title' => __('View', true)));
		if ($is_admin || $is_manager) {
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('action' => 'edit', 'taskSlot' => $taskSlot['TaskSlot']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('action' => 'delete', 'taskSlot' => $taskSlot['TaskSlot']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $taskSlot['TaskSlot']['id'])));
		}
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<ul>
		<?php
		if ($is_admin || $is_manager) {
			echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
				array('action' => 'add'),
				array('alt' => __('Add', true), 'title' => __('Add Task Slot', true))));
		}
		?>
	</ul>
</div>
