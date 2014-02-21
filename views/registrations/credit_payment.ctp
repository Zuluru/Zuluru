<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($payment['Registration']['Person']['full_name']);
$this->Html->addCrumb ($payment['Registration']['Event']['name']);
$this->Html->addCrumb (sprintf(__('%s Payment', true), __('Credit', true)));
?>

<div class="registrations form">
<h2><?php printf(__('%s Payment', true), __('Credit', true)); ?></h2>
<?php echo $this->Form->create('Payment', array('url' => Router::normalize($this->here)));?>

	<fieldset>
		<legend><?php __('Credit Details'); ?></legend>
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

		echo $this->ZuluruForm->input('payment_notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'default' => $payment['Payment']['notes'],
				'after' => $this->Html->para(null, 'These notes will be preserved with the original registration, and are only visible to admins.'),
		));

		echo $this->ZuluruForm->input('credit_notes', array(
				'type' => 'textbox',
				'cols' => 72,
				'default' => "Credit for registration for {$payment['Registration']['Event']['name']}",
				'after' => $this->Html->para(null, 'These notes will be attached to the new credit record, and will be visible by the player in question.'),
		));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
