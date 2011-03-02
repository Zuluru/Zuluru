<?php
if (array_key_exists ('Question', $questionnaire)) {
	$rows = array();
	foreach ($questionnaire['Question'] as $question) {
		if (!$question['anonymous']) {
			switch ($question['type'])
			{
				case 'radio':
				case 'select':
				case 'text':
				case 'textbox':
					$answer = array_shift (Set::extract ("/Response[question_id={$question['id']}]/.", $response));
					if ($question['type'] == 'select') {
						$answer = array_shift (Set::extract ("/Answer[id={$answer['answer_id']}]/.", $question));
					}
					$rows[] = array($question['question'], $answer['answer']);
					break;

				case 'checkbox':
					$label = $question['question'];
					$answers = Set::extract ("/Response[question_id={$question['id']}]/answer_id", $response);
					foreach ($answers as $answer) {
						$answer = array_shift (Set::extract ("/Answer[id={$answer}]/.", $question));
						$rows[] = array($label, $answer['answer']);
						$label = '';
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

echo $this->Html->tag('table', $this->Html->tableCells ($rows));

?>
