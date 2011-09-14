<?php

$rows = array();
foreach ($event['Questionnaire']['Question'] as $question) {
	if (in_array ($question['type'], array('select', 'radio', 'checkbox'))) {
		// There's no way to summarize answers to auto questions
		// TODO: Revisit once regional preference, etc. are handled
		if ($question['id'] > 0) {
			$title = $question['name'];
			foreach ($question['Answer'] as $answer) {
				$counts = Set::extract ("/Response[question_id={$question['id']}][answer_id={$answer['id']}]/..", $responses);
				// Set::extract bug: sometimes it's the nested array returned, sometimes not
				while (array_key_exists (0, $counts)) {
					$counts = $counts[0];
				}
				$rows[] = array($title, $answer['answer'], array_key_exists ('count', $counts) ? $counts['count'] : 0);
				$title = '';
			}
		}
	} else if ($question['type'] == 'label') {
		$rows[] = array(array($question['question'], array('colspan' => 3)));
	}
}

if (!empty ($rows))
	echo $this->Html->tag ('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')), array('class' => 'list'));
?>
