<?php
$this->Html->addCrumb (__('Task Slots', true));
$this->Html->addCrumb (__('View', true));
?>

<div class="taskSlots view">
<h2><?php __('Task Slot');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Task'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($taskSlot['Task']['name'], array('controller' => 'tasks', 'action' => 'view', 'task' => $taskSlot['Task']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Task Date'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date($taskSlot['TaskSlot']['task_date']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Task Start'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time($taskSlot['TaskSlot']['task_start']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Task End'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->time($taskSlot['TaskSlot']['task_end']); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Assigned To'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('people/block', array('person' => $taskSlot['Person'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Approved'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($taskSlot['TaskSlot']['approved'] ? 'Yes' : 'No'); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Approved By'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('people/block', array('person' => $taskSlot['ApprovedBy'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<?php
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('edit_32.png',
			array('action' => 'edit', 'slot' => $taskSlot['TaskSlot']['id']),
			array('alt' => __('Edit', true), 'title' => __('Edit Task Slot', true))));
		echo $this->Html->tag('li', $this->ZuluruHtml->iconLink('delete_32.png',
			array('action' => 'delete', 'slot' => $taskSlot['TaskSlot']['id']),
			array('alt' => __('Delete', true), 'title' => __('Delete Task Slot', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $taskSlot['TaskSlot']['id']))));
		?>
	</ul>
</div>
