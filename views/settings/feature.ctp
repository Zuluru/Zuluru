<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Feature', true));
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
		<legend><?php __('Primary Options'); ?></legend>
	<?php
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'site',
			'name' => 'name',
			'options' => array(
				'label' => __('Site name', true),
				'after' => __('The name this application will be known as to your users.', true),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'affiliates',
			'options' => array(
				'label' => __('Enable affiliates', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('Allow configuration of multiple affiliated organizations.', true),
			),
		));
	}

	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'items_per_page',
		'options' => array(
			'label' => __('Items per page', true),
			'after' => __('The number of items that will be shown per page on search results and long reports.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'public',
		'options' => array(
			'label' => __('Public site', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('If this is enabled, some information normally reserved for people who are logged on (statistics, team rosters, etc.) will be made available to anyone.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'registration',
		'options' => array(
			'label' => __('Handle registration', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable processing of registrations.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'spirit',
		'options' => array(
			'label' => __('Handle Spirit of the Game', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable Spirit of the Game options. If enabled here, Spirit can still be disabled on a per-league basis.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'allow_past_games',
		'options' => array(
			'label' => __('Allow past games', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable the option to schedule games in the past.', true),
		),
	));
	if (!$affiliate) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'tiny_mce',
			'options' => array(
				'label' => __('Use TinyMCE WYSIWYG editor', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('To use this, you need to separately install the TinyMCE plugin.', true),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'pdfize',
			'options' => array(
				'label' => __('Use PDFize PDF converter plugin', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('To use this, you need to separately install the PDFize plugin.', true),
			),
		));
	}
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'badges',
		'options' => array(
			'label' => __('Enable badges', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable the awarding and display of badges.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'contacts',
		'options' => array(
			'label' => __('Handle contacts', true),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => __('Enable or disable management of contacts for users to send messages without exposing email addresses.', true),
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'units',
		'options' => array(
			'label' => __('Units', true),
			'type' => 'radio',
			'options' => Configure::read('options.units'),
		),
	));
	?>
	</fieldset>

	<?php
	$languages = Configure::read('available_translations');
	if (!$affiliate && count($languages) > 1):
	?>
	<fieldset>
		<legend><?php __('Language Features'); ?></legend>
	<?php
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'language',
			'options' => array(
				'label' => __('Allow registered users to select their preferred language', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'uls',
			'options' => array(
				'label' => __('Use ULS to allow language selection for anonymous users and those who haven\'t selected a preferred language', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('To use this, you need to separately install the ULS plugin.', true),
			),
		));
		echo $this->element('settings/input', array(
			'category' => 'site',
			'name' => 'default_language',
			'options' => array(
				'label' => __('Default site language', true),
				'type' => 'select',
				'options' => $languages,
			),
		));
	?>
	</fieldset>
	<?php
	endif;
	?>

	<fieldset>
		<legend><?php __('Twitter Features'); ?></legend>
	<?php
	if (function_exists('curl_init')) {
		echo $this->element('settings/input', array(
			'category' => 'feature',
			'name' => 'twitter',
			'options' => array(
				'label' => __('Twitter', true),
				'type' => 'radio',
				'options' => Configure::read('options.enable'),
				'after' => __('Enable or disable Twitter integration.', true),
			),
		));

		echo $this->element('settings/input', array(
			'category' => 'twitter',
			'name' => 'consumer_key',
			'options' => array(
				'label' => __('Consumer key', true),
				'after' => __('This application\'s Twitter consumer key.', true),
			),
		));

		echo $this->element('settings/input', array(
			'category' => 'twitter',
			'name' => 'consumer_secret',
			'options' => array(
				'label' => __('Consumer secret', true),
				'after' => __('This application\'s Twitter consumer secret.', true),
			),
		));
	} else {
		echo $this->Html->para('warning-message', __('Twitter integration requires the cUrl library, which your installation of PHP does not support. Talk to your system administrator or hosting company about enabling cUrl.', true));
	}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
