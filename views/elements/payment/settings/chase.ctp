	<fieldset>
 		<legend><?php __('Chase Paymentech Options'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'chase_live_store',
		'options' => array(
			'label' => 'Store ID',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'chase_live_password',
		'options' => array(
			'label' => 'Merchant Transaction Key',
		),
	));
	?>
	</fieldset>
