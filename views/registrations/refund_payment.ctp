<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($payment['Registration']['Person']['full_name']);
$this->Html->addCrumb ($payment['Registration']['Event']['name']);
$this->Html->addCrumb (sprintf(__('%s Payment', true), __('Refund', true)));
?>

<div class="registrations form">
<h2><?php printf(__('%s Payment', true), __('Refund', true)); ?></h2>
<?php echo $this->Form->create('Payment', array('url' => Router::normalize($this->here)));?>

	<fieldset>
		<legend><?php __('Refund Details'); ?></legend>
	<?php
		echo $this->ZuluruForm->input('amount', array(
				'default' => $payment['Payment']['payment_amount'] - $payment['Payment']['refunded_amount'],
		));

		if (empty($payment['Payment']['registration_audit_id'])) {
			echo $this->Html->para('warning-message', __('This payment was recorded manually, so in addition to noting the refund here you will need to issue a refund manually.', true));
		} else if (!$payment_obj->can_refund) {
			echo $this->Html->para('warning-message', __('Note that your online payment provider does not currently support automatic refunds, so in addition to noting the refund here you will need to issue a refund manually.', true));
		} else {
			echo $this->ZuluruForm->input('online_refund', array(
					'label' => 'Issue refund through online payment provider',
					'type' => 'checkbox',
					'checked' => true,
			));
		}

		if (!in_array($payment['Registration']['payment'], Configure::read('registration_cancelled'))) {
			echo $this->ZuluruForm->input('mark_refunded', array(
					'label' => 'Mark this registration as refunded?',
					'type' => 'checkbox',
					'checked' => true,
			));
		} else {
			echo $this->ZuluruForm->hidden('mark_refunded', array('value' => 0));
		}

		echo $this->ZuluruForm->input('notes', array(
				'type' => 'textbox',
				'cols' => 72,
		));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
