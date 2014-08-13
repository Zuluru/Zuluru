<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Registration', true));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$defaults = array('empty' => __('Use default', true));
} else {
	$defaults = array('empty' => false);
}
echo $this->ZuluruForm->create('Settings', array(
		'url' => Router::normalize($this->here),
        'inputDefaults' => $defaults,
));

echo $this->element('settings/banner');
?>
	<fieldset>
		<legend><?php __('Registration Configuration'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'registration',
			'name' => 'order_id_format',
			'options' => array(
				'label' => __('Order ID format string', true),
				'after' => __('sprintf format string for the unique order ID.', true),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'allow_tentative',
		'options' => array(
			'label' => __('Allow tentative members to register?', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Tentative members include those whose accounts have not yet been approved but don\'t appear to be duplicates of existing accounts, and those who have registered for membership and called to arrange an offline payment which has not yet been received.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'register_now',
		'options' => array(
			'label' => __('Include "register now" link?', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('By enabling this, you will allow users to register for events directly from the wizard or event list, without going through the "view details" page. If you have various similar events, you should disable this so that people must see the description instead of just the name, decreasing confusion and incorrect registrations.', true),
		),
	));

	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'registration',
			'name' => 'online_payments',
			'options' => array(
				'label' => __('Online payments', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('Do we handle online payments?', true),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'refund_policy_text',
		'options' => array(
			'label' => __('Text of refund policy', true),
			'type' => 'textarea',
			'after' => __('Customize the text of your refund policy, to be shown on registration pages and invoices.', true),
			'class' => 'mceSimple',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'offline_options',
		'options' => array(
			'label' => __('Offline options', true),
			'type' => 'text',
			'after' => __('List the offline payment options you offer, or provide generic text. This will go in the sentence "If you prefer to pay offline (via ____), the ...".', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'offline_payment_text',
		'options' => array(
			'label' => __('Text of offline payment directions', true),
			'type' => 'textarea',
			'after' => __('Customize the text of your offline payment policy. If this is blank, offline payment options will not be offered.', true),
			'class' => 'mceSimple',
		),
	));
	?>
	</fieldset>
	<fieldset>
		<legend><?php __('Waiting List'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'waiting_list',
		'options' => array(
			'label' => __('Allow people to put themselves on a waiting list when events fill up?', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
		),
	));

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'delete_unpaid',
		'options' => array(
			'label' => __('Delete unpaid', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If this is enabled, any registrations which are still unpaid when the final spot is taken will be deleted; the argument for this is that, if someone hasn\'t paid yet, they have probably changed their mind, and leaving them at the front of the waiting list will only delay acceptance of others who are interested. If this is disabled, unpaid registrations will be moved to the front of the waiting list; the argument for this is that they did register first, so sending them to the back of the line may not be fair. Either way, you may want to publish a policy clearly stating your choice and reasons.', true),
		),
	));

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'reservation_time',
		'options' => array(
			'label' => __('Reservation time', true),
			'after' => __('When a spot opens up, the next person on the waiting list is moved to "Reserved" status and notified via email. This setting determines how long (in hours) we will give them to pay before dropping them and moving to the next person. Keep in mind that emails may be sent at any time, so this should be set no lower than 12, and preferably 24 or higher. If a negative response is received at any time in this window, the process will continue immediately; this is a "worst-case" setting. A value of 0 will disable this and require manual expiry of reservations by an admin.', true),
			'size' => 6,
		),
	));
	?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
