<?php
$this->Html->addCrumb (__('Players', true));
$this->Html->addCrumb ($person['full_name']);
$this->Html->addCrumb (__('Resize Photo', true));
?>

<div class="people view">
<h2><?php  echo __('Resize Photo', true) . ': ' . $person['full_name'];?></h2>

<?php
echo $this->ZuluruHtml->css('imgareaselect-default', null, array('inline' => false));
echo $this->ZuluruHtml->script('jquery.imgareaselect.pack', array('inline' => false));

echo $this->Form->create(false, array('action' => 'photo_resize', 'enctype' => 'multipart/form-data'));

echo $this->CropImage->createJavaScript($uploaded['imageWidth'], $uploaded['imageHeight'], $size, $size);
$temp_dir = Configure::read('urls.league_base') . '/temp/';
echo $this->CropImage->createForm($temp_dir, $uploaded['imageName'], $size, $size);

echo $this->Form->submit('Done', array('id' => 'save_thumb'));
?>

</div>
