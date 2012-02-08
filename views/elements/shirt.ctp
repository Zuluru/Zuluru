<?php
$file = Inflector::slug(trim(str_replace(array('!', '(', ')', ','), ' ', low($colour))));

foreach (array('/', '_or_') as $split) {
	if (strpos($file, $split) !== false) {
		$parts = explode($split, $file);
		$out = array();
		foreach ($parts as $part) {
			$out[] = $this->element('shirt', array('colour' => $part));
		}
		echo implode('/', $out);
		return;
	}
}

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
