<?php
$id = low(Inflector::slug($title));
$new_options = array('selector_' . $id);
if (!is_array($options)) {
	$options  = array($options);
}
foreach ($options as $option) {
	$new_options[] = $id . '_' . low(Inflector::slug($option));
}
echo implode(' ', $new_options);
?>
