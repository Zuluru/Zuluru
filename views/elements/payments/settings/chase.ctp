	<fieldset>
		<legend><?php __('Chase Paymentech Options'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_store',
		'options' => array(
			'label' => 'Live Payment Page ID',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_password',
		'options' => array(
			'label' => 'Live Transaction Key',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_response',
		'options' => array(
			'label' => 'Live Response Key',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_store',
		'options' => array(
			'label' => 'Test Payment Page ID',
			'after' => $this->Html->para(null, 'These test settings are only required if you are doing test payments through ' . $this->Html->link('rpm.demo.e-xact.com', 'https://rpm.demo.e-xact.com/')),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_password',
		'options' => array(
			'label' => 'Test Transaction Key',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_response',
		'options' => array(
			'label' => 'Test Response Key',
		),
	));
	?>
	</fieldset>
