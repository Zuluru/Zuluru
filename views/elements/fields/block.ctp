<?php
// Sometimes, there will be a 'Field' key, sometimes not
if (array_key_exists ('Field', $field)) {
	$field = array_merge ($field, $field['Field']);
	unset ($field['Field']);
}
$id = "fields_field_{$field['id']}";

if (isset ($options)) {
	$options = array_merge (array('id' => $id, 'class' => 'trigger'), $options);
} else {
	$options = array('id' => $id, 'class' => 'trigger');
}
if (!isset($display_field)) {
	$display_field = 'long_code';
}
echo $this->ZuluruHtml->link($field[$display_field],
	array('controller' => 'fields', 'action' => 'view', 'field' => $field['id']),
	$options);

echo $this->element('tooltips');
?>
