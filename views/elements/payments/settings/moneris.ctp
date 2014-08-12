	<fieldset>
		<legend><?php __('Moneris Options'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_live_store',
		'options' => array(
			'label' => __('Live store ID', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_live_password',
		'options' => array(
			'label' => __('Live store password', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_test_store',
		'options' => array(
			'label' => __('Test store ID', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'payment',
		'name' => 'moneris_test_password',
		'options' => array(
			'label' => __('Test store password', true),
		),
	));
	?>
	</fieldset>
