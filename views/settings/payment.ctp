<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Online Payments', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('payment')));?>
	<fieldset>
 		<legend><?php __('Common Options'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'payment_implementation',
		'options' => array(
			'id' => 'PaymentProvider',
			'type' => 'select',
			'options' => Configure::read('options.payment_provider'),
			'hide_single' => true,
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'invoice_implementation',
		'options' => array(
			'type' => 'select',
			'options' => Configure::read('options.invoice'),
			'hide_single' => true,
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'reg_id_format',
		'options' => array(
			'label' => 'Event ID format string',
			'after' => 'sprintf format string for the event ID, sent to the payment processor as the item number.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'test_payments',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.test_payment'),
			'after' => 'Who should get test instead of live payments?',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'currency',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.currency'),
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'tax1_enable',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable first tax',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'tax1_name',
		'options' => array(
			'label' => 'First tax name',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'tax2_enable',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable second tax',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'payment',
		'name' => 'tax2_name',
		'options' => array(
			'label' => 'Second tax name',
		),
	));
	?>
	</fieldset>
	<div id="PaymentProviderFields" style="padding:0; margin:0;">
	<?php // TODOCSS: A class for the above style
	echo $this->element ('payment/settings/' . Configure::read('payment.payment_implementation'));
	$this->Js->get('#PaymentProvider')->event('change', $this->Js->request(
			array('action' => 'payment_provider_fields'),
			array('update' => '#PaymentProviderFields', 'dataExpression' => true, 'data' => '$("#PaymentProvider").get()')
	));
	?>
	</div>

<?php echo $this->Form->end(__('Submit', true));?>
</div>
