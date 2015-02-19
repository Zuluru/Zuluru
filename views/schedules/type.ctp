<?php
$this->Html->addCrumb (__('Division', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Type', true));
?>

<div class="schedules add">
<?php echo $this->element('schedules/exclude'); ?>

<?php
$is_tournament = isset($pool) || isset($playoff) || $division['Division']['schedule_type'] == 'tournament';
$create = ($is_tournament ? 'tournament' : 'game(s)');

echo $this->Html->para(null, sprintf(__('Please enter some information about the %s to create.', true), $create));

echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));

if (isset($pool)) {
	echo $this->Html->para('warning-message', sprintf(__('You have defined pool %s with %d teams but not yet scheduled games for it. Options below reflect your choices for scheduling this pool.', true), $pool['name'], count($pool['PoolsTeam'])));
	echo $this->Html->para('warning-message', sprintf(__('If your pool definitions are incorrect, you can %s and then re-create them.', true),
		$this->Html->link(__('delete all pools in this stage', true),
						array('controller' => 'divisions', 'action' => 'delete_stage', 'division' => $division['Division']['id'], 'stage' => $pool['stage']),
						array('confirm' => sprintf(__('Are you sure you want to delete %s # %d?', true), __('stage', true), $pool['stage'])))
	));

	$this->data['Game']['pool_id'] = $pool['id'];
}
$this->data['Game']['step'] = 'type';
?>

<fieldset>
<legend><?php __('Create a ...'); ?></legend>
<?php
echo $this->Form->input('type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));

if ($is_tournament) {
	$help = $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'tournament', 'schedule_type'));
} else {
	$help = $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'schedule_type', $division['Division']['schedule_type']));
}
echo $this->Html->para(null, sprintf(__('Select the type of game or games to add. Note that for auto-generated schedules, %s will be automatically allocated.', true), __(Configure::read('sport.fields'), true)) . $help);

if (!$is_tournament) {
	echo $this->Html->para(null, sprintf(__('Alternately, you can %s.', true),
				$this->Html->link(__('create a playoff schedule', true), array('division' => $division['Division']['id'], 'playoff' => true))) .
			$this->ZuluruHtml->help(array('action' => 'schedules', 'playoffs'))
	);
}

echo $this->ZuluruForm->input('publish', array(
		'label' => __('Publish created games for player viewing?', true),
		'type' => 'checkbox',
));
?>

<p><?php __('If this is checked, players will be able to view games immediately after creation. Uncheck it if you wish to make changes before players can view.'); ?></p>

<?php
if ($is_tournament):
	$this->data['Game']['double_header'] = 0;
else:
	echo $this->ZuluruForm->input ('double_header', array(
			'label' => __('Allow double-headers?', true),
			'type' => 'checkbox',
			'checked' => false,
	));
?>

<p><?php __('If this is checked, you will be allowed to schedule more than the expected number of games. Check it only if you need this, as it disables some safety checks.'); ?></p>
<?php endif; ?>

<?php
if ($division['Division']['double_booking']):
	echo $this->ZuluruForm->input ('double_booking', array(
			'label' => __('Allow double-booking?', true),
			'type' => 'checkbox',
			'checked' => true,
	));
?>

<p><?php __('If this is checked, you will be allowed to schedule more than one game in a game slot.'); ?></p>
<?php
else:
	$this->data['Game']['double_booking'] = 0;
endif;
?>

</fieldset>

<?php
unset($this->data['Game']['type']);
echo $this->element('hidden', array('fields' => $this->data));
echo $this->Form->end(__('Next step', true));
?>

</div>