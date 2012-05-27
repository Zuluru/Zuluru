<?php
if (array_key_exists ('Question', $questionnaire)) {
	foreach ($questionnaire['Question'] as $question) {
		// Anonymous questions are not included when editing an existing registration
		if (!isset($edit) || !array_key_exists('anonymous', $question) || !$question['anonymous']) {
			echo $this->element('questions/input', compact('question'));
		}
	}
}

?>
