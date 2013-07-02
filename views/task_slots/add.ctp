<?php
$this->Html->addCrumb ($task['Task']['name']);
$this->Html->addCrumb (__('Task Slots', true));
$this->Html->addCrumb (__('Create', true));
?>

<div class="taskSlots form">
<?php echo $this->Form->create('TaskSlot', array('url' => Router::normalize($this->here))); ?>
	<fieldset>
		<legend><?php printf(__('Create Slots for the "%s" Task', true), $task['Task']['name']); ?></legend>
	<?php
		echo $this->Form->input('task_date');
		echo $this->Form->input('task_start');
		echo $this->Form->input('task_end');
		echo $this->ZuluruForm->input('number_of_slots', array(
				'type' => 'number',
				'size' => 3,
				'default' => 1,
				'after' => $this->Html->para (null, __('The system will add this many slots at the specified time.', true)),
		));
		echo $this->ZuluruForm->input('days_to_repeat', array(
				'type' => 'number',
				'size' => 3,
				'default' => 1,
				'after' => $this->Html->para (null, __('The system will add the specified number of slots at the specified time for this many consecutive days.', true)),
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<?php
echo $this->ZuluruHtml->script ('datepicker', array('inline' => false));
?>