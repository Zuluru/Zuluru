<?php
$this->Html->addCrumb (__('Leagues', true));
$this->Html->addCrumb (__('Emails', true));
$this->Html->addCrumb ($league['League']['long_name']);
?>

<div class="leagues emails">
<h2><?php echo __('Captain Emails', true) . ': ' . $league['League']['long_name'];?></h2>

<?php
$people = Set::extract('/Team/Person/.', $league);
$people = Set::sort($people, '{n}.last_name', 'asc');
echo $this->element('emails', array('people' => $people));
?>

</div>
