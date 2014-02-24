<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Preferences', true));
?>

<div class="registrations form">
<h2><?php echo __('Registration Preferences', true) . ': ' . $event['Event']['name']; ?></h2>

<?php
echo $this->element ('registrations/notice');

if ($waiting) {
	echo $this->Html->para('warning-message', __('Note that you are only adding yourself to the waiting list for this event. You will be contacted if a space opens up at a later time.', true));
}

echo $this->Form->create('Response', array('url' => Router::normalize($this->here)));

echo $this->element ('questionnaires/input', array('questionnaire' => $event['Questionnaire']));

if ($event['Price'][0]['allow_deposit']):
?>
<fieldset>
<legend><?php __('Payment'); ?></legend>
<?php
$cost = $event['Price'][0]['cost'] + $event['Price'][0]['tax1'] + $event['Price'][0]['tax2'];
if ($event['Price'][0]['deposit_only']) {
	$options = array('Deposit' => "Deposit (\${$event['Price'][0]['minimum_deposit']})");
} else if ($event['Price'][0]['fixed_deposit']) {
	$options = array('Deposit' => "Deposit (\${$event['Price'][0]['minimum_deposit']})", 'Full' => "Full (\${$cost})");
} else if ($event['Price'][0]['allow_deposit']) {
	$options = array('Deposit' => "Deposit (minimum \${$event['Price'][0]['minimum_deposit']})", 'Full' => "Full (\${$cost})");
} else {
	$options = array('Full' => "Full (\${$cost})");
}
echo $this->Form->input('Registration.payment_type', array(
		'options' => $options,
));
if (!$event['Price'][0]['fixed_deposit']) {
	echo $this->Form->input('Registration.deposit_amount', array('default' => $event['Price'][0]['minimum_deposit']));
}
echo $this->Html->scriptBlock("
function typeChanged() {
	if (jQuery('#RegistrationPaymentType').val() == 'Full') {
		jQuery('#RegistrationDepositAmount').prop('disabled', true);
		jQuery('#RegistrationDepositAmount').closest('div').css('display', 'none');
	} else if (jQuery('#RegistrationPaymentType').val() == 'Deposit') {
		jQuery('#RegistrationDepositAmount').prop('disabled', false);
		jQuery('#RegistrationDepositAmount').closest('div').css('display', '');
	}
}
");
$this->Js->buffer('
jQuery("#RegistrationPaymentType").on("change", function(){typeChanged();});
');
?>
</fieldset>
<?php
endif;
?>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

</div>
<?php echo $this->Form->end(); ?>

</div>
