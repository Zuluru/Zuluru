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

	function unregister($event, $data, $recursive = true) {
		if ($this->_controller->Registration->delete($data['Registration']['id'])) {
			$this->_controller->UserCache->clear('RegistrationsUnpaid', $data['Registration']['person_id']);
			$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);

			// Check if anything else must be removed as a result (e.g. team reg after removing membership)
			while ($recursive && $this->_unregisterDependencies($data['Registration']['person_id'])) {
			}

			return true;
		} else {
			$this->_controller->Session->setFlash(__('Failed to unregister from this event!', true), 'default', array('class' => 'warning'));
			return false;
		}
	}

	function _unregisterDependencies($person_id) {
		// Get everything from the user record that the decisions below might need
		$person = array(
			'Person' => $this->_controller->UserCache->read('Person', $person_id),
			'Team' => $this->_controller->UserCache->read('Teams', $person_id),
			'Preregistration' => $this->_controller->UserCache->read('Preregistrations', $person_id),
			'Registration' => array_merge(
				$this->_controller->UserCache->read('RegistrationsPaid', $person_id),
				$this->_controller->UserCache->read('RegistrationsUnpaid', $person_id)
			),
			'Upload' => $this->_controller->UserCache->read('Documents', $person_id),
			'Affiliate' => $this->_controller->UserCache->read('Affiliates', $person_id),
			'Waiver' => $this->_controller->UserCache->read('Waivers', $person_id),
		);

		$unregistered = false;

		// Pull out the list of unpaid registrations; these are the ones that might be removed
		$unpaid = array();
		foreach (Configure::read('registration_none_paid') as $payment) {
			$unpaid = array_merge($unpaid, Set::extract ("/Registration[payment=$payment]/..", $person['Registration']));
		}

		$rule_obj = AppController::_getComponent ('Rule');
		foreach ($unpaid as $key => $registration) {
			$person['Registration'] = Set::extract ("/Registration[id!={$registration['Registration']['id']}]/..", $person['Registration']);

			// Check the registration rule, if any
			$can_register = false;
			foreach ($registration['Event']['Price'] as $price) {
				if (strtotime($price['close']) > time() &&
						!empty ($price['register_rule']) &&
						$rule_obj->init ($price['register_rule']) &&
						$rule_obj->evaluate ($registration['Event']['affiliate_id'], $person))
				{
					$can_register = true;
					break;
				}
			}
			if (!$can_register) {
				$event_obj = $this->_controller->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this->_controller);
				if (in_array($registration['Registration']['payment'], Configure::read('registration_reserved'))) {
					$event_obj->unpaid($registration, $registration);
				}
				$event_obj->unregister($registration, $registration, false);
				$unregistered = true;
			}

			// Refresh the list
			$person['Registration'] = array_merge(
				$this->_controller->UserCache->read('RegistrationsPaid', $person_id),
				$this->_controller->UserCache->read('RegistrationsUnpaid', $person_id)
			);
		}

		return $unregistered;
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
		return Registration::longDescription($data);
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
