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
		echo $this->ZuluruForm->input('payment_method', array(
				'empty' => 'Select one:',
				'options' => Configure::read('options.payment_method'),
		));

		echo $this->ZuluruForm->input('notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'after' => $this->Html->para(null, 'These notes will be attached to the new payment record, and are only visible to admins.'),
		));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>

