<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Preferences', true));
?>

<div class="registrations form">
<h2><?php echo __('Registration Preferences', true) . ': ' . $event['Event']['name']; ?></h2>

<?php
echo $this->element ('registration/notice');

if ($waivered) {
	echo $this->Html->para ('error-message', sprintf (__('You have already accepted the %s for this membership year.', true),
		$this->Html->link (__($event['Event']['waiver_type'], true) . ' ' . __('waiver', true),
				array('controller' => 'people', 'action' => 'view_waiver', 'type' => $event['Event']['waiver_type'], 'year' => $waivered),
				array('target' => 'new')
	)));
}

echo $this->Form->create('Response', array('url' => $this->here));

echo $this->element ('questionnaire/input', array('questionnaire' => $event['Questionnaire']));
?>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

<?php echo $this->Form->end(); ?>
</div>
