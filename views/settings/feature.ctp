<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Feature', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('feature')));?>
	<fieldset>
 		<legend><?php __('Primary Options'); ?></legend>
	<?php
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
		'name' => 'items_per_page',
		'options' => array(
			'after' => 'The number of items that will be shown per page on search results and long reports.',
		),
	));
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Boolean Options'); ?></legend>
	<?php
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
		'name' => 'franchises',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle franchises',
			'after' => 'Enable or disable linking of teams through franchises.',
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
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'region_preference',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, teams will be allowed to set a regional preference for scheduling.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'force_roster_request',
		'options' => array(
			'type' => 'radio',
			'label' => 'Force Roster Request Responses',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, players will be forced to respond to roster requests the next time they sign on. It is recommended to use either this or Generate Roster Emails, not both.',
		),
	));
	echo $this->element('settings/input', array(
		'category' => 'feature',
		'name' => 'generate_roster_email',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, emails will be sent to players invited to join rosters, and captains who have players request to join their teams. It is recommended to use either this or Force Roster Request Responses, not both.',
		),
	));
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
		'name' => 'documents',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle document uploads',
			'after' => 'Enable or disable uploading of documents by players (e.g. as an alternative to faxing or emailing).',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
