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
		<legend><?php __('User Features'); ?></legend>
	<?php
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'auto_approve',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Automatically approve new user accounts',
			'after' => 'By enabling this, you reduce administrative work and minimize delays for users. However, you also lose the ability to detect and eliminate duplicate accounts. <span class="warning-message">Use of this feature is recommended only for brand new sites wanting to ease the transition for their members.</span>',
		),
	));
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'multiple_affiliates',
			'options' => array(
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'label' => 'Enable joining multiple affiliates',
				'after' => sprintf('Allow users to join multiple affiliates (only applicable if affiliates are enabled above).'),
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'photos',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable the option for players to upload profile photos.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'approve_photos',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, profile photos must be approved by an administrator before they will be visible.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'gravatar',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable the option for players to use Gravatar for their photo.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'documents',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle document uploads',
			'after' => 'Enable or disable uploading of documents by players (e.g. as an alternative to faxing or emailing).',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'annotations',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Enable annotations',
			'after' => sprintf('Allow players to attach notes to other players, teams, games and %s.', Configure::read('ui.fields')),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'tasks',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Enable tasks',
			'after' => 'Enable or disable the management and assignment of tasks.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'dog_questions',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable questions and options about dogs.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
