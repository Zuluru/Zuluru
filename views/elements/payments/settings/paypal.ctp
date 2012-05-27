	<fieldset>
 		<legend><?php __('Paypal Options'); ?></legend>
	<p>To find this information, log in to <a href="http://paypal.com/">PayPal</a>, then go to My Account -> Profile -> My selling tools -> Selling online -> API access -> <a href="https://www.paypal.com/ca/cgi-bin/webscr?cmd=_profile-api-signature">View API signature</a>.</p>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_user',
		'options' => array(
			'label' => 'Live API Username',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_password',
		'options' => array(
			'label' => 'Live API Password',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_live_signature',
		'options' => array(
			'label' => 'Live Signature',
		),
	));
	?>
	<p>To do any testing of your registration system, you need a <a href="https://www.x.com/developers/paypal/documentation-tools/paypal-apis-getting-started-guide">PayPal Sandbox account</a>.</p>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_user',
		'options' => array(
			'label' => 'Sandbox API Username',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_password',
		'options' => array(
			'label' => 'Sandbox API Password',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'paypal_test_signature',
		'options' => array(
			'label' => 'Sandbox Signature',
		),
	));
	?>
	</fieldset>
