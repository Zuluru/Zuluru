<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($payment['Registration']['Person']['full_name']);
$this->Html->addCrumb ($payment['Registration']['Event']['name']);
$this->Html->addCrumb (sprintf(__('%s Payment', true), __('Transfer', true)));
?>

<div class="registrations form">
<h2><?php printf(__('%s Payment', true), __('Transfer', true)); ?></h2>
<?php echo $this->Form->create('Payment', array('url' => Router::normalize($this->here)));?>

	<fieldset>
		<legend><?php __('Transfer Details'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('amount', array(
				'default' => $payment['Payment']['payment_amount'] - $payment['Payment']['refunded_amount'],
		));

		if (!in_array($payment['Registration']['payment'], Configure::read('registration_cancelled'))) {
			echo $this->ZuluruForm->input('mark_refunded', array(
					'label' => 'Mark this registration as refunded?',
					'type' => 'checkbox',
					'checked' => true,
			));
		} else {
			echo $this->ZuluruForm->hidden('mark_refunded', array('value' => 0));
		}

		echo $this->ZuluruForm->input('registration_id', array(
				'label' => 'Registration to transfer this payment to',
				'options' => Set::combine($unpaid, '{n}.Registration.id', '{n}.Event.name'),
				'empty' => 'Select one:',
		));

		echo $this->ZuluruForm->input('transfer_from_notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'after' => $this->Html->para(null, 'These notes will be attached to the new payment record on the original registration, and are only visible to admins.'),
		));

		echo $this->ZuluruForm->input('transfer_to_notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'default' => "Transferred from registration {$payment['Registration']['id']} for {$payment['Registration']['Event']['name']}",
				'after' => $this->Html->para(null, 'These notes will be attached to the new payment record on the new registration, and are only visible to admins.'),
		));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
