<?php
if (array_key_exists ('Question', $questionnaire)) {
	$rows = array();
	foreach ($questionnaire['Question'] as $question) {
		if (!array_key_exists('anonymous', $question) || !$question['anonymous']) {
			switch ($question['type'])
			{
				case 'radio':
				case 'select':
				case 'text':
				case 'textbox':
					$answer = array_shift (Set::extract ("/Response[question_id={$question['id']}]/.", $response));
					if ($question['type'] == 'select' || $question['type'] == 'radio') {
						if (array_key_exists('Answer', $question)) {
							$answer = array_shift (Set::extract ("/Answer[id={$answer['answer_id']}]/.", $question));
							$answer = $answer['answer'];
						} else if (array_key_exists('options', $question) && array_key_exists($answer['answer_id'], $question['options'])) {
							$answer = $question['options'][$answer['answer_id']];
						} else {
							// This shouldn't happen, unless questionnaires change after a registration happened
							$answer = null;
						}
					} else {
						$answer = $answer['answer'];
					}
					$name = $question['question'];
					if (array_key_exists('name', $question) && !empty($question['name'])) {
						$name = $question['name'];
					}
					$rows[] = array($name, $answer);
					break;

				case 'checkbox':
					$label = $question['question'];
					if (array_key_exists('name', $question) && !empty($question['name'])) {
						$label = $question['name'];
					}
					$answers = Set::extract ("/Response[question_id={$question['id']}]/answer_id", $response);
					// Deal with both checkbox groups and single checkboxes
					if (!empty($question['Answer'])) {
						foreach ($answers as $answer) {
							$answer = array_shift (Set::extract ("/Answer[id={$answer}]/.", $question));
							$rows[] = array($label, $answer['answer']);
							$label = '';
						}
					} else {
						$rows[] = array($label, __(empty($answers) ? 'N/A' : ($answers[0] ? 'Yes' : 'No'), true));
					}
					break;

				// TODO: Handle these
				case 'group_start':
				case 'group_end':
					break;

				case 'description':
				case 'label':
					$rows[] = array(array($question['question'], array('colspan' => 2)));
					break;
			}
		}
	}
}

echo $this->Html->tag('table', $this->Html->tableCells ($rows), array('class' => 'list'));

?>
