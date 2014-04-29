<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Team', true));
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
		<legend><?php __('Team Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'franchises',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle franchises',
			'after' => 'Enable or disable linking of teams through franchises.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'shirt_colour',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Shirt Colours',
			'after' => 'Disable this if teams don\'t have predetermined shirt colours (e.g. if you use pinnies or if matching shirt colours on a team is unimportant).',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'shirt_numbers',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable everything to do with shirt numbers. If enabled here, teams can still opt not to use this feature.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'attendance',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Attendance Tracking',
			'after' => 'Enable or disable everything to do with attendance tracking. If enabled here, teams can still opt not to use this feature.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'urls',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Allow URLs',
			'after' => 'Enable or disable attachment of URLs to team and franchise records.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'flickr',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable attachment of Flickr slideshows to team records.',
		),
	));
	?>
	</fieldset>

	<fieldset>
		<legend><?php __('Location Preference Features'); ?></legend>
	<?php
	echo $this->Html->para('warning-message', __('Any or all of these options may be enabled; fields will be allocated in order of most specific available preference to least.', true));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'home_field',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, administrators will be able to assign home ' . Configure::read('ui.fields') . ' to teams. Teams with home fields will be scheduled there whenever possible.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'facility_preference',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, teams will be allowed to set a list of preferred facilities for scheduling.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'region_preference',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, teams will be allowed to set a regional preference for scheduling.',
		),
	));
	?>
	</fieldset>

	<fieldset>
		<legend><?php __('Roster Management Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'force_roster_request',
		'options' => array(
			'type' => 'radio',
			'label' => 'Force Roster Request Responses',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, players will be forced to respond to roster requests the next time they sign on. It is recommended to use either this or Generate Roster Emails, not both.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'generate_roster_email',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, emails will be sent to players invited to join rosters, and captains who have players request to join their teams. It is recommended to use either this or Force Roster Request Responses, not both.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
