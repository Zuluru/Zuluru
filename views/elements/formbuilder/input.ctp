<?php
foreach ($questions as $question => $details) {
	$field = "$prefix.$question";
	if (array_key_exists ('options', $details)) {
		$options = $details['options'];
	}
	if (array_key_exists ('desc', $details)) {
		$desc = $details['desc'];
	}
	$label = __($details['text'], true);
	echo $this->Html->tag ('div',
		$this->element("/formbuilder/input/{$details['type']}", compact('field', 'label', 'options', 'desc')),
		array('class' => 'input required'));
}
?>
