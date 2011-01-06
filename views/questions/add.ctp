<div class="questions form">
<?php echo $this->Form->create('Question');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Question', true)); ?></legend>
	<?php
		echo $this->Form->input('question', array(
				'cols' => 60,
		));
		echo $this->Form->input('type', array(
				'options' => Configure::read('options.question_types'),
				'empty' => true,
		));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Questions', true)), array('action' => 'index'));?></li>
	</ul>
</div>