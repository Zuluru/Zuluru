<?php
$this->Html->addCrumb (__('Tasks', true));
$this->Html->addCrumb ($task['Task']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="tasks view">
<h2><?php __('Task');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $task['Task']['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Category'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($task['Category']['name'], array('controller' => 'categories', 'action' => 'view', 'task' => $task['Category']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $task['Task']['description']; ?>
			&nbsp;
		</dd>
		<?php if ($is_admin || $is_manager): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Notes'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $task['Task']['notes']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Auto-Approve'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($task['Task']['auto_approve'] ? 'Yes' : 'No'); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Allow Signup'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($task['Task']['allow_signup'] ? 'Yes' : 'No'); ?>
			&nbsp;
		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Reporting To'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('people/block', array('person' => $task['Person'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<?php if (!empty($task['TaskSlot'])):?>
<div class="related">
	<h3><?php __('Related Task Slots');?></h3>
	<table class="list">
	<tr>
		<th><?php __('Task Date'); ?></th>
		<th><?php __('Task Start'); ?></th>
		<th><?php __('Task End'); ?></th>
		<th><?php __('Assigned To'); ?></th>
		<th><?php __('Approved'); ?></th>
		<th><?php __('Approved By'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
		<?php
		foreach ($task['TaskSlot'] as $taskSlot):
			$class = null;
			if (empty($taskSlot['person_id'])) {
				$class = ' class="unpublished"';
			}
		?>
	<tr id="slot_<?php echo $taskSlot['id']; ?>"<?php echo $class;?>>
		<td><?php echo $this->ZuluruTime->date($taskSlot['task_date']);?></td>
		<td><?php echo $this->ZuluruTime->time($taskSlot['task_start']);?></td>
		<td><?php echo $this->ZuluruTime->time($taskSlot['task_end']);?></td>
		<td class="assigned_to"><?php
		if ($is_admin || $is_manager) {
			echo $this->Form->input('person_id', array(
					'label' => false,
					'empty' => '---',
					'options' => $people,
					'default' => $taskSlot['person_id'],
			));
		} else if (!empty($taskSlot['person_id'])) {
			echo $this->element('people/block', array('person' => $taskSlot['Person']));
		} else {
			echo 'unclaimed';
		}
		?></td>
		<td class="approved"><?php __($taskSlot['approved'] ? 'Yes' : 'No');?></td>
		<td class="approved_by"><?php
		if (!empty($taskSlot['approved_by'])) {
			echo $this->element('people/block', array('person' => $taskSlot['ApprovedBy']));
		}
		?></td>
		<td class="actions">
		<?php
		if ($is_admin || $is_manager) {
			echo $this->ZuluruHtml->iconLink('view_24.png',
				array('controller' => 'task_slots', 'action' => 'view', 'slot' => $taskSlot['id']),
				array('alt' => __('View', true), 'title' => __('View', true)));
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'task_slots', 'action' => 'edit', 'slot' => $taskSlot['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
			echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('controller' => 'task_slots', 'action' => 'delete', 'slot' => $taskSlot['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $taskSlot['id'])));
			if ($taskSlot['person_id'] && !$taskSlot['approved']) {
				echo $this->ZuluruHtml->link(__('Approve', true),
					array('controller' => 'task_slots', 'action' => 'approve', 'slot' => $taskSlot['id']),
					array('class' => 'approve_link'));
			}
		} else if (empty($taskSlot['person_id'])) {
			echo $this->ZuluruHtml->link(__('Sign up', true),
				array('controller' => 'task_slots', 'action' => 'assign', 'slot' => $taskSlot['id'], 'person' => $my_id),
				array('class' => 'assign_link'));
		}
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php if ($is_admin || $is_manager): ?>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('view_32.png',
			array('action' => 'index'),
			array('alt' => __('List', true), 'title' => __('List Tasks', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
			array('action' => 'edit', 'task' => $task['Task']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit Task', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
			array('action' => 'delete', 'task' => $task['Task']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete Task', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $task['Task']['id']))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('schedule_add_32.png',
			array('controller' => 'task_slots', 'action' => 'add', 'task' => $task['Task']['id']),
			array('alt' => __('Add Slots', true), 'title' => __('Add Slots', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('add_32.png',
			array('action' => 'add'),
			array('alt' => __('Add', true), 'title' => __('Add Task', true))));
		?>
	</ul>
</div>
<?php endif; ?>

<?php
$assign_url = Router::url(array('controller' => 'task_slots', 'action' => 'assign'));
$approve_url = Router::url(array('controller' => 'task_slots', 'action' => 'approve'));
echo $this->Html->scriptBlock ("
function assign_slot(ths, person) {
	if (person == '') {
		person = 0;
	}
	var id = jQuery(ths).closest('tr').attr('id');
	var slot = id.substr(5);
	jQuery.ajax({
		dataType: 'html',
		type: 'GET',
		success: function (data, textStatus) {
			jQuery('#temp_update').html(data);
		},
		url: '$assign_url/slot:' + slot + '/person:' + person
	});
}

function approve_slot(ths) {
	var id = jQuery(ths).closest('tr').attr('id');
	var slot = id.substr(5);
	jQuery.ajax({
		dataType: 'html',
		type: 'GET',
		success: function (data, textStatus) {
			jQuery('#temp_update').html(data);
		},
		url: '$approve_url/slot:' + slot
	});
}
");

if ($is_admin || $is_manager) {
	echo $this->Html->scriptBlock ("
jQuery('.related select').change(function() { assign_slot(this, jQuery(this).attr('value')); })
jQuery('.approve_link').bind('click', function() { approve_slot(this); return false; });
	");
} else {
	echo $this->Html->scriptBlock ("jQuery('.assign_link').bind('click', function() { assign_slot(this, $my_id); return false; });");
}
?>
