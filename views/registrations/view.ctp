<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb ($registration['Person']['full_name']);
$this->Html->addCrumb ($registration['Event']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	if (!in_array($registration['Event']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'))) {
		$is_manager = false;
	}
}

$balance = $registration['Registration']['total_amount'];
?>

<div class="registrations view">
<h2><?php printf(__('View %s', true), __('Registration', true));?></h2>
<fieldset><legend><?php __('Registration Details'); ?></legend>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Order ID'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php printf (Configure::read('registration.order_id_format'), $registration['Registration']['id']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->element('people/block', array('person' => $registration)); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('User ID'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Person']['id']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Event'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Total Amount'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Registration']['total_amount']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->datetime($registration['Registration']['created']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->datetime($registration['Registration']['modified']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Payment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Registration']['payment']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Notes'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Registration']['notes']; ?>
			&nbsp;
		</dd>
	</dl>
</fieldset>

<?php if (($is_admin || $is_manager)):?>
	<?php foreach ($registration['Payment'] as $payment): ?>
<fieldset><legend><?php __('Payment');?></legend>
	<div class="related">
		<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Payment Type');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['payment_type'];?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Payment Amount');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			echo $payment['payment_amount'];
			if ($payment['refunded_amount'] != 0) {
				echo ' ' . $this->Html->tag('span', sprintf(__('(%s refunded; see below)', true), $payment['refunded_amount']), array('class' => 'warning-message'));
			}
			?>
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Payment Date');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->date($payment['created']);?>
		</dd>
		<?php if (!empty($payment['notes'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Notes');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['notes'];?>
		</dd>
		<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Entered By');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			if ($payment['created_person_id'] !== null) {
				echo $this->UserCache->read('Person.full_name', $payment['created_person_id']);
			} else {
				__('Online payment');
			}
			?>
		</dd>
		<?php if ($payment['updated_person_id'] !== null): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Updated By');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->UserCache->read('Person.full_name', $payment['updated_person_id']); ?>
		</dd>
		<?php endif; ?>
		<?php if (!empty($payment['RegistrationAudit'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Response Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['response_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('ISO Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['iso_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['date'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Time');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['time'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transaction Id');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['transaction_id'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Approval Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['approval_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transaction Name');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['transaction_name'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Charge Total');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['charge_total'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Cardholder');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['cardholder'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Expiry');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['expiry'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('F4L4');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['f4l4'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Card');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['card'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Message');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['message'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['issuer'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer Invoice');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['issuer_invoice'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer Confirmation');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $payment['RegistrationAudit']['issuer_confirmation'];?>
			&nbsp;
		</dd>
		<?php endif; ?>
		</dl>
	</div>
	<?php if ($payment['payment_amount'] != $payment['refunded_amount'] && in_array($payment['payment_type'], Configure::read('payment_payment'))): ?>
	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('Issue Refund', true), array('action' => 'refund_payment', 'payment' => $payment['id'])); ?></li>
			<li><?php echo $this->Html->link(__('Issue Credit', true), array('action' => 'credit_payment', 'payment' => $payment['id'])); ?></li>
			<li><?php echo $this->Html->link(__('Transfer Payment', true), array('action' => 'transfer_payment', 'payment' => $payment['id'])); ?></li>
		</ul>
	</div>
	<?php endif; ?>
</fieldset>
	<?php
		$balance -= $payment['payment_amount'];
	endforeach;
	?>

	<?php if (in_array($registration['Registration']['payment'], Configure::read('registration_unpaid')) && $balance > 0): ?>
<fieldset><legend><?php __('Balance'); ?></legend>
	<div class="related">
	<?php echo $this->Html->para('warning-message', sprintf(__('There is an outstanding balance of $%0.2f.', true), $balance)); ?>
		<div class="actions">
			<ul>
				<li><?php echo $this->ZuluruHtml->link(__('Add Payment', true), array('action' => 'add_payment', 'registration' => $registration['Registration']['id'])); ?> </li>
			</ul>
		</div>
	</div>
</fieldset>
	<?php endif; ?>
<?php endif; ?>

<?php if (!empty($registration['Response'])):?>
<fieldset><legend><?php __('Registration Answers'); ?></legend>
	<div class="related">
<?php echo $this->element ('questionnaires/view', array('questionnaire' => $registration['Event']['Questionnaire'], 'response' => $registration)); ?>

	</div>
</fieldset>
<?php endif; ?>
</div>

<div class="actions">
	<ul>
		<li><?php if ($is_admin || $is_manager) echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'registration' => $registration['Registration']['id'], 'return' => true)); ?> </li>
		<li><?php echo $this->Html->link(__('Unregister', true),
				array('action' => 'unregister', 'registration' => $registration['Registration']['id']),
				null,
				__('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.', true));
		?></li>
	</ul>
</div>
