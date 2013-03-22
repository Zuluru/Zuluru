<?php
$this->Html->addCrumb (__('Questionnaire', true));
if (isset ($add)) {
	$this->Html->addCrumb (__('Create', true));
} else {
	$this->Html->addCrumb ($this->Form->value('Questionnaire.name'));
	$this->Html->addCrumb (__('Edit', true));
}
?>

<div class="questionnaires form">
<?php echo $this->Form->create('Questionnaire', array('url' => Router::normalize($this->here)));?>
	<fieldset>
 		<legend><?php printf(__(isset($add) ? 'Create %s' : 'Edit %s', true), __('Questionnaire', true)); ?></legend>
	<?php
		if (!isset ($add)) {
			echo $this->Form->input('id');
		}
		echo $this->ZuluruForm->input('name', array('size' => 60));
		if (isset ($add)) {
			echo $this->ZuluruForm->input('affiliate_id', array(
				'options' => $affiliates,
				'hide_single' => true,
				'empty' => '---',
			));
		} else {
			echo $this->ZuluruForm->input('active');
			echo $this->ZuluruForm->hidden('affiliate_id');
		}
	?>
	</fieldset>
	<?php if (!isset ($add)): ?>
	<fieldset>
 		<legend><?php __('Questions'); ?></legend>
	<?php
		echo $this->element('questionnaires/edit', array('questionnaire' => $this->data));
	?>
	</fieldset>
	<?php endif; ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Questionnaires', true)), array('action' => 'index'));?></li>
	</ul>
</div>
