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
	function registrationFields($event, $user_id, $for_output = false) {
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
		if (Configure::read('feature.badges')) {
			$badge_obj = $this->_controller->_getComponent('Badge', '', $this->_controller);
			if (!$badge_obj->update('registration', $data, true)) {
				return false;
			}
		}

		return true;
	}

	function unpaid($event, $data) {
		if (Configure::read('feature.badges')) {
			$badge_obj = $this->_controller->_getComponent('Badge', '', $this->_controller);
			if (!$badge_obj->update('registration', $data, false)) {
				return false;
			}
		}

		return true;
	}

	function longDescription($data) {
		return $data['Event']['name'];
	}

	static function _extractAnswer($data, $question) {
		$answer = Set::extract ("/Response[question_id=$question]/.", $data);
		if (!empty ($answer)) {
			if (array_key_exists('answer_id', $answer[0]) && $answer[0]['answer_id'] !== null) {
				return $answer[0]['answer_id'];
			} else {
				return $answer[0]['answer'];
			}
		} else {
			return null;
		}
	}

	static function _extractAnswers($data, $questions) {
		$answers = array();
		foreach ($questions as $field => $question) {
			$answer = self::_extractAnswer ($data, $question);
			if (!empty ($answer)) {
				$answers[$field] = $answer;
			}
		}
		return $answers;
	}

	static function _extractAnswerId($data, $question) {
		$id = Set::extract ("/Response[question_id=$question]/id", $data);
		if (!empty ($id)) {
			return $id[0];
		} else {
			return null;
		}
	}

	static function _extractAnswerIds($data, $questions) {
		$ids = array();
		foreach ($questions as $field => $question) {
			$id = self::_extractAnswerId ($data, $question);
			if (!empty ($id)) {
				$ids[$field] = $id;
			}
		}
		return $ids;
	}
}

?>
