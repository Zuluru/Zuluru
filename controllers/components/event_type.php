<?php
/**
 * Base class for event-specific functionality.  This class defines default
 * no-op functions for all operations that events might need to do, as well
 * as providing some common utility functions that derived classes need.
 */

class EventTypeComponent extends Object
{
	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	/**
	 * Return the list of field names used for configuration.
	 * 
	 */
	function configurationFields() {
		return array();
	}

	/**
	 * Return the name of the element used to render configuration fields.
	 *
	 */
	function configurationFieldsElement() {
		return 'none';
	}

	/**
	 * Return entries for validation of any event-type-specific edit fields.
	 *
	 * @return mixed An array containing items to be added to the validation array.
	 *
	 */
	function configurationFieldsValidation() {
		return array();
	}

	/**
	 * Return an array of registration fields in questionnaire format.
	 *
	 */
	function registrationFields($event) {
		return array();
	}

	/**
	 * Return entries for validation of any event-type-specific registration fields.
	 *
	 * @return mixed An array containing items to be added to the validation array.
	 *
	 */
	function registrationFieldsValidation() {
		return array();
	}

	function register($event, &$data) {
		return true;
	}

	function unregister($event, $data) {
		return true;
	}

	function paid($event, &$data) {
		return true;
	}

	function unpaid($event, $data) {
		return true;
	}

	function _extractAnswer($data, $question) {
		$answer = Set::extract ("/Response[question_id=$question]/.", $data);
		if (!empty ($answer)) {
			// TODO: Handle those with answer_id instead of answer, when we do region preference and roster status
			return $answer[0]['answer'];
		} else {
			return null;
		}
	}

	function _extractAnswers($data, $questions) {
		$answers = array();
		foreach ($questions as $field => $question) {
			$answer = $this->_extractAnswer ($data, $question);
			if (!empty ($answer)) {
				$answers[$field] = $answer;
			}
		}
		return $answers;
	}

	function _extractAnswerId($data, $question) {
		$id = Set::extract ("/Response[question_id=$question]/id", $data);
		if (!empty ($id)) {
			return $id[0];
		} else {
			return null;
		}
	}

	function _extractAnswerIds($data, $questions) {
		$ids = array();
		foreach ($questions as $field => $question) {
			$id = $this->_extractAnswerId ($data, $question);
			if (!empty ($id)) {
				$ids[$field] = $id;
			}
		}
		return $ids;
	}
}

?>
