<?php
if (array_key_exists ('Question', $questionnaire)) {
	foreach ($questionnaire['Question'] as $question) {
		echo $this->element('question/input', compact('question'));
	}
}

?>
