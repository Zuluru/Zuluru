<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Roster Request', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people form">
<h2><?php  echo __('Roster Request', true) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<?php
echo $this->Html->para(null, __('You are requesting to join the team', true) . ' ' .
	$this->element('teams/block', array('team' => $team, 'show_shirt' => false)) .
	'. ' .
	__('A captain will have to approve your request before you are considered an active member of the team.', true));

echo $this->Html->para(null, __('Possible roster roles are:', true));
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('role', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_role_options,
));
echo $this->Form->end(__('Submit', true));
?>

</div>