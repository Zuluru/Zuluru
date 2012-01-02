	<fieldset>
 		<legend><?php __('Moneris Options'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_live_store',
		'options' => array(
			'label' => 'Live Store ID',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_live_password',
		'options' => array(
			'label' => 'Live Store Password',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_test_store',
		'options' => array(
			'label' => 'Test Store ID',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_test_password',
		'options' => array(
			'label' => 'Test Store Password',
		),
	));
	?>
	</fieldset>
