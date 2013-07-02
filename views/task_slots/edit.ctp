<?php
$this->Html->addCrumb (__('Task Slots', true));
$this->Html->addCrumb (__('Edit', true));
?>

<div class="taskSlots form">
<?php echo $this->Form->create('TaskSlot', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
		<legend><?php printf(__('Edit %s', true), __('Task Slot', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('task_date');
		echo $this->Form->input('task_start');
		echo $this->Form->input('task_end');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'slot' => $this->Form->value('TaskSlot.id')),
				array('alt' => __('Delete', true), 'title' => __('Delete TaskSlot', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $this->Form->value('TaskSlot.id')))); ?></li>
	</ul>
</div>

<?php
echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
?>