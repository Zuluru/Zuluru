<?php
// TODOCAKE: $file = Inflector::slug(low($colour)); doesn't work on TUC site?
$file = str_replace(' ', '_', trim(low($colour), ' !()/'));
if (!file_exists ('img/shirts' . DS . $file . '.png')) {
	$file = 'default';
}
echo $this->Html->image("shirts/$file.png", array('title' => __('Shirt colour', true) . ': ' . $colour));
?>