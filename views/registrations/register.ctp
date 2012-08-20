<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('Preferences', true));
?>

<div class="registrations form">
<h2><?php echo __('Registration Preferences', true) . ': ' . $event['Event']['name']; ?></h2>

<?php
echo $this->element ('registrations/notice');

echo $this->Form->create('Response', array('url' => Router::normalize($this->here)));

echo $this->element ('questionnaires/input', array('questionnaire' => $event['Questionnaire']));
?>

<div class="submit">
<?php echo $this->Form->submit('Submit', array('div' => false)); ?>

<?php echo $this->Form->submit('Reset', array('div' => false, 'type' => 'reset')); ?>

</div>
<?php echo $this->Form->end(); ?>

</div>
