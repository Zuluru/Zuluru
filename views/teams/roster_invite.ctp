<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Roster Invitation', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people form">
<h2><?php  echo __('Roster Invitation', true) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<?php
echo $this->Html->para(null, __('You are inviting', true) . ' ' .
	$this->Html->link($person['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id'])) .
	' ' . __('to join the team', true) . ' ' .
	$this->Html->link($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id'])) .
	'. ' .
	__('The player will have to accept your invitation before they are considered an active member of the team.', true));
if ($can_add !== true) {
	echo $this->Html->para('error-message', $can_add . ' ' .
		__('This player can still be invited to join, but will not be able to accept the invitation until this is resolved.', true));
}

echo $this->Html->para(null, __('Possible roster positions are:', true));
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_invite', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('position', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_options,
));
echo $this->Form->end(__('Submit', true));
?>

</div>