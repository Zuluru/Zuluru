<?php
// Output a block with hidden fields for all of the items in the provided array.
?>
<?php
foreach ($fields as $model => $values) {
	if (is_array ($values)) {
		foreach ($values as $field => $value) {
			echo $this->Form->hidden("$model.$field", array('value' => $value));
		}
	} else {
		echo $this->Form->hidden($model, array('value' => $values));
	}
}

?>
