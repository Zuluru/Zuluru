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
?>
<p>Please enter some information about the <?php echo $create; ?> to create.</p>

<?php
echo $this->Form->create ('Game', array('url' => Router::normalize($this->here)));

if (isset($pool)):
?>
<p class="warning-message">You have defined pool <?php echo $pool['name']; ?> with <?php echo count($pool['PoolsTeam']); ?> teams but not yet scheduled games for it. Options below reflect your choices for scheduling this pool.</p>
<p class="warning-message">If your pool definitions are incorrect, you can <?php
echo $this->Html->link('delete all pools in this stage', array('controller' => 'divisions', 'action' => 'delete_stage', 'division' => $division['Division']['id'], 'stage' => $pool['stage']), array('confirm' => sprintf(__('Are you sure you want to delete %s # %d?', true), __('stage', true), $pool['stage'])));
?> and then re-create them.</p>
<?php
	$this->data['Game']['pool_id'] = $pool['id'];
endif;
$this->data['Game']['step'] = 'type';
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

<p>Select the type of game or games to add. Note that for auto-generated schedules, <?php __(Configure::read('sport.fields')); ?> will be automatically allocated.
<?php
if ($is_tournament) {
	echo $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'tournament', 'schedule_type'));
} else {
	echo $this->ZuluruHtml->help(array('action' => 'schedules', 'add', 'schedule_type', $division['Division']['schedule_type']));
}
?>
</p>
<?php if (!$is_tournament): ?>
<p>Alternately, you can <?php echo $this->Html->link(__('create a playoff schedule', true), array('division' => $division['Division']['id'], 'playoff' => true)); ?>.
<?php echo $this->ZuluruHtml->help(array('action' => 'schedules', 'playoffs')); ?>
</p>
<?php endif; ?>

<?php
echo $this->ZuluruForm->input('publish', array(
		'label' => __('Publish created games for player viewing?', true),
		'type' => 'checkbox',
));
?>

<p>If this is checked, players will be able to view games immediately after creation. Uncheck it if you wish to make changes before players can view.</p>

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

<p>If this is checked, you will be allowed to schedule more than the expected number of games. Check it only if you need this, as it disables some safety checks.</p>
<?php endif; ?>

</fieldset>

<?php
unset($this->data['Game']['type']);
echo $this->element('hidden', array('fields' => $this->data));
echo $this->Form->end(__('Next step', true));
?>

</div>