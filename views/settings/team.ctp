<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Team', true));
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
		<legend><?php __('Team Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'franchises',
		'options' => array(
			'label' => __('Handle franchises', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable linking of teams through franchises.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'shirt_colour',
		'options' => array(
			'label' => __('Shirt colours', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Disable this if teams don\'t have predetermined shirt colours (e.g. if you use pinnies or if matching shirt colours on a team is unimportant).', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'shirt_numbers',
		'options' => array(
			'label' => __('Shirt numbers', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable everything to do with shirt numbers. If enabled here, teams can still opt not to use this feature.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'attendance',
		'options' => array(
			'label' => __('Attendance tracking', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable everything to do with attendance tracking. If enabled here, teams can still opt not to use this feature.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'urls',
		'options' => array(
			'label' => __('Allow URLs', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable attachment of URLs to team and franchise records.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'flickr',
		'options' => array(
			'label' => __('Flickr', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable attachment of Flickr slideshows to team records.', true),
		),
	));
	?>
	</fieldset>

	<fieldset>
		<legend><?php __('Location Preference Features'); ?></legend>
		<p class="warning-message"><?php printf(__('Any or all of these options may be enabled; %s will be allocated in order of most specific available preference to least.', true), __(Configure::read('ui.fields'), true)); ?></p>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'home_field',
		'options' => array(
			'label' => __('Home field', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => sprintf(__('If enabled, administrators will be able to assign home %s to teams. Teams with home %s will be scheduled there whenever possible.', true),
					__(Configure::read('ui.fields'), true), __(Configure::read('ui.fields'), true)
			),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'facility_preference',
		'options' => array(
			'label' => __('Facility preference', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, teams will be allowed to set a list of preferred facilities for scheduling.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'region_preference',
		'options' => array(
			'label' => __('Region preference', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, teams will be allowed to set a regional preference for scheduling.', true),
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
			'label' => __('Force roster request responses', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, players will be forced to respond to roster requests the next time they sign on. It is recommended to use either this or Generate Roster Emails, not both.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'generate_roster_email',
		'options' => array(
			'label' => __('Generate roster email', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, emails will be sent to players invited to join rosters, and captains who have players request to join their teams. It is recommended to use either this or Force Roster Request Responses, not both.', true),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
