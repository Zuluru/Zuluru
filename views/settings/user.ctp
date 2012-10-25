<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('User', true));
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
 		<legend><?php __('Profile Requirements'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'first_name',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'last_name',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'gender',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_street',
		'options' => array(
			'type' => 'radio',
			'label' => 'Street Address',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_city',
		'options' => array(
			'type' => 'radio',
			'label' => 'City',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_prov',
		'options' => array(
			'type' => 'radio',
			'label' => 'Province',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_country',
		'options' => array(
			'type' => 'radio',
			'label' => 'Country',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_postalcode',
		'options' => array(
			'type' => 'radio',
			'label' => 'Postal Code',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'home_phone',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'work_phone',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'mobile_phone',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'skill_level',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'year_started',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'birthdate',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'height',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'shirt_size',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'willing_to_volunteer',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'contact_for_feedback',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
