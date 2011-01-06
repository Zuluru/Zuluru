<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Organization', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('organization')));?>
	<fieldset>
 		<legend><?php __('Organization'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'name',
		'options' => array(
			'after' => 'Your organization\'s full name.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'short_name',
		'options' => array(
			'after' => 'Your organization\'s abbreviated name or acronym.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'address',
		'options' => array(
			'after' => 'Your organization\'s street address.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'address2',
		'options' => array(
			'label' => 'Unit',
			'after' => 'Your organization\'s unit number, if any.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'city',
		'options' => array(
			'after' => 'Your organization\'s city.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'province',
		'options' => array(
			'type' => 'select',
			'options' => $provinces,
			'after' => 'Your organization\'s province or state.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'country',
		'options' => array(
			'type' => 'select',
			'options' => $countries,
			'after' => 'Your organization\'s country.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'postal',
		'options' => array(
			'label' => 'Postal Code',
			'after' => 'Your organization\'s postal code.',
		),
	));
	echo $this->element ('setting/input', array(
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
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'latitude',
		'options' => array(
			'after' => 'Latitude in decimal degrees for game location (center of city). Used for calculating sunset times.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'longitude',
		'options' => array(
			'after' => 'Longitude in decimal degrees for game location (center of city). Used for calculating sunset times.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'site',
		'name' => 'gmaps_key',
		'options' => array(
			'label' => 'Google Maps API Key',
			'after' => 'An API key for the <a href="http://www.google.com/apis/maps/signup.html">Google Maps API</a>. Required for rendering custom Google Maps.',
		),
	));
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Dates'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'organization',
		'name' => 'year_end',
		'options' => array(
			'type' => 'select',
			'options' => $this->Form->__generateOptions('month', array('monthNames' => true)),
			'after' => 'Last month of your organization\'s membership year',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
