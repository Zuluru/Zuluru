<?php

$rows = array();
foreach ($event['Questionnaire']['Question'] as $question) {
	if (in_array ($question['type'], array('select', 'radio', 'checkbox'))) {
		$title = $question['question'];
		if ($question['type'] == 'checkbox') {
			$counts = Set::extract ("/Response[question_id={$question['id']}]/..", $responses);
			$rows[] = array($title, __('yes', true), $counts[0][0]['count']);
		} else {
			foreach ($question['Answer'] as $answer) {
				$counts = Set::extract ("/Response[question_id={$question['id']}][answer_id={$answer['id']}]/..", $responses);
				// Set::extract bug: sometimes it's the nested array returned, sometimes not
				while (array_key_exists (0, $counts)) {
					$counts = $counts[0];
				}
				$rows[] = array($title, $answer['answer'], array_key_exists ('count_id', $counts) ? $counts['count_id'] : 0);
				$title = '';
			}
		}
	} else if ($question['type'] == 'label') {
		$rows[] = array(array($question['question'], array('colspan' => 3)));
	}
}

if (!empty ($rows))
	echo $this->Html->tag ('table', $this->Html->tableCells ($rows, array(), array('class' => 'altrow')));
?>
