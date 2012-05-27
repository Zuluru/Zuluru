<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Registration', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('registration')));?>
	<fieldset>
 		<legend><?php __('Registration Configuration'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'registration',
		'name' => 'order_id_format',
		'options' => array(
			'label' => 'Order ID format string',
			'after' => 'sprintf format string for the unique order ID.',
		),
	));
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
		'name' => 'online_payments',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Do we handle online payments?',
		),
	));
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
