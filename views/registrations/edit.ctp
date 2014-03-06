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
		echo $this->ZuluruForm->input('order', array(
				'label' => 'Order ID',
				'value' => sprintf (Configure::read('registration.order_id_format'), $registration['Registration']['id']),
				'readonly' => true,
		));
		if ($is_admin || $is_manager) {
			echo $this->ZuluruForm->input('name', array(
					'value' => $registration['Person']['full_name'],
					'readonly' => true,
					'size' => 75,
			));
		}
		echo $this->ZuluruForm->input('event', array(
				'value' => $registration['Event']['name'],
				'readonly' => true,
				'size' => 75,
		));
		if ($is_admin || $is_manager) {
			echo $this->ZuluruForm->input('payment', array(
					'options' => Configure::read('options.payment'),
					'after' => $this->Html->para('warning-message', 'Change this only in extreme circumstances; for proper accounting, refunds and payments should be entered through links on the registration view page.'),
			));
			echo $this->Form->input('notes', array(
					'type' => 'textbox',
					'cols' => 72,
			));
		}
	?>
	</fieldset>

<?php if (!empty($registration['Event']['Questionnaire'])):?>
	<fieldset><legend><?php __('Registration Answers'); ?></legend>
		<div class="related">
<?php echo $this->element ('questionnaires/input', array('questionnaire' => $registration['Event']['Questionnaire'], 'response' => $registration, 'edit' => true)); ?>

		</div>
	</fieldset>
<?php endif; ?>

<?php
// If there is more than one price option, or if deposits are allowed and either they are not the only option or the amount is not fixed,
// then we need to provide payment options.
if (count($registration['Event']['Price']) > 1 || ($registration['Event']['Price'][0]['allow_deposit'] && (!$registration['Event']['Price'][0]['deposit_only'] || !$registration['Event']['Price'][0]['fixed_deposit']))):
?>
<fieldset>
<legend><?php __('Payment'); ?></legend>
<?php if (count($registration['Event']['Price']) > 1): ?>
<p><?php __('This event has the following options. Please select your preference. Note that options may have limitations, which will be noted after selection.'); ?></p>
<?php
		$options = array();
		foreach ($registration['Event']['Price'] as $price_option) {
			$cost = $price_option['cost'] + $price_option['tax1'] + $price_option['tax2'];
			$options[$price_option['id']] = "{$price_option['name']} (\${$cost})";
		}
		echo $this->Form->input('Registration.price_id', array(
				'label' => 'Registration options',
				'empty' => 'Select one:',
				'options' => $options,
				'default' => $registration['Price']['id'],
		));

		$spinner = $this->ZuluruHtml->icon('spinner.gif');
		echo $this->Html->scriptBlock("
function optionChanged() {
	jQuery('#PaymentDetails').html('$spinner');
" . $this->Js->request(
		array('action' => 'register_payment_fields', 'registration' => $registration['Registration']['id'], 'for_edit' => true),
		array('update' => '#PaymentDetails', 'dataExpression' => true, 'data' => 'jQuery("#RegistrationPriceId").get()')
) . '
}
		');
		$this->Js->get('#RegistrationPriceId')->event('change', 'optionChanged();');
	else:
		// There is only one price option
		echo $this->Form->hidden('Registration.price_id', array('value' => $registration['Registration']['price_id']));
	endif;
?>
<div id="PaymentDetails">
<?php echo $this->element('registrations/register_payment_fields', array('price' => $price, 'registration' => $registration['Registration'])); ?>
</div>
</fieldset>
<?php
else:
	// There is only one price option
	echo $this->Form->hidden('Registration.price_id', array('value' => $registration['Registration']['price_id']));
endif;
?>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
