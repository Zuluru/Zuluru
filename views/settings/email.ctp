<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Email', true));
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
		<legend><?php __('Sender'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'admin_name',
			'options' => array(
				'label' => __('Admin name', true),
				'after' => sprintf(__('The name (or descriptive role) of the system administrator. Mail from %s will come from this name.', true), ZULURU),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'admin_email',
			'options' => array(
				'label' => __('Admin email', true),
				'after' => sprintf(__('The e-mail address of the system administrator. Mail from %s will come from this address.', true), ZULURU),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'support_email',
			'options' => array(
				'label' => __('Support email', true),
				'after' => __('The e-mail address for system support. This address will be linked for bug reports, etc.', true),
			),
		));
	}
	if (Configure::read('scoring.incident_reports')) {
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'incident_report_email',
			'options' => array(
				'label' => __('Incident report email', true),
				'after' => __('The e-mail address to send incident reports to, if enabled.', true),
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'email',
		'name' => 'emogrifier',
		'options' => array(
			'label' => __('Use Emogrifier', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable usage of the Emogrifier email style pre-processor.', true),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
