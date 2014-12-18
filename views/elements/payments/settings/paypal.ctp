	<fieldset>
		<legend><?php __('Paypal Options'); ?></legend>
	<p><?php printf (__('To find this information, log in to %s, then go to My Account -> Profile -> My selling tools -> Selling online -> API access -> Update, then Request API Credentials or %s.', true),
			$this->Html->link(__('PayPal', true), 'http://paypal.com/'),
			$this->Html->link(__('View API signature', true), 'https://www.paypal.com/ca/cgi-bin/webscr?cmd=_profile-api-signature')
	);
	?></p>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_user',
		'options' => array(
			'label' => __('Live API username', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_password',
		'options' => array(
			'label' => __('Live API password', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_signature',
		'options' => array(
			'label' => __('Live signature', true),
		),
	));
	?>
	<p><?php printf (__('To do any testing of your registration system, you need a %s.', true),
			$this->Html->link(__('PayPal Sandbox account', true), 'https://www.x.com/developers/paypal/documentation-tools/paypal-apis-getting-started-guide')
	);
	?></p>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_user',
		'options' => array(
			'label' => __('Sandbox API username', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_password',
		'options' => array(
			'label' => __('Sandbox API password', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_signature',
		'options' => array(
			'label' => __('Sandbox signature', true),
		),
	));
	?>
	</fieldset>
