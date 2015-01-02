<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('User', true));
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
		<legend><?php __('User Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'auto_approve',
		'options' => array(
			'label' => __('Automatically approve new user accounts', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('By enabling this, you reduce administrative work and minimize delays for users. However, you also lose the ability to detect and eliminate duplicate accounts.', true) . ' ' .
					$this->Html->tag('span', __('Use of this feature is recommended only for brand new sites wanting to ease the transition for their members.', true), array('class' => 'warning-message')),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'antispam',
		'options' => array(
			'label' => __('Anti-spam measures', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable this to add honeypot-style anti-spam measures to the "create account" page. These measures are generally invisible to users.', true),
		),
	));
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'multiple_affiliates',
			'options' => array(
				'label' => __('Enable joining multiple affiliates', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('Allow users to join multiple affiliates (only applicable if affiliates are enabled above).', true),
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'photos',
		'options' => array(
			'label' => __('Photos', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable the option for people to upload profile photos.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'approve_photos',
		'options' => array(
			'label' => __('Approve photos', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If enabled, profile photos must be approved by an administrator before they will be visible.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'gravatar',
		'options' => array(
			'label' => __('Gravatar', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable the option for people to use Gravatar for their photo.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'documents',
		'options' => array(
			'label' => __('Handle document uploads', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable uploading of documents by people (e.g. as an alternative to faxing or emailing).', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'annotations',
		'options' => array(
			'label' => __('Enable annotations', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => sprintf(__('Allow people to attach notes to other people, teams, games and %s.', true), Configure::read('ui.fields')),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'tasks',
		'options' => array(
			'label' => __('Enable tasks', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable the management and assignment of tasks.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'dog_questions',
		'options' => array(
			'label' => __('Dog questions', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable questions and options about dogs.', true),
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
