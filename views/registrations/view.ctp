<?php
$this->Html->addCrumb (__('Registrations', true));
$this->Html->addCrumb ($registration['Person']['full_name']);
$this->Html->addCrumb ($registration['Event']['name']);
$this->Html->addCrumb (__('View', true));
?>

<?php
// Perhaps remove manager status, if we're looking at a different affiliate
if ($is_manager) {
	if (!in_array($registration['Event']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
		$is_manager = false;
	}
}
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
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Registration']['created']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['Registration']['modified']; ?>

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

<?php if (($is_admin || $is_manager) && $registration['RegistrationAudit']['id'] != null):?>
<fieldset><legend><?php __('Payment Details');?></legend>
	<div class="related">
		<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Response Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['response_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('ISO Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['iso_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Date');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['date'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Time');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['time'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transaction Id');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['transaction_id'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Approval Code');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['approval_code'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Transaction Name');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['transaction_name'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Charge Total');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['charge_total'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Cardholder');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['cardholder'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Expiry');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['expiry'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('F4L4');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['f4l4'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Card');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['card'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Message');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['message'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['issuer'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer Invoice');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['issuer_invoice'];?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Issuer Confirmation');?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $registration['RegistrationAudit']['issuer_confirmation'];?>
			&nbsp;
		</dd>
		</dl>
	</div>
</fieldset>
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
