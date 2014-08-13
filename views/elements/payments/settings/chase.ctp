	<fieldset>
		<legend><?php __('Chase Paymentech Options'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_store',
		'options' => array(
			'label' => __('Live payment page ID', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_password',
		'options' => array(
			'label' => __('Live transaction key', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_live_response',
		'options' => array(
			'label' => __('Live response key', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_store',
		'options' => array(
			'label' => __('Test payment page ID', true),
			'after' => printf(__('These test settings are only required if you are doing test payments through %s', true), $this->Html->link('rpm.demo.e-xact.com', 'https://rpm.demo.e-xact.com/'))
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_password',
		'options' => array(
			'label' => __('Test transaction key', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'chase_test_response',
		'options' => array(
			'label' => __('Test response key', true),
		),
	));
	?>
	</fieldset>
