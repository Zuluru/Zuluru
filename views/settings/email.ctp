<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Email', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('email')));?>
	<fieldset>
 		<legend><?php __('Sender'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'email',
		'name' => 'admin_name',
		'options' => array(
			'after' => 'The name (or descriptive role) of the system administrator. Mail from Zuluru will come from this name.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'email',
		'name' => 'admin_email',
		'options' => array(
			'after' => 'The e-mail address of the system administrator. Mail from Zuluru will come from this address.',
		),
	));
	if (Configure::read('scoring.incident_reports')) {
		echo $this->element('settings/input', array(
			'category' => 'email',
			'name' => 'incident_report_email',
			'options' => array(
				'after' => 'The e-mail address to send incident reports to, if enabled.',
			),
		));
	}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
