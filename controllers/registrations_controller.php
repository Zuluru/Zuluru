<?php
class RegistrationsController extends AppController {

	var $name = 'Registrations';
	var $components = array('Questionnaire', 'CanRegister');
	var $paginate = array(
		'Registration' => array(
			'contain' => array('Person'),
			'order' => array('Registration.payment' => 'DESC', 'Registration.created' => 'DESC'),
		),
	);

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'register',
				'unregister',
				'checkout',
		)))
		{
			return true;
		}

		return false;
	}

	function full_list() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect('/');
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Questionnaire' => array('Question' => array('Answer')),
		));
		$event = $this->Registration->Event->read(null, $id);
		if ($event === false) {
			$this->Session->setFlash(__('Invalid Event', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire']);

		if ($this->params['url']['ext'] == 'csv') {
			// Technically, there's no reason not to include responses for events with
			// anonymous surveys, because there are none to read. However, it takes a
			// lot longer to even try, and scripts timeout because those also tend to
			// be the events with a lot of registrations (memberships).
			if ($event['Event']['anonymous']) {
				$this->Registration->contain ('Person');
			} else {
				$this->Registration->contain ('Person', 'Response');
			}
			$this->set('registrations', $this->Registration->find ('all', array(
					'conditions' => array('Registration.event_id' => $id),
					'order' => array('Registration.payment' => 'DESC', 'Registration.created' => 'DESC'),
			)));
			$this->set('download_file_name', "Registrations - {$event['Event']['name']}");
		} else {
			$this->set('registrations', $this->paginate ('Registration', array('event_id' => $id)));
		}
		$this->set(compact('event'));
	}

	function summary() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Questionnaire' => array('Question' => array('Answer')),
		));
		$event = $this->Registration->Event->read(null, $id);
		if ($event === false) {
			$this->Session->setFlash(__('Invalid Event', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire']);

		$this->Registration->contain ('Person');
		$gender = $this->Registration->find('all', array(
				'fields' => array(
					'Person.gender',
					'COUNT(Registration.id) AS count',
				),
				'conditions' => array(
					'Registration.event_id' => $id,
					'Registration.payment !=' => 'Refunded',
				),
				'group' => 'Person.gender',
				'order' => array ('Person.gender' => 'DESC'),
		));

		$this->Registration->contain ();
		$payment = $this->Registration->find('all', array(
				'fields' => array(
					'payment',
					'COUNT(payment) AS count',
				),
				'conditions' => array(
					'event_id' => $id,
				),
				'group' => 'payment',
				'order' => 'payment',
		));

		$this->Registration->Response->contain ();
		$responses = $this->Registration->Response->find('all', array(
				'fields' => array(
					'question_id',
					'answer_id',
					'answer',
					'COUNT(answer_id) AS count_id',
					'COUNT(answer) AS count',
				),
				'conditions' => array(
					'event_id' => $id,
					'OR' => array(
						array('answer' => null),	// an answer_id is present
						array('answer' => 1),		// checked checkboxes
					),
				),
				'group' => array('question_id', 'answer_id', 'answer'),
				'order' => 'question_id',
		));

		$this->set(compact ('event', 'gender', 'payment', 'responses'));
	}

	function statistics() {
		$year = $this->_arg('year');
		if ($year === null) {
			$year = date('Y');
		}

		$this->Registration->contain ();
		$this->set('events', $this->Registration->find('all', array(
			'fields' => array(
				'Event.id',
				'Event.name',
				'EventType.name',
				'COUNT(Registration.id) AS count',
			),
			'conditions' => array(
				'Registration.payment !=' => 'Refunded',
				'OR' => array(
					'YEAR(Event.open)' => $year,
					'YEAR(Event.close)' => $year,
				),
			),
			'group' => 'Event.id',
			'order' => array('Event.event_type_id', 'Event.open' => 'DESC', 'Event.close' => 'DESC', 'Event.id'),
			'joins' => array(
				array(
					'table' => "{$this->Registration->tablePrefix}events",
					'alias' => 'Event',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => array('Registration.event_id = Event.id'),
				),
				array(
					'table' => "{$this->Registration->tablePrefix}event_types",
					'alias' => 'EventType',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => array('Event.event_type_id = EventType.id'),
				),
			),
		)));

		$this->Registration->Event->contain();
		$this->set('years', $this->Registration->Event->find('all', array(
			'fields' => 'DISTINCT YEAR(open) AS year',
		)));
	}

	function view() {
		$id = $this->_arg('registration');
		if (!$id) {
			$this->Session->setFlash(__('Invalid registration', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Registration->contain (array(
			'Person',
			'Event' => array(
				'EventType',
				'Questionnaire' => array('Question' => array('Answer')),
			),
			'Response',
			'RegistrationAudit',
		));
		$registration = $this->Registration->read(null, $id);
		if (!$registration) {
			$this->Session->setFlash(__('Invalid registration', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($registration, $event_obj, $registration['Event']['Questionnaire']);
		$this->set(compact('registration'));
	}

	function register() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Questionnaire' => array('Question' => array('Answer')),
		));
		$event = $this->Registration->Event->read(null, $id);
		if ($event === false) {
			$this->Session->setFlash(__('Invalid Event', true));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		// Re-do "can register" checks to make sure someone hasn't hand-fed us a URL
		$test = $this->CanRegister->test ($this->Auth->user('id'), $event);
		if (!$test['allowed']) {
			foreach ($test['messages'] as $key => $message) {
				if (is_array ($message)) {
					$test['messages'][$key] = $message['text'];
				}
			}
			$this->Session->setFlash(implode ('<br>', $test['messages']));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		// Check the waiver, if any
		$this->set('waivered', $this->_checkWaiver($event['Event']));

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire']);

		$empty = empty($this->data);
		$this->data['Registration']['event_id'] = $id;
		if ($event['Event']['cost'] == 0) {
			$this->data['Registration']['payment'] = 'Paid';
		}

		// Data was posted, save it and proceed
		if (!$empty) {
			// array_merge doesn't work, since we have numeric keys
			$this->Registration->Response->validate =
				$event_obj->registrationFieldsValidation ($event) +
				$this->Questionnaire->validation($event['Questionnaire']);

			// This is all a little fragile, because of the weird format of the data we're saving.
			// We need to first set the response data, then validate it.  We can't rely on
			// Registration->saveAll to validate properly.
			$this->Registration->Response->set ($this->data);

			if ($this->Registration->Response->validates()) {
				// Wrap the whole thing in a transaction, for safety.
				$db =& ConnectionManager::getDataSource($this->Registration->useDbConfig);
				$db->begin($this->Registration);

				// TODO: Confirmation page
				// Next, we must do the event registration, as it may add to the $this->data array
				$result = $event_obj->register($event, $this->data);
				if ($result === true) {
					// Now manually add the event id to all of the responses :-(
					foreach (array_keys ($this->data['Response']) as $key) {
						$this->data['Response'][$key]['event_id'] = $id;
					}

					// TODO: 'atomic' can go, once we've upgraded everything to Cake 1.3.6
					if ($this->Registration->saveAll($this->data, array('atomic' => false, 'validate' => false))) {
						$this->Session->setFlash(__('Your preferences for this registration have been saved.', true));
						if ($event['Event']['anonymous']) {
							$this->Registration->Response->updateAll (array('registration_id' => null),
								array('registration_id' => $this->Registration->id));
						}
						if ($db->commit($this->Registration) !== false) {
							$this->redirect(array('action' => 'checkout'));
						} else {
							$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
						}
					} else {
						$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
					}
				} else if ($result === false) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true));
				} else {
					// TODO: Do a validation-only save, add $result to validation errors
					$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
				}

				// If we get here, something failed
				$db->rollback($this->Registration);
			} else {
				$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
			}
		}

		// The event has no questionnaire, save trivial registration data and proceed
		if (empty ($event['Questionnaire']['Question'])) {
			if ($event_obj->register($event, $this->data) === true) {
				if ($this->Registration->save($this->data)) {
					$this->Session->setFlash(__('Your registration for this event has been confirmed.', true));
					$this->Session->delete ('Zuluru.Unpaid');
					$this->redirect (array('action' => 'checkout'));
				} else {
					$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
				}
			} else {
				$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true));
			}
		}

		$this->set(compact ('id', 'event', 'event_obj'));
	}

	function checkout($op = null) {
		$this->Registration->contain (array(
			'Event' => array('EventType'),
		));
		$registrations = $this->Registration->find('all', array(
				'conditions' => array(
					'person_id' => $this->Auth->user('id'),
					'payment' => array('Unpaid', 'Pending'),
				),
		));

		// If there are no unpaid registrations, then we must have gotten here by
		// someone registering for a free event.  In that case, we don't want to
		// disturb the flash message, just go back to the event list.
		if (empty ($registrations)) {
			$this->Session->delete ('Zuluru.Unpaid');
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$this->Registration->Person->recursive = -1;
		$person = $this->Registration->Person->read (null, $this->Auth->user('id'));

		$full = array();
		foreach ($registrations as $key => $registration) {
			// Find the registration cap and how many are already registered.
			$conditions = array(
				'event_id' => $registration['Event']['id'],
				'payment' => array('Paid', 'Pending'),
			);
			if ($registration['Event']['cap_female'] != -2) {
				$conditions['gender'] = $person['Person']['gender'];
			}
			$cap = $this->Registration->Event->cap($registration['Event']['cap_male'], $registration['Event']['cap_female'], $person['Person']['gender']);
			if ($cap != -1) {
				$paid = $this->Registration->find ('count', array('conditions' => $conditions));
				if ($cap <= $paid) {
					$full[] = $registration;
					unset ($registrations[$key]);
				}
			}
		}
		// Reset the array to 0-indexed keys
		$registrations = array_values ($registrations);

		$this->set(compact ('registrations', 'full', 'person'));

		if ($op == 'payment') {
			$this->render ('payment');
		}
	}

	function unregister() {
		$id = $this->_arg('registration');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid registration', true));
			$this->redirect(array('action' => 'checkout'));
		}
		$this->Registration->contain (array(
			'Event' => array('EventType'),
			'Response',
		));
		$registration = $this->Registration->read(null, $id);

		if ($registration['Registration']['payment'] == 'Paid') {
			$this->Session->setFlash(__('You have already paid for this! Contact the office to arrange a refund.', true));
			$this->redirect(array('action' => 'checkout'));
		}
		if ($registration['Registration']['payment'] == 'Refunded') {
			$this->Session->setFlash(__('You have already received a refund for this. Refunded records are kept on file for accounting purposes.', true));
			$this->redirect(array('action' => 'checkout'));
		}

		if (!$this->is_admin && $registration['Registration']['person_id'] != $this->Auth->user('id')) {
			$this->Session->setFlash(__('You may only unregister from events that you have registered for!', true));
			$this->redirect(array('action' => 'checkout'));
		}

		if ($this->Registration->delete()) {
			$this->Session->setFlash(__('Successfully unregistered from this event.', true));

			// Check if anything else must be removed as a result (e.g. team reg after removing membership)
			while ($this->_unregisterDependencies()) {}

			$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
			if (!$event_obj->unregister($registration, $registration)) {
				$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true));
			}
		} else {
			$this->Session->setFlash(__('Failed to unregister from this event!', true));
		}

		$this->redirect(array('action' => 'checkout'));
	}

	function _unregisterDependencies() {
		// Get everything from the user record that the decisions below might need
		$this->Registration->Person->contain (array (
			'Registration' => array(
				'Event' => array('EventType'),
				'Response',
				'conditions' => array('payment NOT ' => array('Refunded', 'Paid'), 'id !=' => 33723),
			),
		));
		$person = $this->Registration->Person->read(null, $this->Auth->user('id'));
		$unregistered = false;

		foreach ($person['Registration'] as $key => $registration) {
			// Check the registration rule, if any
			if (!empty ($registration['Event']['register_rule'])) {
				$rule_obj = AppController::_getComponent ('Rule');
				if ($rule_obj->init ($registration['Event']['register_rule']) &&
					!$rule_obj->evaluate ($person))
				{
					$this->Registration->delete($registration['id']);
					$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
					$event_obj->unregister($registration, $registration);
					unset ($person['Registration'][$key]);
					$unregistered = true;
				}
			}
		}

		return $unregistered;
	}

	function payment() {
		$this->layout = 'bare';
		$payment = $this->_getComponent ('payment', Configure::read('payment.payment_implementation'), $this);
		list ($result, $audit, $registration_ids) = $payment->process ($this->params['form']);
		if ($result) {
			$errors = array();

			$this->Registration->contain (array(
				'Person',
				'Event',
			));
			$registrations = $this->Registration->find ('all', array(
				'conditions' => array('Registration.id' => $registration_ids),
			));
			if (!$this->Registration->updateAll (
				array('Registration.payment' => '"Paid"'),
				array('Registration.id' => $registration_ids)
			))
			{
				$errors[] = sprintf (__('Your payment was approved, but there was an error updating your payment status in the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $audit['order_id']);
			}

			foreach ($registration_ids as $id) {
				$this->Registration->RegistrationAudit->create();
				if (!$this->Registration->RegistrationAudit->save (array_merge($audit, array('registration_id' => $id)))) {
					$errors[] = sprintf (__('There was an error updating the audit record in the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $audit['order_id']);
				}
			}
		}
		$this->set (compact ('result', 'audit', 'registrations', 'errors'));
		$this->Session->delete ('Zuluru.Unpaid');
	}

	function edit() {
		$id = $this->_arg('registration');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid registration', true));
			$this->redirect('/');
		}
		$this->Registration->contain (array(
			'Person',
			'Event' => array(
				'EventType',
				'Questionnaire' => array('Question' => array('Answer')),
			),
			'Response',
		));
		$registration = $this->Registration->read(null, $id);
		if ($registration === false) {
			$this->Session->setFlash(__('Invalid registration', true));
			$this->redirect('/');
		}

		$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($registration, $event_obj, $registration['Event']['Questionnaire']);

		if (!empty($this->data)) {
			if ($this->Registration->saveAll($this->data)) {
				$this->Session->setFlash(__('The registration has been saved', true));
				$this->redirect(array('controller' => 'people', 'action' => 'registrations', 'person' => $registration['Person']['id']));
			} else {
				$this->Session->setFlash(__('The registration could not be saved. Please, try again.', true));
			}
		} else {
			// Something like adjustEntryIndices, need a general-purpose AppModel method for this
			$keys = array_keys ($registration['Response']);
			foreach ($keys as $key) {
				$question = $registration['Response'][$key]['question_id'];
				$registration['Response'][$question] = $registration['Response'][$key];
				unset ($registration['Response'][$key]);
			}

			$this->data = $registration;
		}

		$this->set(compact('registration'));
	}

	function preregistrations() { // TODO
	}

	function unpaid() {
		$this->Registration->contain (array(
			'Event' => array('EventType'),
			'Person',
		));
		$registrations = $this->Registration->find('all', array(
				'conditions' => array(
					'Registration.payment' => array('Unpaid', 'Pending'),
				),
				'order' => array('Registration.payment', 'Registration.modified'),
		));

		$this->set(compact('registrations'));
	}

	function _mergeAutoQuestions($event, $event_obj, &$questionnaire) {
		if (!array_key_exists ('Question', $questionnaire)) {
			$questionnaire['Question'] = array();
		}
		$questionnaire['Question'] = array_merge (
				$questionnaire['Question'], $event_obj->registrationFields($event)
		);
	}
}
?>
