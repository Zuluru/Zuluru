<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($registration['Person']['full_name']);
$this->Html->addCrumb ($registration['Event']['name']);
$this->Html->addCrumb (sprintf(__('%s Payment', true), __('Add', true)));
?>

<div class="registrations form">
<h2><?php printf(__('%s Payment', true), __('Add', true)); ?></h2>
<?php echo $this->Form->create('Payment', array('url' => Router::normalize($this->here)));?>

	<fieldset>
		<legend><?php __('Payment Details'); ?></legend>
	<?php
		$payments = array_sum(Set::extract('/Payment/payment_amount', $registration));
		echo $this->ZuluruForm->input('payment_amount', array(
				'default' => $registration['Registration']['total_amount'] - $payments,
		));

		$options = Configure::read('options.payment_method');
		$credits = $this->UserCache->read('Credits', $registration['Person']['id']);
		$credit_options = array();
		foreach ($credits as $credit) {
			if ($credit['Credit']['affiliate_id'] == $registration['Event']['affiliate_id']) {
				$credit_options[$credit['Credit']['id']] = sprintf('$%.02f', $credit['Credit']['amount'] - $credit['Credit']['amount_used']);
				if ($credit['Credit']['amount_used'] > 0) {
					$credit_options[$credit['Credit']['id']] .= sprintf(' ($%.02f - $%.02f)', $credit['Credit']['amount'], $credit['Credit']['amount_used']);
				}
			}
		}
		if (empty($credit_options)) {
			unset($options['Credit Redeemed']);
		}
		echo $this->ZuluruForm->input('payment_method', array(
				'empty' => 'Select one:',
				'options' => $options,
		));
		echo $this->Html->scriptBlock('
function methodChanged() {
	var show_credits = (jQuery("#PaymentPaymentMethod").val() == "Credit Redeemed");
	jQuery("#standard_options").find("textarea").prop("disabled", show_credits);
	jQuery("#standard_options").css("display", (show_credits ? "none" : ""));
	jQuery("#credit_options").find("select").prop("disabled", !show_credits);
	jQuery("#credit_options").css("display", (show_credits ? "" : "none"));
}
		');
		$this->Js->get('#PaymentPaymentMethod')->event('change', 'methodChanged();');
		$this->Js->buffer('methodChanged();');
	?>
	<div id="standard_options">
	<?php
		echo $this->ZuluruForm->input('notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'after' => $this->Html->para(null, __('These notes will be attached to the new payment record, and are only visible to admins.', true)),
		));
	?>
	</div>
	<div id="credit_options">
	<?php
		echo $this->ZuluruForm->input('credit_id', array(
				'options' => $credit_options,
				'after' => $this->Html->para(null, __('The lowest of the credit amount, specified payment amount, and outstanding balance will be used as the actual payment amount.', true)),
		));
	?>
	</div>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>

