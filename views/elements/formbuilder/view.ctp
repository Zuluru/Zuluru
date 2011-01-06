<?php
foreach ($questions as $question => $details) {
	// We can view the answer if it's an unrestricted question, we're admin, or we're
	// coordinator.  $is_coordinator will only be set in places where we're looking at
	// something to do with a league, like game results.
	if (!array_key_exists ('restricted', $details) || !$details['restricted'] || $is_admin ||
		(isset($is_coordinator) && $is_coordinator))
	{
		echo $this->Html->tag ('h3', __($details['text'], true));
		if (array_key_exists ('options', $details)) {
			$options = $details['options'];
		}
		if (array_key_exists ($question, $answers)) {
			$answer = $answers[$question];
		}
		echo $this->element("/formbuilder/view/{$details['type']}", compact('options', 'answer'));
	}
}
?>
