<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Preferences', true));
?>

<div class="registrations form">
<h2><?php echo __('Registration Preferences', true) . ': ' . $event['Event']['name']; ?></h2>

<?php
echo $this->element('registrations/relative_notice');
echo $this->element('registrations/notice');

if ($waiting) {
	echo $this->Html->para('warning-message', __('Note that you are only adding yourself to the waiting list for this event. You will be contacted if a space opens up at a later time.', true));
}

echo $this->Form->create('Response', array('url' => Router::normalize($this->here)));

echo $this->element ('questionnaires/input', array('questionnaire' => $event['Questionnaire']));

// If there is more than one price option, or if deposits are allowed and either they are not the only option or the amount is not fixed,
// then we need to provide payment options.
if (count($event['Price']) > 1 || ($event['Price'][0]['allow_deposit'] && (!$event['Price'][0]['deposit_only'] || !$event['Price'][0]['fixed_deposit']))):
?>
<fieldset>
<legend><?php __('Payment'); ?></legend>
<?php if (count($event['Price']) > 1): ?>
<p><?php __('This event has the following options. Please select your preference. Note that options may have limitations, which will be noted on selection.'); ?></p>
<?php
		$options = array();
		foreach ($event['Price'] as $price_option) {
			$cost = $price_option['cost'] + $price_option['tax1'] + $price_option['tax2'];
			$options[$price_option['id']] = "{$price_option['name']} (\${$cost})";
		}
		echo $this->Form->input('Registration.price_id', array(
				'label' => 'Registration options',
				'empty' => 'Select one:',
				'options' => $options,
				'default' => $price_id,
		));

		$spinner = $this->ZuluruHtml->icon('spinner.gif');
		echo $this->Html->scriptBlock("
function optionChanged() {
	jQuery('#PaymentDetails').html('$spinner');
" . $this->Js->request(
		array('action' => 'register_payment_fields'),
		array('update' => '#PaymentDetails', 'dataExpression' => true, 'data' => 'jQuery("#RegistrationPriceId").get()')
) . '
}
		');
		$this->Js->get('#RegistrationPriceId')->event('change', 'optionChanged();');
	else:
		// There is only one price option
		echo $this->Form->hidden('Registration.price_id', array('value' => $event['Price'][0]['id']));
	endif;
?>
<div id="PaymentDetails">
<?php
	if (isset($price)) {
		echo $this->element('registrations/register_payment_fields', array('price' => $price));
	}
?>
</div>
</fieldset>
<?php
else:
	// There is only one price option
	echo $this->Form->hidden('Registration.price_id', array('value' => $event['Price'][0]['id']));
endif;
?>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

</div>
<?php echo $this->Form->end(); ?>

</div>
