<?php
// Output a block with hidden fields for all of the items in the provided array.
if (isset($model)) {
	$model .= '.';
} else {
	$model = '';
}

foreach ($fields as $field => $values) {
	if (is_array ($values)) {
		echo $this->element('hidden', array('model' => $model . $field, 'fields' => $values));
	} else {
		echo $this->Form->hidden($model . $field, array('value' => $values));
	}
}
?>
