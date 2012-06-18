<?php
$this->Html->addCrumb (__('Questionnaire', true));
$this->Html->addCrumb ($this->data['Questionnaire']['name']);
$this->Html->addCrumb (__('Edit', true));
?>

<div class="questionnaires form">
<?php echo $this->Form->create('Questionnaire', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Questionnaire', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->ZuluruForm->input('name', array('size' => 60));
		echo $this->ZuluruForm->input('active');
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Questions'); ?></legend>
	<?php
		echo $this->element('questionnaires/edit', array('questionnaire' => $this->data));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
