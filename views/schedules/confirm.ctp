<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Confirm Selections', true));
?>

<div class="schedules add">
<p>The following information will be used to create your games:</p>
<h3>What:</h3>
<p><?php echo $desc; ?></p>
<h3>Start date:</h3>
<p><?php echo $this->ZuluruTime->fulldate($start_date); ?></p>

<?php echo $this->element('schedule/exclude'); ?>

<?php
echo $this->Form->create ('Game', array('url' => array('controller' => 'schedules', 'action' => 'add', 'league' => $id)));
$this->data['Game']['step'] = 'finalize';
echo $this->element('hidden', array('fields' => $this->data));
?>

<?php echo $this->Form->end(__('Create games', true)); ?>

</div>