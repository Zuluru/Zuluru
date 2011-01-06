<?php
$this->Html->addCrumb (__('Preferences', true));
$this->Html->addCrumb ("{$person['Person']['first_name']} {$person['Person']['last_name']}");
?>

<div class="settings form">
<?php echo $this->Form->create('People', array('url' => $this->here));?>
	<fieldset>
 		<legend><?php __('Preferences'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'enable_ical',
		'options' => array(
			'label' => 'Enable Personal iCal Feed',
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => '<span class="error-message">NOTE: By enabling this, you agree to make your personal schedule in iCal format available as public information (required for Google Calendar, etc. to be able to access the data)</span>',
		),
	));

	$options = array('' => 'use system default');
	foreach (Configure::read('options.date_formats') as $format) {
		$options[$format] = date($format);
	}
	echo $this->element ('setting/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'date_format',
		'options' => array(
			'type' => 'radio',
			'options' => $options,
			'after' => 'Select your preferred date format',
		),
	));

	$options = array('' => 'use system default');
	foreach (Configure::read('options.day_formats') as $format) {
		$options[$format] = date($format);
	}
	echo $this->element ('setting/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'day_format',
		'options' => array(
			'type' => 'radio',
			'options' => $options,
			'after' => 'Select your preferred day format',
		),
	));

	$options = array('' => 'use system default');
	foreach (Configure::read('options.time_formats') as $format) {
		$options[$format] = date($format);
	}
	echo $this->element ('setting/input', array(
		'person_id' => $id,
		'category' => 'personal',
		'name' => 'time_format',
		'options' => array(
			'type' => 'radio',
			'options' => $options,
			'after' => 'Select your preferred time format',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
