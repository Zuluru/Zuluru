<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb (__('Checkout', true));
?>

<div class="registrations checkout form">
<h2><?php __('Registration Checkout');?></h2>

<?php
$order_id_format = Configure::read('registration.order_id_format');

if (!empty($registrations)):
	echo $this->Html->para(null, __('These are your current unpaid registrations. <span class="highlight-message">Payment completes your registration and confirms your booking/purchase.</span>', true));
?>
<div>
	<div class="caption">
	<?php
		if ($this->UserCache->currentId() != $this->UserCache->realId()) {
			$title = sprintf(__('Add something else for %s', true), $this->UserCache->read('Person.full_name'));
		} else {
			$title = __('Add something else', true);
		}
		echo $this->ZuluruHtml->iconLink('cart_add.png', array('controller' => 'events', 'action' => 'wizard'), array('title' => $title));
		echo $this->Html->para(null, $this->Html->link($title, array('controller' => 'events', 'action' => 'wizard')));
	?>
	</div>

	<div class="caption">
	<?php
	echo $this->ZuluruHtml->iconLink('cart_remove.png', '#', array('class' => 'show_unregister', 'title' => __('Click for instructions', true)));
	echo $this->Html->para(null, $this->Html->link(__('Remove something', true), '#', array('class' => 'show_unregister', 'title' => __('Click for instructions', true))));
	$this->Js->get('.show_unregister')->event('click', 'jQuery(".register_help").hide(); jQuery(".unregister_help").show();');
	?>
	</div>

	<?php
	$test_payments = Configure::read('payment.test_payments');
	if (Configure::read('registration.online_payments') && ($test_payments <= 1 || ($is_admin && Configure::read('payment.test_payments') == 2))):
	?>
	<div class="caption">
	<?php
		echo $this->ZuluruHtml->iconLink('pay_online.png', '#', array('class' => 'show_online', 'title' => __('Click for instructions', true)));
		echo $this->Html->para(null, $this->Html->link(__('Pay online', true), '#', array('class' => 'show_online', 'title' => __('Click for instructions', true))));
		$this->Js->get('.show_online')->event('click', 'jQuery(".register_help").hide(); jQuery(".online_help").show();');
	?>
	</div>
	<?php endif; ?>

	<?php if (Configure::read('registration.offline_payment_text')): ?>
	<div class="caption">
	<?php
	echo $this->ZuluruHtml->iconLink('pay_offline.png', '#', array('class' => 'show_offline', 'title' => __('Click for instructions', true)));
	echo $this->Html->para(null, $this->Html->link(__('Pay offline', true), '#', array('class' => 'show_offline', 'title' => __('Click for instructions', true))));
	$this->Js->get('.show_offline')->event('click', 'jQuery(".register_help").hide(); jQuery(".offline_help").show();');
	?>
	</div>
	<?php endif; ?>

	<?php if (!empty($person['Credit'])): ?>
	<div class="caption">
	<?php
	echo $this->ZuluruHtml->iconLink('redeem.png', '#', array('class' => 'show_credit', 'title' => __('Click for instructions', true)));
	echo $this->Html->para(null, $this->Html->link(__('Redeem credit', true), '#', array('class' => 'show_credit', 'title' => __('Click for instructions', true))));
	$this->Js->get('.show_credit')->event('click', 'jQuery(".register_help").hide(); jQuery(".credit_help").show();');
	?>
	<?php endif; ?>
</div>

<div class="clear">&nbsp;</div>

<div class="unregister_help register_help">
	<p><?php echo $this->ZuluruHtml->icon('help_24.png'); ?></p>
	<p><?php __('To remove an item, click the "Unregister" button next to it.'); ?></p>
	<p><?php __('Note that this will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.'); ?></p>
</div></li>

<div class="online_help register_help">
<?php
	$provider = Configure::read('payment.payment_implementation');
	if ($provider == 'paypal') {
		$button = 'Check out with PayPal';
	} else {
		$button = 'Pay';
	}
?>
	<p><?php echo $this->ZuluruHtml->icon('help_24.png'); ?></p>
	<p><?php printf(__('To pay online with %s, click the "%s" button below.', true), Configure::read('payment.options'), $button); ?></p>
	<?php echo Configure::read('registration.online_payment_text'); ?>
</div>

<div class="offline_help register_help">
<?php
echo $this->Html->para(null, $this->ZuluruHtml->icon('help_24.png'));
echo $this->element('payments/offline');
?>
</div>

<div class="credit_help register_help">
	<p><?php echo $this->ZuluruHtml->icon('help_24.png'); ?></p>
	<p><?php __('To redeem a credit, click the "Redeem credit" button next to the registration that you want the credit to be applied to.'); ?></p>
	<p><?php __('You will be given options on the resulting page, including opting not to redeem the credit at this time.'); ?></p>
</div></li>

<?php
	$this->Js->buffer('
jQuery(".register_help").hide();
	');
?>

<table class="list">
	<tr>
		<th><?php __('Order ID'); ?></th>
		<th><?php __('Event'); ?></th>
		<th><?php __('Balance'); ?></th>
		<th><?php __('Actions'); ?></th>
	</tr>
<?php
	$total = $i = 0;
	foreach ($registrations as $registration):
		list ($cost, $tax1, $tax2) = Registration::paymentAmounts($registration);
		$total += $cost + $tax1 + $tax2;

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
?>
	<tr<?php echo $class;?>>
		<td><?php printf ($order_id_format, $registration['Registration']['id']); ?></td>
		<td><?php
		echo $this->Html->link (Registration::longDescription($registration), array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id']));
		if ($registration['Registration']['payment'] == 'Reserved') {
			echo ' (' . __('Reserved until', true) . ' ' . $this->ZuluruTime->datetime($registration['Registration']['reservation_expires']) . ')';
		}
		?></td>
		<td><?php echo $this->Number->currency ($cost + $tax1 + $tax2); ?></td>
		<td class="actions"><?php
		echo $this->Html->link (__('Edit', true),
				array('action' => 'edit', 'registration' => $registration['Registration']['id']));
		if (in_array($registration['Registration']['payment'], Configure::read('registration_none_paid'))) {
			echo $this->Html->link (__('Unregister', true),
					array('action' => 'unregister', 'registration' => $registration['Registration']['id']),
					array(),
					__('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.', true));
		}
		if (!empty($person['Credit'])) {
			echo $this->Html->link (__('Redeem credit', true), array('action' => 'redeem', 'registration' => $registration['Registration']['id']));
		}
		?></td>
	</tr>
<?php endforeach; ?>
	<tr>
		<th></th>
		<th><?php __('Total'); ?>:</th>
		<th><?php echo $this->Number->currency ($total); ?></th>
		<th class="actions"><?php
		if (Configure::read('registration.online_payments') && ($test_payments <= 1 || ($is_admin && Configure::read('payment.test_payments') == 2))) {
			echo $this->element("payments/forms/$provider");
		}
		?></th>
	</tr>
</table>
<?php endif; ?>

<?php
if (!empty($other)):
	echo $this->Html->para('error-message', 'You have registered for the following events, but cannot pay right now:');
?>
<table class="list">
	<tr>
		<th><?php __('Order ID'); ?></th>
		<th><?php __('Event'); ?></th>
		<th><?php __('Cost'); ?></th>
		<th><?php __('Reason'); ?></th>
		<th><?php __('Actions'); ?></th>
	</tr>
<?php
	$i = 0;
	foreach ($other as $registration):
		list ($cost, $tax1, $tax2) = Registration::paymentAmounts($registration);
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
?>
	<tr<?php echo $class;?>>
		<td><?php printf ($order_id_format, $registration['Registration']['id']); ?></td>
		<td><?php echo $this->Html->link ($registration['Event']['name'], array('controller' => 'events', 'action' => 'view', 'event' => $registration['Event']['id'])); ?></td>
		<td><?php echo $this->Number->currency ($cost + $tax1 + $tax2); ?></td>
		<td><?php echo $registration['reason']; ?></td>
		<td class="actions"><?php
			if (!empty($registration['change_price'])) {
				echo $this->Html->link (__('Reregister', true),
						array('action' => 'edit', 'registration' => $registration['Registration']['id']));
			}

			if (!in_array($registration['Registration']['payment'], Configure::read('registration_some_paid'))) {
				echo $this->Html->link (__('Unregister', true),
						array('action' => 'unregister', 'registration' => $registration['Registration']['id']),
						array(),
						__('Are you sure you want to unregister from this event? This will delete all of your preferences and you may lose the spot that is currently tentatively reserved for you.', true));
			}
		?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<?php
echo $this->element('payments/refund');
if (Configure::read('registration.online_payments') && stripos(Configure::read('payment.options'), 'interac')) {
	echo $this->Html->para('small', __('&reg; Trade-mark of Interac Inc. Used under licence. <a href="http://www.interaconline.com/learn/" target="_blank">Learn more</a> about INTERAC Online.', true));
}
?>
</div>
