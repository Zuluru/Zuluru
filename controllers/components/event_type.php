<?php
/**
 * Base class for event-specific functionality.  This class defines default
 * no-op functions for all operations that events might need to do, as well
 * as providing some common utility functions that derived classes need.
 */

class EventTypeComponent extends Object
{
	var $viewVarsSaved = null;
	var $viewVarsCount = 0;

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
		$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
		$this->_controller->UserCache->clear('RegistrationsUnpaid', $data['Registration']['person_id']);

		return true;
	}

	function unregister($event, $data, $recursive = true) {
		if ($this->_controller->Registration->delete($data['Registration']['id'])) {
			$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
			$this->_controller->UserCache->clear('RegistrationsUnpaid', $data['Registration']['person_id']);

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
						(empty ($price['register_rule']) ||
							($rule_obj->init ($price['register_rule']) && $rule_obj->evaluate ($registration['Event']['affiliate_id'], $person))
						)
				)
				{
					$can_register = true;
					break;
				}
			}
			if (!$can_register) {
				$event_obj = $this->_controller->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this->_controller);
				if (in_array($registration['Registration']['payment'], Configure::read('registration_paid'))) {
					$event_obj->unpaid($registration, $registration);
				}
				if (in_array($registration['Registration']['payment'], Configure::read('registration_reserved'))) {
					$event_obj->unreserve($registration, $registration);
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

	function reserve($event, &$data, $skip_registration_id = null) {
		if (Configure::read('feature.waiting_list')) {
			$person = $this->_controller->UserCache->read('Person', $data['Registration']['person_id']);
			$conditions = array(
				'Registration.event_id' => $event['Event']['id'],
				'Registration.payment' => Configure::read('registration_reserved'),
			);
			if ($event['Event']['cap_female'] != -2) {
				$conditions['Person.gender'] = $person['gender'];
			}
			$cap = Event::cap($event['Event']['cap_male'], $event['Event']['cap_female'], $person['gender']);
			if ($cap != -1) {
				$this->_controller->Registration->contain('Person');
				$reserved = $this->_controller->Registration->find ('count', array('conditions' => $conditions));
			}

			if ($cap > 0 && $reserved >= $cap ) {
				$this->_saveViewVars();

				$conditions['Registration.payment'] = 'Unpaid';
				if ($skip_registration_id) {
					$conditions['Registration.id !='] = $skip_registration_id;
				}
				$unpaid = $this->_controller->Registration->find('all', array(
						'conditions' => $conditions,
						'contain' => array(
							'Person' => $this->_controller->Auth->authenticate->name,
						),
				));
				foreach ($unpaid as $registration) {
					$this->_controller->set(compact('event', 'registration'));

					if (Configure::read('registration.delete_unpaid')) {
						// Remove any unpaid registrations for this event
						$this->unregister($event, $registration);

						$this->_controller->_sendMail (array (
								'to' => $registration,
								'subject' =>  Configure::read('organization.name') . ' Registration removed',
								'template' => 'registration_removed',
								'sendAs' => 'both',
						));
					} else {
						// Move any unpaid registrations for this event to the waiting list
						$this->_controller->Registration->id = $registration['Registration']['id'];
						$this->_controller->Registration->saveField('payment', 'Waiting');

						$this->_controller->_sendMail (array (
								'to' => $registration,
								'subject' =>  Configure::read('organization.name') . ' Registration moved to waiting list',
								'template' => 'registration_waiting',
								'sendAs' => 'both',
						));
					}
					$this->_controller->UserCache->clear('Registrations', $registration['Registration']['person_id']);
					$this->_controller->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);
				}

				$this->_restoreViewVars();
			}
		}

		$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
		$this->_controller->UserCache->clear('RegistrationsUnpaid', $data['Registration']['person_id']);

		return true;
	}

	function unreserve($event, &$data) {
		// Default payment status to change the unreserved registration to
		$new_payment = 'Unpaid';

		if (Configure::read('feature.waiting_list')) {
			$person = $this->_controller->UserCache->read('Person', $data['Registration']['person_id']);
			$conditions = array(
				'Registration.event_id' => $event['Event']['id'],
				'Registration.payment' => Configure::read('registration_reserved'),
			);
			if ($event['Event']['cap_female'] != -2) {
				$conditions['Person.gender'] = $person['gender'];
			}
			$cap = Event::cap($event['Event']['cap_male'], $event['Event']['cap_female'], $person['gender']);
			if ($cap != -1) {
				$this->_controller->Registration->contain('Person');
				$reserved = $this->_controller->Registration->find ('count', array('conditions' => $conditions));
			}

			if ($cap > 0) {
				if ($reserved < $cap) {
					$this->_saveViewVars();

					$conditions['Registration.payment'] = 'Waiting';
					$conditions['Registration.id !='] = $data['Registration']['id'];
					$waiting = $this->_controller->Registration->find('all', array(
							'conditions' => $conditions,
							'contain' => array(
								'Person' => $this->_controller->Auth->authenticate->name,
							),
							'order' => 'Registration.id',
							'limit' => $cap - $reserved,	// number of open spots
					));
					foreach ($waiting as $registration) {
						if (Configure::read('registration.reservation_time') > 0) {
							$expiry = date('Y-m-d H:i:s', time() + Configure::read('registration.reservation_time') * HOUR);
						} else {
							$expiry = null;
						}
						$this->_controller->Registration->id = $registration['Registration']['id'];
						$this->_controller->Registration->saveField('payment', 'Reserved');
						$this->_controller->Registration->saveField('reservation_expires', $expiry);
						$this->_controller->Registration->saveField('delete_on_expiry', true);
						$this->reserve($event, $registration, $data['Registration']['id']);

						$this->_controller->set(compact('event', 'registration', 'expiry'));

						$this->_controller->_sendMail (array (
								'to' => $registration,
								'subject' =>  Configure::read('organization.name') . ' Waiting list opening',
								'template' => 'registration_opening',
								'sendAs' => 'both',
						));

						++ $reserved;
					}

					$this->_restoreViewVars();
				}
				if ($reserved >= $cap) {
					// The event is now full, move this to the waiting list
					$new_payment = 'Waiting';
				}
			}
		}

		$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
		$this->_controller->UserCache->clear('RegistrationsUnpaid', $data['Registration']['person_id']);

		return $new_payment;
	}

	function paid($event, &$data) {
		if (Configure::read('feature.badges')) {
			$badge_obj = $this->_controller->_getComponent('Badge', '', $this->_controller);
			if (!$badge_obj->update('registration', $data, true)) {
				$this->_controller->Session->setFlash(__('Failed to update badge information!', true), 'default', array('class' => 'warning'));
				return false;
			}
		}

		$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
		$this->_controller->UserCache->clear('RegistrationsPaid', $data['Registration']['person_id']);

		return true;
	}

	function unpaid($event, $data) {
		if (Configure::read('feature.badges')) {
			$badge_obj = $this->_controller->_getComponent('Badge', '', $this->_controller);
			if (!$badge_obj->update('registration', $data, false)) {
				$this->_controller->Session->setFlash(__('Failed to update badge information!', true), 'default', array('class' => 'warning'));
				return false;
			}
		}

		$this->_controller->UserCache->clear('Registrations', $data['Registration']['person_id']);
		$this->_controller->UserCache->clear('RegistrationsPaid', $data['Registration']['person_id']);

		return true;
	}

	function longDescription($data) {
		return Registration::longDescription($data);
	}

	function _saveViewVars() {
		if ($this->viewVarsCount == 0) {
			$this->viewVarsSaved = $this->_controller->viewVars;
		}
		++ $this->viewVarsCount;
	}

	function _restoreViewVars() {
		-- $this->viewVarsCount;
		if ($this->viewVarsCount == 0) {
			$this->_controller->viewVars = $this->viewVarsSaved;
			$this->viewVarsSaved = null;
		}
	}

	static function _extractAnswer($data, $question) {
		if (!empty($data['Registration']['Response'])) {
			$data = $data['Registration'];
		}
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
		if (!empty($data['Registration']['Response'])) {
			$data = $data['Registration'];
		}
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
