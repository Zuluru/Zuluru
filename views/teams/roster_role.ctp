<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Roster Role', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people form">
<h2><?php  echo __('Roster Role', true) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<?php
$roster_descriptions = Configure::read('options.roster_role');
echo $this->Html->para(null, __('You are attempting to change the role for', true) . ' ' .
	$this->element('people/block', compact('person')) .
	' ' . __('on the team', true) . ' ' .
	$this->element('teams/block', array('team' => $team, 'show_shirt' => false)));
echo $this->Html->para(null, __('Current role:', true) . ' ' .
	$this->Html->tag('strong', __($roster_descriptions[$role], true)));

echo $this->Html->para(null, __('Possible roster roles are:', true));
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_role', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('role', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_role_options,
		'default' => $role,
));
echo $this->Form->end(__('Submit', true));
?>

</div>