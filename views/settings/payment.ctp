<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Online Payments', true));
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
		<legend><?php __('Common Options'); ?></legend>
	<?php
	if (!$affiliate) {
		$options = Configure::read('options.payment_provider');
		if (!function_exists('curl_init')) {
			unset($options['paypal']);
		}
		echo $this->element('settings/input', array(
			'category' => 'payment',
			'name' => 'payment_implementation',
			'options' => array(
				'label' => __('Payment implementation', true),
				'id' => 'PaymentProvider',
				'type' => 'select',
				'options' => $options,
				'hide_single' => true,
			),
		));
		if (!function_exists('curl_init')) {
			echo $this->Html->para('warning-message', 'PayPal integration requires the cUrl library, which your installation of PHP does not support. If you need PayPal support, talk to your system administrator or hosting company about enabling cUrl.');
		}
		echo $this->element('settings/input', array(
			'category' => 'payment',
			'name' => 'options',
			'options' => array(
				'label' => __('Options', true),
				'type' => 'text',
				'after' => __('List the payment options offered by your payment provider, or provide generic text. This will go in the sentence "To pay online with ____, click ...".', true),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'registration',
			'name' => 'online_payment_text',
			'options' => array(
				'label' => __('Text of online payment directions', true),
				'type' => 'textarea',
				'after' => __('Customize any text to add to the default online payment directions.', true),
				'class' => 'mceSimple',
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'popup',
		'options' => array(
			'label' => __('Popup', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Handle online payments in a popup window?', true),
		),
	));

	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'payment',
			'name' => 'invoice_implementation',
			'options' => array(
				'label' => __('Invoice implementation', true),
				'type' => 'select',
				'options' => Configure::read('options.invoice'),
				'hide_single' => true,
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'payment',
			'name' => 'reg_id_format',
			'options' => array(
				'label' => __('Event ID format string', true),
				'after' => __('sprintf format string for the event ID, sent to the payment processor as the item number.', true),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'test_payments',
		'options' => array(
			'label' => __('Test payments', true),
			'type' => 'radio',
			'options' => Configure::read('options.test_payment'),
			'after' => __('Who should get test instead of live payments? If set to admins, then admins are the only ones who will get the online payment option.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'currency',
		'options' => array(
			'label' => __('Currency', true),
			'type' => 'radio',
			'options' => Configure::read('options.currency'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'tax1_enable',
		'options' => array(
			'label' => __('Tax1 enable', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable first tax', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'tax1_name',
		'options' => array(
			'label' => __('First tax name', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'tax2_enable',
		'options' => array(
			'label' => __('Tax2 enable', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable second tax', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'tax2_name',
		'options' => array(
			'label' => __('Second tax name', true),
		),
	));
	?>
	</fieldset>
	<?php if (!$affiliate): ?>
	<div id="PaymentProviderFields">
	<?php
	echo $this->element('payments/settings/' . Configure::read('payment.payment_implementation'));
	$this->Js->get('#PaymentProvider')->event('change', $this->Js->request(
			array('action' => 'payment_provider_fields'),
			array('update' => '#PaymentProviderFields', 'dataExpression' => true, 'data' => 'jQuery("#PaymentProvider").get()')
	));
	?>
	</div>
	<?php endif; ?>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('simple'); ?>
