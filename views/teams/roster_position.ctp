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
?>

<?php if ($can_add !== true): ?>
<p><?php echo $can_add; ?></p>
<?php else: ?>
<p>You are attempting to change player position for <?php
echo $this->Html->link ($person['Person']['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['Person']['id']));
?> on team <?php
echo $this->Html->link ($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']));
?>.</p>
<p>Current position: <strong><?php echo $roster_descriptions[$position]; ?></strong></p>
<p>Choices are:
<?php
echo $this->Form->create('Person', array('url' => array('controller' => 'teams', 'action' => 'roster_position', 'team' => $team['Team']['id'], 'person' => $person['Person']['id'])));
echo $this->Form->input('position', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $roster_options,
		'default' => $position,
));
echo $this->Form->end(__('Submit', true));
?>
<?php endif; ?>

</p>
</div>