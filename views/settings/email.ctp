<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Email', true));
?>

<div class="settings form">
<?php
if ($affiliate) {
	$defaults = array('empty' => 'Use default');
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
				'after' => 'The name (or descriptive role) of the system administrator. Mail from <?php echo ZULURU; ?> will come from this name.',
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'admin_email',
			'options' => array(
				'after' => 'The e-mail address of the system administrator. Mail from <?php echo ZULURU; ?> will come from this address.',
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'support_email',
			'options' => array(
				'after' => 'The e-mail address for system support. This address will be linked for bug reports, etc.',
			),
		));
	}
	if (Configure::read('scoring.incident_reports')) {
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'incident_report_email',
			'options' => array(
				'after' => 'The e-mail address to send incident reports to, if enabled.',
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'email',
		'name' => 'emogrifier',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Use Emogrifier',
			'after' => 'Enable or disable usage of the Emogrifier email style pre-processor.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
