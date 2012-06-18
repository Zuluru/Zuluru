<?php
$this->Html->addCrumb (__('Questionnaire', true));
$this->Html->addCrumb (__('Add', true));
?>

<div class="questionnaires form">
<?php echo $this->Form->create('Questionnaire');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Questionnaire', true)); ?></legend>
	<?php
		echo $this->Form->input('name', array('size' => 60));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Questionnaires', true)), array('action' => 'index'));?></li>
	</ul>
</div>