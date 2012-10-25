<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Organization', true));
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
 		<legend><?php __('Organization'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'organization',
			'name' => 'name',
			'options' => array(
				'after' => 'Your organization\'s full name.',
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'short_name',
		'options' => array(
			'after' => 'Your organization\'s abbreviated name or acronym.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'address',
		'options' => array(
			'after' => 'Your organization\'s street address.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'address2',
		'options' => array(
			'label' => 'Unit',
			'after' => 'Your organization\'s unit number, if any.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'city',
		'options' => array(
			'after' => 'Your organization\'s city.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'province',
		'options' => array(
			'type' => 'select',
			'options' => $provinces,
			'after' => 'Your organization\'s province or state.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'country',
		'options' => array(
			'type' => 'select',
			'options' => $countries,
			'after' => 'Your organization\'s country.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'postal',
		'options' => array(
			'label' => 'Postal Code',
			'after' => 'Your organization\'s postal code.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'phone',
		'options' => array(
			'after' => 'Your organization\'s phone number.',
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
			'after' => 'Latitude in decimal degrees for game location (center of city). Used for calculating sunset times.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'organization',
		'name' => 'longitude',
		'options' => array(
			'after' => 'Longitude in decimal degrees for game location (center of city). Used for calculating sunset times.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'site',
		'name' => 'gmaps_key',
		'options' => array(
			'label' => 'Google Maps API V3 Key',
			'after' => 'A key for the <a href="http://code.google.com/apis/maps/documentation/javascript/tutorial.html#api_key">Google Maps API V3</a>. Required for rendering custom Google Maps.',
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
	<p>The following settings are used for determining which season is currently in effect, for the purposes of providing links to current <?php __(Configure::read('ui.field')); ?> permits.</p>
	<?php
	foreach ($seasons as $season) {
		$season = low($season);
		$season_key = Inflector::slug($season);
		echo $this->element('settings/input', array(
			'category' => 'organization',
			'name' => "{$season_key}_start",
			'options' => array(
				'type' => 'date',
				'dateFormat' => 'MD',
				'after' => "First day that would be considered for a $season game",
			),
		));
	}
	?>
	</fieldset>
	<?php endif; ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
