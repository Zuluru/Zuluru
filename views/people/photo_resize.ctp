<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Resize Photo', true));
?>

<div class="people view">
<h2><?php  echo __('Resize Photo', true) . ': ' . $person['full_name'];?></h2>

<?php
$this->ZuluruHtml->css('imgareaselect-default', array('inline' => false));
$this->ZuluruHtml->script('jquery.imgareaselect.pack', array('inline' => false));

echo $this->Form->create(false, array('action' => 'photo', 'enctype' => 'multipart/form-data'));

echo $this->CropImage->createJavaScript($uploaded['imageWidth'], $uploaded['imageHeight'], $size, $size);
echo $this->CropImage->createForm($uploaded['imagePath'], $size, $size); 

echo $this->Form->submit('Done', array('id' => 'save_thumb'));
?>

</div>
