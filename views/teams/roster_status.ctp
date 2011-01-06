<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Roster Status', true));
$this->Html->addCrumb ($person['Person']['full_name']);
?>

<?php
$roster_descriptions = Configure::read('options.roster_position');
?>

<div class="people form">
<h2><?php  echo __('Roster Status', true) . ': ' . $team['Team']['name'] . ': ' . $person['Person']['full_name'];?></h2>
<p>You are attempting to change player status for <?php
echo $this->Html->link ($person['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id']));
?> on team <?php
echo $this->Html->link ($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']));
?>.</p>
<p>Current status: <strong><?php echo $roster_descriptions[$status]; ?></strong></p>
<p>Choices are:
<?php
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_status', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('status', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_options,
));
echo $this->Form->end(__('Submit', true));
?>

</p>
</div>