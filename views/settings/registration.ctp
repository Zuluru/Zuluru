<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Registration', true));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$defaults = array('empty' => 'Use default');
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
				'label' => 'Order ID format string',
				'after' => 'sprintf format string for the unique order ID.',
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'allow_tentative',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Allow tentative members to register?',
			'after' => 'Tentative members include those whose accounts have not yet been approved but don\'t appear to be duplicates of existing accounts, and those who have registered for membership and called to arrange an offline payment which has not yet been received.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'register_now',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Include "register now" link?',
			'after' => 'By enabling this, you will allow users to register for events directly from the wizard or event list, without going through the "view details" page. If you have various similar events, you should disable this so that people must see the description instead of just the name, decreasing confusion and incorrect registrations.',
		),
	));

	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'registration',
			'name' => 'online_payments',
			'options' => array(
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => 'Do we handle online payments?',
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'refund_policy_text',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Text of refund policy',
			'after' => 'Customize the text of your refund policy, to be shown on registration pages and invoices.',
			'class' => 'mceSimple',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'offline_payment_text',
		'options' => array(
			'type' => 'textarea',
			'label' => 'Text of offline payment directions',
			'after' => 'Customize the text of your offline payment policy.',
			'class' => 'mceSimple',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
