<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Roster Position', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<div class="people form">
<h2><?php  echo __('Roster Position', true) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<?php
$roster_descriptions = Configure::read('options.roster_position');
echo $this->Html->para(null, __('You are attempting to change player position for', true) . ' ' .
	$this->Html->link($person['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id'])) .
	' ' . __('on the team', true) . ' ' .
	$this->Html->link($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id'])));
echo $this->Html->para(null, __('Current position:', true) . ' ' .
	$this->Html->tag('strong', __($roster_descriptions[$position], true)));

echo $this->Html->para(null, __('Possible roster positions are:', true));
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_position', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('position', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_options,
		'default' => $position,
));
echo $this->Form->end(__('Submit', true));
?>

</div>