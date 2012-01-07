<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb (__('Emails', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
?>

<div class="divisions emails">
<h2><?php echo __('Captain Emails', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
$people = Set::extract('/Team/Person/.', $division);
$people = Set::sort($people, '{n}.last_name', 'asc');
echo $this->element('emails', array('people' => $people));
?>

</div>
