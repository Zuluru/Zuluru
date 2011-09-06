<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Feature', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('feature')));?>
	<fieldset>
 		<legend><?php __('Primary Options'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'site',
		'name' => 'name',
		'options' => array(
			'label' => 'Site Name',
			'after' => 'The name this application will be known as to your users.',
		),
	));
	echo $this->element ('setting/input', array(
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
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'registration',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle registration',
			'after' => 'Enable or disable processing of registrations.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'franchises',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Handle franchises',
			'after' => 'Enable or disable linking of teams through franchises.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'dog_questions',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'Enable or disable questions and options about dogs.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'region_preference',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, teams will be allowed to set a regional preference for scheduling.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'force_roster_request',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, players will be forced to respond to roster requests the next time they sign on.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'feature',
		'name' => 'generate_roster_email',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, emails will be sent to players invited to join rosters, and captains who have players request to join their teams.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
