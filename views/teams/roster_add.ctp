<?php
$adding_noun = __($adding ? 'Addition' : 'Invitation', true);

$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (sprintf (__('Roster %s', true), $adding_noun));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people form">
<h2><?php echo sprintf (__('Roster %s', true), $adding_noun) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<?php
if ($adding) {
	echo $this->Html->para(null, __('You are adding', true) . ' ' .
		$this->element('people/block', compact('person')) .
		' ' . __('to the team', true) . ' ' .
		$this->element('teams/block', array('team' => $team, 'show_shirt' => false)) .
		'.');
} else {
	echo $this->Html->para(null, __('You are inviting', true) . ' ' .
		$this->element('people/block', compact('person')) .
		' ' . __('to join the team', true) . ' ' .
		$this->element('teams/block', array('team' => $team, 'show_shirt' => false)) .
		'. ' .
		__('The player will have to accept your invitation before they are considered an active member of the team.', true));
}
if ($can_add !== true) {
	echo $this->Html->para('warning-message', $can_add . ' ' .
		__('This player can still be invited to join, but will not be allowed to accept the invitation or play with your team until this is resolved.', true));
}

echo $this->Html->para(null, __('Possible roster roles are:', true));
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_add', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('role', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_role_options,
));
echo $this->Form->end(__('Submit', true));
?>

</div>