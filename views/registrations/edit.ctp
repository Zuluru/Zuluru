<div class="registrations form">
<h2><?php printf(__('Edit %s', true), __('Registration', true)); ?></h2>
<?php echo $this->Form->create('Registration', array('url' => $this->here));?>

	<fieldset>
 		<legend><?php __('Registration Details'); ?></legend>
	<?php
		echo $this->Form->input('id', array(
				'value' => $registration['Registration']['id'],
		));
		echo $this->Form->input('order', array(
				'label' => 'Order ID',
				'value' => sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']),
				'readonly' => true,
		));
		echo $this->Form->input('name', array(
				'value' => $registration['Person']['full_name'],
				'readonly' => true,
				'size' => 75,
		));
		echo $this->Form->input('event', array(
				'value' => $registration['Event']['name'],
				'readonly' => true,
				'size' => 75,
		));
		echo $this->Form->input('payment', array(
				'options' => Configure::read('options.payment'),
		));
		echo $this->Form->input('notes', array(
				'type' => 'textbox',
				'cols' => 72,
		));
	?>
	</fieldset>

<?php if (!empty($registration['Response'])):?>
	<fieldset><legend><?php __('Registration Answers'); ?></legend>
		<div class="related">
<?php echo $this->element ('questionnaire/input', array('questionnaire' => $registration['Event']['Questionnaire'], 'response' => $registration, 'edit' => true)); ?>

		</div>
	</fieldset>
<?php endif; ?>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
