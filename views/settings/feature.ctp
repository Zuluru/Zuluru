<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Feature', true));
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
		<legend><?php __('Primary Options'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'site',
			'name' => 'name',
			'options' => array(
				'label' => 'Site Name',
				'after' => 'The name this application will be known as to your users.',
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'affiliates',
			'options' => array(
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'label' => 'Enable affiliates',
				'after' => sprintf('Allow configuration of multiple affiliated organizations.'),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'items_per_page',
		'options' => array(
			'after' => 'The number of items that will be shown per page on search results and long reports.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'public',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Public Site',
			'after' => 'If this is enabled, some information normally reserved for people who are logged on (statistics, team rosters, etc.) will be made available to anyone.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'registration',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle registration',
			'after' => 'Enable or disable processing of registrations.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'spirit',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle Spirit of the Game',
			'after' => 'Enable or disable Spirit of the Game options. If enabled here, Spirit can still be disabled on a per-league basis.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'allow_past_games',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable the option to schedule games in the past.',
		),
	));
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'tiny_mce',
			'options' => array(
				'type' => 'radio',
				'label' => 'Use TinyMCE WYSIWYG editor',
				'options' => Configure::read('options.enable'),
				'after' => 'To use this, you need to separately install the TinyMCE plugin.',
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'pdfize',
			'options' => array(
				'type' => 'radio',
				'label' => 'Use PDFize PDF converter plugin',
				'options' => Configure::read('options.enable'),
				'after' => 'To use this, you need to separately install the PDFize plugin.',
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'badges',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Enable badges',
			'after' => 'Enable or disable the awarding and display of badges.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'contacts',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle contacts',
			'after' => 'Enable or disable management of contacts for users to send messages without exposing email addresses.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'units',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.units'),
		),
	));
	?>
	</fieldset>

	<fieldset>
		<legend><?php __('Twitter Features'); ?></legend>
	<?php
	if (function_exists('curl_init')) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'twitter',
			'options' => array(
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => 'Enable or disable Twitter integration.',
			),
		));

		echo $this->element('settings/input', array(
			'category' => 'twitter',
			'name' => 'consumer_key',
			'options' => array(
				'after' => 'This application\'s Twitter consumer key.',
			),
		));

		echo $this->element('settings/input', array(
			'category' => 'twitter',
			'name' => 'consumer_secret',
			'options' => array(
				'after' => 'This application\'s Twitter consumer secret.',
			),
		));
	} else {
		echo $this->Html->para('warning-message', 'Twitter integration requires the cUrl library, which your installation of PHP does not support. Talk to your system administrator or hosting company about enabling cUrl.');
	}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
