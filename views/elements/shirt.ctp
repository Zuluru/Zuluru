<?php
$file = Inflector::slug(trim(low($colour), ' !()/,'));

// Remove anything after "with"
$with = strpos($file, '_with');
if ($with !== false) {
	$file = substr($file, 0, $with);
}

if (!file_exists (Configure::read('folders.icon_base') . DS . 'shirts' . DS . $file . '.png')) {
	$file = 'default';
}
echo $this->ZuluruHtml->icon("shirts/$file.png", array('title' => __('Shirt colour', true) . ': ' . $colour));
?>
