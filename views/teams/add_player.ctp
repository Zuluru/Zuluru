<?php if (!$this->params['isAjax']): ?>

<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Add Player', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams add_player">
<h2><?php echo sprintf(__('Add %s', true), __('Player', true)) . ': ' . $team['Team']['name'];?></h2>

<?php echo $this->element('people/search_form', array('affiliate_id' => $team['Division']['League']['affiliate_id'])); ?>

<?php endif; ?>

<?php
echo $this->element('people/search_results', array('extra_url' => array('Add to team' => array('controller' => 'teams', 'action' => 'roster_add', 'team' => $team['Team']['id']))));
?>

<?php if (!$this->params['isAjax']): ?>

<p><?php
if (!empty ($teams)) {
	__('Or select a team from your history below to invite people from that roster.');
	$options = array();
	foreach ($teams as $history) {
		$options[$history['id']] = "{$history['name']} ({$history['Division']['full_league_name']})";
	}
	echo $this->Form->create(false, array('url' => array('action' => 'add_from_team', 'team' => $team['Team']['id'])));
	echo $this->Form->input ('team', array(
			'label' => false,
			'options' => $options,
			'empty' => '-- select from list --',
	));
	echo $this->Form->end(__('Show roster', true));
}
?></p>

<p><?php
if (!empty ($events)) {
	__('Or select a recent event to add people that are registered.');
	$options = array();
	foreach ($events as $event) {
		$options[$event['Event']['id']] = $event['Event']['name'];
	}
	echo $this->Form->create(false, array('url' => array('action' => 'add_from_event', 'team' => $team['Team']['id'])));
	echo $this->Form->input ('event', array(
			'label' => false,
			'options' => $options,
			'empty' => '-- select from list --',
	));
	echo $this->Form->end(__('Show registrations', true));
}
?></p>
</div>

<div class="actions">
	<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list')); ?>
</div>
<?php endif; ?>
