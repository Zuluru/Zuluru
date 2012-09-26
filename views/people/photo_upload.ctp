<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Upload Photo', true));
?>

<div class="people view">
<h2><?php  echo __('Upload Photo', true) . ': ' . $person['full_name'];?></h2>

<?php echo $this->element('people/photo_legal'); ?>

<?php
echo $this->Form->create(false, array('action' => 'photo_upload', 'enctype' => 'multipart/form-data'));
echo $this->Form->input('image', array('type' => 'file', 'label' => __('Profile Photo', true))); 
echo $this->Form->end(__('Upload', true));
?>

</div>
