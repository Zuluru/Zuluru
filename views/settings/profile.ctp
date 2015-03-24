<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Profile', true));
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
		<legend><?php __('Profile Requirements'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'first_name',
		'options' => array(
			'label' => __('First name', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'last_name',
		'options' => array(
			'label' => __('Last name', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'gender',
		'options' => array(
			'label' => __('Gender', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_required'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_street',
		'options' => array(
			'label' => __('Street address', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_city',
		'options' => array(
			'label' => __('City', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_prov',
		'options' => array(
			'label' => __('Province', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_country',
		'options' => array(
			'label' => __('Country', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'addr_postalcode',
		'options' => array(
			'label' => __('Postal code', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'home_phone',
		'options' => array(
			'label' => __('Home phone', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'work_phone',
		'options' => array(
			'label' => __('Work phone', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'mobile_phone',
		'options' => array(
			'label' => __('Mobile phone', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'skill_level',
		'options' => array(
			'label' => __('Skill level', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'year_started',
		'options' => array(
			'label' => __('Year started', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'birthdate',
		'options' => array(
			'label' => __('Birthdate', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'birth_year_only',
		'options' => array(
			'label' => __('Birth year only', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, the system will not ask for birth month and day.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'height',
		'options' => array(
			'label' => __('Height', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'shirt_size',
		'options' => array(
			'label' => __('Shirt size', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_registration'),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'profile',
		'name' => 'contact_for_feedback',
		'options' => array(
			'label' => __('Contact for feedback', true),
			'type' => 'radio',
			'options' => Configure::read('options.access_optional'),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
