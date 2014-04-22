<?php
if (!empty($preview)) {
	echo $this->Html->tag('label', $label);
} else {
	echo $this->Form->input($field, array('type' => 'textbox', 'label' => $label, 'cols' => 60));
}
?>
