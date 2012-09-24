<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($registration['Person']['full_name']);
$this->Html->addCrumb ($registration['Event']['name']);
$this->Html->addCrumb (__('Edit', true));
?>

<div class="registrations form">
<h2><?php printf(__('Edit %s', true), __('Registration', true)); ?></h2>
<?php echo $this->Form->create('Registration', array('url' => Router::normalize($this->here)));?>

	<fieldset>
 		<legend><?php __('Registration Details'); ?></legend>
	<?php
		echo $this->Form->input('id', array(
				'value' => $registration['Registration']['id'],
		));
		echo $this->ZuluruForm->input('order', array(
				'label' => 'Order ID',
				'value' => sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']),
				'readonly' => true,
		));
		echo $this->ZuluruForm->input('name', array(
				'value' => $registration['Person']['full_name'],
				'readonly' => true,
				'size' => 75,
		));
		echo $this->ZuluruForm->input('event', array(
				'value' => $registration['Event']['name'],
				'readonly' => true,
				'size' => 75,
		));
		echo $this->ZuluruForm->input('payment', array(
				'options' => Configure::read('options.payment'),
		));
		echo $this->Form->input('notes', array(
				'type' => 'textbox',
				'cols' => 72,
		));
	?>
	</fieldset>

<?php if (!empty($registration['Event']['Questionnaire'])):?>
	<fieldset><legend><?php __('Registration Answers'); ?></legend>
		<div class="related">
<?php echo $this->element ('questionnaires/input', array('questionnaire' => $registration['Event']['Questionnaire'], 'response' => $registration, 'edit' => true)); ?>

		</div>
	</fieldset>
<?php endif; ?>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
