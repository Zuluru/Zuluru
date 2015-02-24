<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Organization', true));
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
		<legend><?php __('Organization'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'organization',
			'name' => 'name',
			'options' => array(
				'label' => __('Name', true),
				'after' => __('Your organization\'s full name.', true),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'short_name',
		'options' => array(
			'label' => __('Short name', true),
			'after' => __('Your organization\'s abbreviated name or acronym.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'address',
		'options' => array(
			'label' => __('Address', true),
			'after' => __('Your organization\'s street address.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'address2',
		'options' => array(
			'label' => __('Unit', true),
			'after' => __('Your organization\'s unit number, if any.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'city',
		'options' => array(
			'label' => __('City', true),
			'after' => __('Your organization\'s city.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'province',
		'options' => array(
			'label' => __('Province', true),
			'type' => 'select',
			'options' => $provinces,
			'after' => __('Your organization\'s province or state.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'country',
		'options' => array(
			'label' => __('Country', true),
			'type' => 'select',
			'options' => $countries,
			'after' => __('Your organization\'s country.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'postal',
		'options' => array(
			'label' => __('Postal code', true),
			'after' => __('Your organization\'s postal code.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'phone',
		'options' => array(
			'label' => __('Phone', true),
			'after' => __('Your organization\'s phone number.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'notice',
		'options' => array(
			'type' => 'textarea',
			'label' => __('Announcement Text', true),
			'after' => __('Optional announcement text to display at the top of the home page.', true),
			'class' => 'mceAdvanced',
		),
	));
	?>
	</fieldset>

	<fieldset>
		<legend><?php __('Location and Mapping'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'latitude',
		'options' => array(
			'label' => __('Latitude', true),
			'after' => __('Latitude in decimal degrees for game location (center of city). Used for calculating sunset times.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'longitude',
		'options' => array(
			'label' => __('Longitude', true),
			'after' => __('Longitude in decimal degrees for game location (center of city). Used for calculating sunset times.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'site',
		'name' => 'gmaps_key',
		'options' => array(
			'label' => __('Google Maps API V3 key', true),
			'after' => sprintf(__('A key for the %s. Required for rendering custom Google Maps.', true),
					$this->Html->link(__('Google Maps API V3', true), 'http://code.google.com/apis/maps/documentation/javascript/tutorial.html#api_key')
			),
		),
	));
	?>
	</fieldset>

	<?php
	$seasons = Configure::read('options.season');
	unset($seasons['None']);
	if (!empty($seasons)):
	?>
	<fieldset>
		<legend><?php __('Dates'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'first_day',
		'options' => array(
			'label' => __('First day', true),
			'type' => 'select',
			'options' => array(
				// Numbering matches the PHP date('N') format
				1 => __('Monday', true),
				2 => __('Tuesday', true),
				3 => __('Wednesday', true),
				4 => __('Thursday', true),
				5 => __('Friday', true),
				6 => __('Saturday', true),
				7 => __('Sunday', true),
			),
			'after' => __('First day of the week, for scheduling purposes.', true),
		),
	));
	?>
	<p><?php printf(__('The following settings are used for determining which season is currently in effect, for the purposes of providing links to current %s permits.', true), __(Configure::read('ui.field'), true)); ?></p>
	<?php
	foreach ($seasons as $season) {
		$season = low($season);
		$season_key = Inflector::slug($season);
		echo $this->element('settings/input', array(
			'category' => 'organization',
			'name' => "{$season_key}_start",
			'options' => array(
				'label' => __(Inflector::humanize($season), true) . ' ' . __('start', true),
				'type' => 'date',
				'dateFormat' => 'MD',
				'after' => sprintf(__('First day that would be considered for a %s game', true), __($season, true)),
			),
		));
	}
	?>
	</fieldset>
	<?php endif; ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php if (Configure::read('feature.tiny_mce')) $this->TinyMce->editor('advanced'); ?>
