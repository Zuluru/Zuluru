<?php
/**
 * Component for assisting with questionnaire-related tasks.
 */

class QuestionnaireComponent extends Object
{
	function validation($questionnaire, $edit = false) {
		$validation = array();

		foreach ($questionnaire['Question'] as $question) {
			// Anonymous questions are not included when editing an existing registration
			if (!$edit || !array_key_exists('anonymous', $question) || !$question['anonymous']) {
				$required = (array_key_exists ('QuestionnairesQuestion', $question) && $question['QuestionnairesQuestion']['required']) ||
							(array_key_exists ('required', $question) && $question['required']);

				// 'message' must go into an array with key = 'answer' or 'answer_id' because
				// field names when we display this are like Response.{id}.answer
				switch ($question['type']) {
					// These types may require a single selection
					case 'select':
					case 'radio':
						if (array_key_exists('Answer', $question)) {
							$options = Set::extract ('/Answer/id', $question);
						} else if (array_key_exists('options', $question)) {
							$options = array_keys($question['options']);
						} else {
							$options = array();
						}
						$validation[Question::_formName($question)] = array(
							'rule' => array('response_select', $options, $required),
							'message' => array('answer_id' => 'Select one'),
							'required' => true,
						);
						break;

					case 'text':
					case 'textbox':
						if ($required) {
							$validation[Question::_formName($question)] = array(
								'rule' => array('response', 'notempty'),
								'message' => array('answer' => 'Must not be blank.'),
								'required' => true,
								'allowEmpty' => false,
							);
						}
						break;

					case 'checkbox':
						// TODO: Checkbox validation?
						break;

					case 'group_start':
					case 'group_end':
					case 'description':
					case 'label':
						break;

					default:
						$this->log("Unknown question type $type in QuestionnaireComponent::validation");
						break;
				}
			}
		}

		return $validation;
	}
}
?>
