<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Player Emails', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams emails">
<h2><?php echo __('Player Emails', true) . ': ' . $team['Team']['name'];?></h2>

<?php echo $this->element('emails', array('people' => $team['Person'])); ?>

</div>
