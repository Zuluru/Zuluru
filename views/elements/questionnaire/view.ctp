<?php
if (array_key_exists ('Question', $questionnaire)) {
	$rows = array();
	foreach ($questionnaire['Question'] as $question) {
		switch ($question['type'])
		{
			case 'radio':
			case 'select':
			case 'checkbox':
			case 'text':
			case 'textbox':
				$answer = array_shift (Set::extract ("/Response[question_id={$question['id']}]/.", $response));
				if ($question['type'] == 'select') {
					$answer = array_shift (Set::extract ("/Answer[id={$answer['answer_id']}]/.", $question));
				}
				$rows[] = array($question['question'], $answer['answer']);
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

echo $this->Html->tag('table', $this->Html->tableCells ($rows));

?>
