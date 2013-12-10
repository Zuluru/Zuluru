<?php
$id = low(str_replace(' ', '_', $title));
$new_options = array('selector_' . $id);
if (!is_array($options)) {
	$options  = array($options);
}
foreach ($options as $option) {
	$new_options[] = $id . '_' . low(str_replace(' ', '_', $option));
}
echo implode(' ', $new_options);
?>
