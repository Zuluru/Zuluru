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

	function publicActions() {
		// 'Payment' comes from the payment processor.
		return array('payment');
	}

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

		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'report',
					'unpaid',
					'credits',
					'statistics',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if (!$affiliate) {
					// If there's no affiliate, this is a top-level operation that all managers can perform
					return true;
				} else if (in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
					return true;
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'summary',
					'full_list',
					'waiting',
			)))
			{
				// If an event id is specified, check if we're a manager of that event's affiliate
				$event = $this->_arg('event');
				if ($event) {
					if (in_array($this->Registration->Event->affiliate($event), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'view',
					'edit',
			)))
			{
				// If a registration id is specified, check if we're a manager of that registration's event's affiliate
				$registration = $this->_arg('registration');
				if ($registration) {
					if (in_array($this->Registration->affiliate($registration), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'add_payment',
					'refund_payment',
					'credit_payment',
					'transfer_payment',
			)))
			{
				// If a payment id is specified, check if we're a manager of that payment's registration's event's affiliate
				$payment = $this->_arg('payment');
				if ($payment) {
					if (in_array($this->Payment->affiliate($payment), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function full_list() {
		if (!ini_get('safe_mode')) { 
			set_time_limit(1800);
		}
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Questionnaire' => array('Question' => array('Answer')),
			'Division' => 'League',
		));
		$event = $this->Registration->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire'], null, true);

		if ($this->params['url']['ext'] == 'csv') {
			$this->Registration->contain (array('Person' => $this->Auth->authenticate->name, 'Response'));
			$this->set('registrations', $this->Registration->find ('all', array(
					'conditions' => array('Registration.event_id' => $id),
					'order' => array('Registration.payment' => 'DESC', 'Registration.created' => 'DESC'),
			)));
			$this->set('download_file_name', "Registrations - {$event['Event']['name']}");
			Configure::write ('debug', 0);
		} else {
			$this->paginate['Registration']['limit'] = Configure::read('feature.items_per_page');
			$this->set('registrations', $this->paginate ('Registration', array('event_id' => $id)));
		}
		$this->set(compact('event'));
	}

	function summary() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Questionnaire' => array('Question' => array('Answer')),
			'Division' => 'League',
		));
		$event = $this->Registration->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire'], null, true);

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
					'COUNT(answer_id) AS count',
				),
				'conditions' => array(
					'event_id' => $id,
					'answer' => null,
				),
				'group' => array('question_id', 'answer_id'),
				'order' => 'question_id',
		));

		$this->set(compact ('event', 'gender', 'payment', 'responses'));
	}

	function statistics() {
		$year = $this->_arg('year');
		if ($year === null) {
			$year = date('Y');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->Registration->contain ();
		$events = $this->Registration->find('all', array(
			'fields' => array(
				'Event.id', 'Event.name', 'Event.affiliate_id', 'Event.division_id', 'Event.event_type_id',
				'EventType.id', 'EventType.name', 'EventType.type',
				'Affiliate.name',
				'COUNT(Registration.id) AS count',
			),
			'conditions' => array(
				'Registration.payment !=' => 'Refunded',
				'OR' => array(
					'YEAR(Event.open)' => $year,
					'YEAR(Event.close)' => $year,
				),
				'Event.affiliate_id' => $affiliates,
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
				array(
					'table' => "{$this->Registration->tablePrefix}affiliates",
					'alias' => 'Affiliate',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => array('Event.affiliate_id = Affiliate.id'),
				),
			),
		));

		// Add division information where applicable
		foreach ($events as $id => $event) {
			if (!empty($event['Event']['division_id'])) {
				$this->Registration->Event->Division->contain(array('League', 'Day'));
				$events[$id] += $this->Registration->Event->Division->read(null, $event['Event']['division_id']);
			}
		}

		$this->set(compact('events'));

		$this->Registration->Event->contain();
		$this->set('years', $this->Registration->Event->find('all', array(
			'fields' => 'DISTINCT YEAR(open) AS year',
			'order' => 'open',
		)));
	}

	function report() {
		if (!empty($this->data)) {
			// Deconstruct dates
			$start_date = $this->data['Registration']['start_date']['year'] . '-' . $this->data['Registration']['start_date']['month'] . '-' . $this->data['Registration']['start_date']['day'];
			$end_date = $this->data['Registration']['end_date']['year'] . '-' . $this->data['Registration']['end_date']['month'] . '-' . $this->data['Registration']['end_date']['day'];
		} else {
			$start_date = $this->_arg('start_date');
			$end_date = $this->_arg('end_date');
			if (!$start_date || !$end_date) {
				// Just return, which will present the user with a date selection
				return;
			}
		}

		if ($start_date > $end_date) {
			$this->Session->setFlash(__('Start date must be before end date!', true), 'default', array('class' => 'info'));
			return;
		}

		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliateIDs(true);

		$conditions = array(
			'Registration.created >=' => $start_date,
			'Registration.created <=' => "$end_date 23:59:59",
			'Event.affiliate_id' => $affiliates,
		);

		$contain = array(
			'Event' => array('EventType', 'Affiliate'),
			'Price',
			'Person',
		);
		$order = array('Event.affiliate_id', 'Registration.payment' => 'DESC', 'Registration.created');
		$limit = Configure::read('feature.items_per_page');

		if ($this->params['url']['ext'] == 'csv') {
			$this->set('registrations', $this->Registration->find ('all', compact('conditions', 'contain', 'order')));
			$this->set('download_file_name', "Registrations $start_date to $end_date");
		} else {
			$this->paginate = array(
				'Registration' => compact('conditions', 'contain', 'order', 'limit'),
			);
			$this->set('registrations', $this->paginate ('Registration'));
		}

		$this->set(compact('affiliates', 'affiliate', 'start_date', 'end_date'));
	}

	function view() {
		$id = $this->_arg('registration');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Registration->contain (array(
			'Person',
			'Event' => array(
				'EventType',
				'Questionnaire' => array('Question' => array('Answer')),
				'Division' => 'League',
			),
			'Response',
			'Payment' => array('RegistrationAudit', 'order' => 'Payment.id'),
		));
		$registration = $this->Registration->read(null, $id);
		if (!$registration) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($registration['Event']['affiliate_id']);

		$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($registration, $event_obj, $registration['Event']['Questionnaire'], $registration['Person']['id'], true);
		$this->set(compact('registration'));
	}

	function register() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}

		$price = $this->_arg('option');
		if ($price) {
			$price_condition = array('Price.id' => $price);
		} else {
			$price_condition = array();
		}

		$this->Registration->Event->contain (array(
			'EventType',
			'Price' => array(
				'order' => array('Price.open', 'Price.close', 'Price.id'),
				'conditions' => $price_condition,
			),
			'Questionnaire' => array(
				'Question' => array(
					'Answer' => array(
						'conditions' => array('active' => true),
					),
					'conditions' => array('active' => true),
				),
			),
			'Division' => 'League',
		));
		$event = $this->Registration->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}
		if (empty($event['Price'])) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('option', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		// Re-do "can register" checks to make sure someone hasn't hand-fed us a URL
		$waiting = $this->_arg('waiting');
		$test = $this->CanRegister->test ($this->Auth->user('zuluru_person_id'), $event, array('waiting' => $waiting, 'all_rules' => true));
		if (!$test['allowed']) {
			if ($this->is_logged_in && !empty($test['redirect'])) {
				$this->redirect(array_merge($test['redirect'], array('return' => true)), $this->here);
			}
			foreach ($test['messages'] as $key => $message) {
				if (is_array ($message)) {
					$test['messages'][$key] = $message['text'];
				}
			}
			$this->Session->setFlash(implode ('<br>', $test['messages']), 'default', array('class' => 'warning'));
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}

		if (count($event['Price']) > 1) {
			$this->set(compact('event'));
			$this->set($test);
			$this->render('options');
			return;
		} else {
			$price = $event['Price'][0]['id'];
		}

		$cost = $event['Price'][0]['cost'] + $event['Price'][0]['tax1'] + $event['Price'][0]['tax2'];

		$event_obj = $this->_getComponent ('EventType', $event['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($event, $event_obj, $event['Questionnaire'], $this->Auth->user('zuluru_person_id'));
		$this->set(compact('id', 'event', 'event_obj', 'waiting'));

		// Data was posted, save it and proceed
		if (!empty($this->data)) {
			$this->Registration->Response->validate = array_merge(
				$this->Questionnaire->validation($event['Questionnaire']),
				$event_obj->registrationFieldsValidation ($event)
			);

			// Remove any unchecked checkboxes; we only save the checked ones.
			// $delete will be empty here, we don't need to do anything with it.
			list ($data, $delete) = $this->_splitResponses ($this->data);

			// This is all a little fragile, because of the weird format of the data we're saving.
			// We need to first set the response data, then validate it.  We can't rely on
			// Registration->saveAll to validate properly.
			$this->Registration->Response->set ($data);

			// Validation of payment amounts is a manual process
			if ($event['Price'][0]['allow_deposit'] && $this->data['Registration']['payment_type'] == 'Deposit') {
				if ($event['Price'][0]['fixed_deposit']) {
					$this->data['Registration']['deposit_amount'] = $event['Price'][0]['minimum_deposit'];
				} else if ($this->data['Registration']['deposit_amount'] < $event['Price'][0]['minimum_deposit']) {
					$this->Registration->validationErrors['deposit_amount'] = sprintf(__('A minimum deposit of $%s is required.', true), $event['Price'][0]['minimum_deposit']);
				}
			}

			if (!$this->Registration->Response->validates() || !empty($this->Registration->validationErrors)) {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
				return;
			}

			// Use array_values here to get numeric keys in the data to be saved
			if (is_array($data) && array_key_exists('Response', $data)) {
				$data['Response'] = array_values($data['Response']);
			}

			// Set the flash message that will be used, if there are no errors
			if ($cost == 0) {
				$this->Session->setFlash(__('Your preferences have been saved and your registration confirmed.', true), 'default', array('class' => 'success'));
			} else {
				$this->Session->setFlash(__('Your preferences for this registration have been saved.', true), 'default', array('class' => 'success'));
			}
			$save = true;
		}

		if (empty ($event['Questionnaire']['Question'])) {
			// The event has no questionnaire, save trivial registration data and proceed
			$data = array('Registration' => array(), 'Response' => array());

			// Set the flash message that will be used, if there are no errors
			if ($waiting) {
				$this->Session->setFlash(__('You have been added to the waiting list for this event.', true), 'default', array('class' => 'success'));
			} else {
				$this->Session->setFlash(__('Your registration for this event has been confirmed.', true), 'default', array('class' => 'success'));
			}
			$save = true;
		}

		if (isset ($save)) {
			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Registration);

			$data['Registration']['event_id'] = $id;
			$data['Registration']['total_amount'] = $cost;
			$data['Registration']['price_id'] = $price;
			if (!empty($this->data['Registration']['deposit_amount'])) {
				$data['Registration']['deposit_amount'] = $this->data['Registration']['deposit_amount'];
			}

			// Next, we do the event registration
			$result = $event_obj->register($event, $data);
			if ($result === false) {
				$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
				return;
			}
			if (is_array ($result)) {
				$data['Response'] = array_merge($data['Response'], $result);
			}

			if ($waiting) {
				$data['Registration']['payment'] = 'Waiting';
			} else if ($cost == 0) {
				$this->UserCache->clear('RegistrationsPaid');
				// Free events may need even more processing
				$result = $event_obj->paid($event, $data);
				if ($result === false) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
					return;
				}
				if (is_array ($result)) {
					$data['Response'] = array_merge($data['Response'], $result);
				}
				$data['Registration']['payment'] = 'Paid';
			}

			if (empty ($data['Response'])) {
				unset ($data['Response']);
			} else {
				// Manually add the event id to all of the responses :-(
				foreach (array_keys ($data['Response']) as $key) {
					$data['Response'][$key]['event_id'] = $id;
				}
			}

			if (!$this->Registration->saveAll($data, array('validate' => false))) {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
				return;
			}

			$anonymous = Set::extract ('/Question[anonymous=1]/id', $event['Questionnaire']);
			if (!empty ($anonymous)) {
				$this->Registration->Response->updateAll (array('registration_id' => null),
					array('question_id' => $anonymous));
			}
			if ($transaction->commit() !== false) {
				$this->UserCache->clear('Registrations');
				$this->UserCache->clear('RegistrationsUnpaid');
				$this->redirect(array('action' => 'checkout'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
			}
		}
	}

	function checkout($op = null) {
		$this->Registration->contain (array(
			'Event' => array('EventType'),
			'Price',
			'Payment',
			'Response',
		));
		$registrations = $this->Registration->find('all', array(
				'conditions' => array(
					'person_id' => $this->Auth->user('zuluru_person_id'),
					'payment' => Configure::read('registration_unpaid'),
				),
		));

		// If there are no unpaid registrations, then we must have gotten here by
		// someone registering for a free event.  In that case, we don't want to
		// disturb the flash message, just go back to the event list.
		if (empty ($registrations)) {
			$this->UserCache->clear('Registrations');
			$this->UserCache->clear('RegistrationsPaid');
			$this->UserCache->clear('RegistrationsUnpaid');
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}

		$this->Registration->Person->contain($this->Auth->authenticate->name);
		$person = $this->Registration->Person->read (null, $this->Auth->user('zuluru_person_id'));

		$other = array();
		$affiliate = $this->_arg('affiliate');
		foreach ($registrations as $key => $registration) {
			// Find the registration cap and how many are already registered.
			$conditions = array(
				'Registration.event_id' => $registration['Event']['id'],
				'Registration.payment' => Configure::read('registration_reserved'),
				'Registration.person_id !=' => $person['Person']['id'],
			);
			if ($registration['Event']['cap_female'] != -2) {
				$conditions['gender'] = $person['Person']['gender'];
			}
			$cap = $this->Registration->Event->cap($registration['Event']['cap_male'], $registration['Event']['cap_female'], $person['Person']['gender']);
			if ($cap != -1) {
				$paid = $this->Registration->find ('count', array('conditions' => $conditions));
				if ($cap <= $paid) {
					$other[] = array_merge($registration, array('reason' => 'Filled up since you registered'));
					unset ($registrations[$key]);
					continue;
				}
			}

			// Don't allow the user to pay for things from multiple affiliates at the same time
			if (!$affiliate) {
				$affiliate = $registration['Event']['affiliate_id'];
			} else if ($affiliate != $registration['Event']['affiliate_id']) {
				$other[] = array_merge($registration, array('reason' => 'In a different affiliate'));
				unset ($registrations[$key]);
				continue;
			}

			// Don't allow further payment on "deposit only" items
			if ($registration['Price']['deposit_only'] && in_array($registration['Registration']['payment'], Configure::read('registration_some_paid'))) {
				$other[] = array_merge($registration, array('reason' => 'Deposit paid; balance must be paid off-line'));
				unset ($registrations[$key]);
				continue;
			}

			// Set the description for the invoice
			$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
			$registrations[$key]['Event']['payment_desc'] = $event_obj->longDescription($registration);
		}

		// Reset the array to 0-indexed keys
		$registrations = array_values ($registrations);

		$this->Configuration->loadAffiliate($affiliate);

		$payment_obj = $this->_getComponent ('payment', Configure::read('payment.payment_implementation'), $this);

		$this->set(compact ('registrations', 'other', 'person', 'payment_obj'));

		if ($op == 'payment') {
			$this->render ('payment');
		}
	}

	function unregister() {
		$id = $this->_arg('registration');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'checkout'));
		}
		$this->Registration->contain (array(
			'Event' => array('EventType'),
			'Price',
			'Response',
		));
		$registration = $this->Registration->read(null, $id);

		if (in_array($registration['Registration']['payment'], Configure::read('registration_some_paid')) && $registration['Price']['cost'] > 0) {
			$this->Session->setFlash(__('You have already paid for this! Contact the office to arrange a refund.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'checkout'));
		}
		if (in_array($registration['Registration']['payment'], Configure::read('registration_cancelled'))) {
			$this->Session->setFlash(__('You have already received a refund for this. Refunded records are kept on file for accounting purposes.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'checkout'));
		}

		if (!$this->is_admin &&
			!($this->is_manager && in_array($registration['Event']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'))) &&
			$registration['Registration']['person_id'] != $this->Auth->user('zuluru_person_id')
		)
		{
			$this->Session->setFlash(__('You may only unregister from events that you have registered for!', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'checkout'));
		}
		$this->Configuration->loadAffiliate($registration['Event']['affiliate_id']);

		// Wrap the rest in a transaction, for safety.
		$transaction = new DatabaseTransaction($this->Registration);

		if ($this->Registration->delete()) {
			$success = true;
			$this->Session->setFlash(__('Successfully unregistered from this event.', true), 'default', array('class' => 'success'));

			// Check if anything else must be removed as a result (e.g. team reg after removing membership)
			while ($this->_unregisterDependencies($registration['Registration']['person_id'])) {}

			$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
			if (in_array($registration['Registration']['payment'], Configure::read('registration_paid'))) {
				if (!$event_obj->unpaid($registration, $registration)) {
					$success = false;
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
				}
			}
			if (!$event_obj->unregister($registration, $registration)) {
				$success = false;
				$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
			}

			if ($success) {
				$transaction->commit();
			}
		} else {
			$this->Session->setFlash(__('Failed to unregister from this event!', true), 'default', array('class' => 'warning'));
		}

		$this->redirect(array('action' => 'checkout'));
	}

	function _unregisterDependencies($person_id) {
		// Get everything from the user record that the decisions below might need
		$this->Registration->Person->contain (array (
			'Registration' => array(
				'Event' => array('EventType'),
				'Price',
				'Response',
				'conditions' => array('payment !=' => 'Refunded'),
			),
		));
		$person = $this->Registration->Person->read(null, $person_id);
		$unregistered = false;

		// Pull out the list of unpaid registrations; these are the ones that might be removed
		$unpaid = Set::extract ('/Registration[payment!=Paid]/.', $person);

		foreach ($unpaid as $key => $registration) {
			// Check the registration rule, if any
			if (!empty ($registration['Price']['register_rule'])) {
				$rule_obj = AppController::_getComponent ('Rule');
				if ($rule_obj->init ($registration['Price']['register_rule']) &&
					!$rule_obj->evaluate ($registration['Event']['affiliate_id'], $person))
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
		if (Configure::read('payment.popup')) {
			$this->layout = 'bare';
		}
		$payment_obj = $this->_getComponent ('payment', Configure::read('payment.payment_implementation'), $this);
		list ($result, $audit, $registration_ids) = $payment_obj->process ($this->params);
		if ($result) {
			$errors = array();

			$this->Registration->contain (array(
				'Person',
				'Event' => array(
					'EventType',
					'Division' => 'League',
				),
				'Price',
				'Payment',
				'Response',
			));
			$registrations = $this->Registration->find ('all', array(
				'conditions' => array('Registration.id' => $registration_ids),
			));
			$this->Configuration->loadAffiliate($registrations[0]['Event']['affiliate_id']);

			$this->Registration->Payment->RegistrationAudit->create();
			if (!$this->Registration->Payment->RegistrationAudit->save ($audit)) {
				$errors[] = sprintf (__('There was an error updating the audit record in the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $audit['order_id']);
			}

			foreach ($registrations as $key => $registration) {
				list ($cost, $tax1, $tax2) = Registration::paymentAmounts($registration);
				$paid = array_sum(Set::extract('/Payment/payment_amount', $registration));
				if ($paid == 0) {
					if ($cost == $registration['Price']['cost']) {
						$payment_type = 'Full';
						$payment_status = 'Paid';
					} else {
						$payment_type = $payment_status = 'Deposit';
					}
				} else {
					if ($paid + $cost + $tax1 + $tax2 == $registration['Price']['cost'] + $registration['Price']['tax1'] + $registration['Price']['tax2']) {
						$payment_type = 'Remaining Balance';
						$payment_status = 'Paid';
					} else {
						$payment_type = 'Installment';
						$payment_status = 'Partial';
					}
				}

				$this->Registration->id = $registration['Registration']['id'];
				if (!$this->Registration->saveField('payment', $payment_status)) {
					$errors[] = sprintf (__('Your payment was approved, but there was an error updating your payment status in the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $audit['order_id']);
				} else {
					$registrations[$key]['Registration']['new_payment'] = $payment_status;
				}

				$this->Registration->Payment->create();
				if (!$this->Registration->Payment->save(array(
						'registration_id' => $registration['Registration']['id'],
						'registration_audit_id' => $this->Registration->Payment->RegistrationAudit->id,
						'payment_type' => $payment_type,
						'payment_amount' => $cost + $tax1 + $tax2,
				)))
				{
					$errors[] = sprintf (__('There was an error updating the payment record in the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $audit['order_id']);
				}
			}

			// Wrap the rest in a transaction, for safety. The updates above are
			// intentionally excluded from this, as we always want as much of that
			// saved as possible. Missing team records can easily be added later;
			// missing payments take more work to track down.
			$transaction = new DatabaseTransaction($this->Registration);

			// Do any event payment processing
			$success = true;
			$paid = Configure::read('registration_paid');
			foreach ($registrations as $registration) {
				$was_paid = in_array ($registration['Registration']['payment'], $paid);
				$now_paid = in_array ($registration['Registration']['new_payment'], $paid);
				if (!$was_paid && $now_paid) {
					$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
					$extra = $event_obj->paid($registration, $registration);
					if ($extra) {
						if (is_array ($extra)) {
							// Manually add the event id to all of the responses :-(
							foreach (array_keys ($extra) as $key) {
								$extra[$key]['event_id'] = $registration['Event']['id'];
							}
							$success = $this->Registration->Response->saveAll($extra, array('atomic' => false, 'validate' => false));
							if (!$success) {
								$this->Session->setFlash(sprintf (__('There was an error updating the database. Contact the office to ensure that your information is updated, quoting order #<b>%s</b>, or you may not be allowed to be added to rosters, etc.', true), $registration['Registration']['id']), 'default', array('class' => 'error'));
							}
						}
					} else if ($extra === false) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						$success = false;
					}
					if (!$success) {
						break;
					}

					// If a parent is registering kids, there might be different person IDs for each registration
					$this->UserCache->clear('Registrations', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsPaid', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);
				}
			}

			if ($success) {
				$transaction->commit();
			}
		}
		$this->set (compact ('result', 'audit', 'registrations', 'errors'));
	}

	function add_payment() {
		$id = $this->_arg('registration');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Registration->contain (array(
			'Person',
			'Event' => array(
				// Some of these details are needed if we're completing payment
				'EventType',
				'Questionnaire' => array(
					'Question' => array(
						'Answer' => array(
							'conditions' => array('active' => true),
						),
						'conditions' => array('active' => true),
					),
				),
				'Division' => 'League',
			),
			'Response',
			'Payment',
		));
		$registration = $this->Registration->read(null, $id);
		if (!$registration) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!in_array($registration['Registration']['payment'], Configure::read('registration_unpaid'))) {
			$this->Session->setFlash(sprintf(__('This registration is marked as %s.', true), __($registration['Registration']['payment'], true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'registration' => $registration['Registration']['id']));
		}

		$outstanding = $registration['Registration']['total_amount'] - array_sum(Set::extract('/Payment/payment_amount', $registration));
		if ($outstanding <= 0) {
			$this->Session->setFlash(__('This registration is already paid in full; you may need to edit it manually to mark it as paid.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'registration' => $registration['Registration']['id']));
		}

		$this->Configuration->loadAffiliate($registration['Event']['affiliate_id']);
		$this->set(compact('registration'));

		if (!empty($this->data)) {
			if ($this->data['Payment']['payment_amount'] <= 0) {
				$this->Registration->Payment->validationErrors['amount'] = 'Payment amounts must be positive.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
				return;
			}

			if ($outstanding < $this->data['Payment']['payment_amount']) {
				$this->Registration->Payment->validationErrors['amount'] = 'This would pay more than the amount owing.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
				return;
			}

			$this->data['Payment']['registration_id'] = $id;
			if ($this->data['Payment']['payment_amount'] == $registration['Registration']['total_amount']) {
				$this->data['Payment']['payment_type'] = 'Full';
				$new_payment = 'Paid';
			} else if ($this->data['Payment']['payment_amount'] == $outstanding) {
				$this->data['Payment']['payment_type'] = 'Remaining Balance';
				$new_payment = 'Paid';
			} else if ($registration['Registration']['total_amount'] == $outstanding) {
				$this->data['Payment']['payment_type'] = 'Deposit';
				$new_payment = 'Deposit';
			} else {
				$this->data['Payment']['payment_type'] = 'Installment';
				$new_payment = 'Partial';
			}

			$transaction = new DatabaseTransaction($this->Registration->Payment);

			$this->Registration->Payment->create();
			if ($this->Registration->Payment->save($this->data)) {
				$this->Registration->id = $registration['Registration']['id'];
				if (!$this->Registration->saveField('payment', $new_payment)) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
					return;
				}

				$paid = Configure::read('registration_paid');
				$was_paid = in_array ($registration['Registration']['payment'], $paid);
				$now_paid = in_array ($new_payment, $paid);
				if (!$was_paid && $now_paid) {
					$this->UserCache->clear('Registrations', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsPaid', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);

					$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
					$result = $event_obj->paid($registration, $registration);
					if (!$result) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						return;
					}
					if (is_array ($result)) {
						// Now manually add the event id to all of the responses :-(
						foreach (array_keys ($result) as $key) {
							$result[$key]['event_id'] = $registration['Event']['id'];
						}

						if (!$this->Registration->Response->saveAll($result, array('validate' => false))) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						}
					}
				}

				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('payment', true)), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect(array('action' => 'view', 'registration' => $registration['Registration']['id']));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
			}
		}
	}

	function refund_payment() {
		$id = $this->_arg('payment');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Registration->Payment->contain (array(
			'Registration' => array(
				'Person',
				'Event' => array(
					// Some of these details are needed if we're unregistering someone
					'EventType',
					'Questionnaire' => array(
						'Question' => array(
							'Answer' => array(
								'conditions' => array('active' => true),
							),
							'conditions' => array('active' => true),
						),
					),
					'Division' => 'League',
				),
				'Response',
			),
		));
		$payment = $this->Registration->Payment->read(null, $id);
		if (!$payment) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if ($payment['Payment']['payment_amount'] == $payment['Payment']['refunded_amount']) {
			$this->Session->setFlash(__('This payment has already been fully refunded.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!in_array($payment['Payment']['payment_type'], Configure::read('payment_payment'))) {
			$this->Session->setFlash(__('Only payments can be refunded.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($payment['Registration']['Event']['affiliate_id']);

		$payment_obj = $this->_getComponent ('payment', Configure::read('payment.payment_implementation'), $this);
		$this->set(compact('payment', 'payment_obj'));

		if (!empty($this->data)) {
			if ($this->data['Payment']['amount'] <= 0) {
				$this->Registration->Payment->validationErrors['amount'] = 'Refund amounts must be positive.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
				return;
			}

			$payment['Payment']['refunded_amount'] += $this->data['Payment']['amount'];
			if ($payment['Payment']['refunded_amount'] > $payment['Payment']['payment_amount']) {
				$this->Registration->Payment->validationErrors['amount'] = 'This would refund more than the amount paid.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
				return;
			}

			$transaction = new DatabaseTransaction($this->Registration->Payment);

			if ($this->Registration->Payment->save($payment['Payment'])) {
				$this->Registration->Payment->create();
				if (!$this->Registration->Payment->save(array(
						'registration_id' => $payment['Payment']['registration_id'],
						'payment_type' => 'Refund',
						'payment_amount' => - $this->data['Payment']['amount'],
						'notes' => $this->data['Payment']['notes'],
				)))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
					return;
				}

				if ($payment_obj->can_refund && $this->data['Payment']['online_refund']) {
					if (!$payment_obj->refund($payment)) {
						$this->Session->setFlash(__('Failed to issue refund through online processor. Refund data was NOT saved. You can try again, or uncheck the "Issue refund through online payment provider" box and issue the refund manually.', true), 'default', array('class' => 'warning'));
						return;
					}
				}

				if ($this->data['Payment']['mark_refunded']) {
					$this->Registration->id = $payment['Registration']['id'];
					if (!$this->Registration->saveField('payment', 'Refunded')) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						return;
					}

					if (in_array ($payment['Registration']['payment'], Configure::read('registration_paid'))) {
						$this->UserCache->clear('Registrations', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsPaid', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsUnpaid', $payment['Registration']['person_id']);

						$event_obj = $this->_getComponent ('EventType', $payment['Registration']['Event']['EventType']['type'], $this);
						$result = $event_obj->unpaid($payment['Registration'], $payment['Registration']);
						if (!$result) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						} else if (is_array($result) && !$this->Registration->Response->deleteAll (array('id' => $result), false)) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						}
					}
				}

				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('refund', true)), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('refund', true)), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $payment;
		}
	}

	function credit_payment() {
		$id = $this->_arg('payment');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Registration->Payment->contain (array(
			'Registration' => array(
				'Person',
				'Event' => array(
					// Some of these details are needed if we're unregistering someone
					'EventType',
					'Questionnaire' => array(
						'Question' => array(
							'Answer' => array(
								'conditions' => array('active' => true),
							),
							'conditions' => array('active' => true),
						),
					),
					'Division' => 'League',
				),
				'Response',
			),
		));
		$payment = $this->Registration->Payment->read(null, $id);
		if (!$payment) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if ($payment['Payment']['payment_amount'] == $payment['Payment']['refunded_amount']) {
			$this->Session->setFlash(__('This payment has already been fully refunded.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!in_array($payment['Payment']['payment_type'], Configure::read('payment_payment'))) {
			$this->Session->setFlash(__('Only payments can be credited.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($payment['Registration']['Event']['affiliate_id']);

		$this->set(compact('payment'));

		if (!empty($this->data)) {
			if ($this->data['Payment']['amount'] <= 0) {
				$this->Registration->Payment->validationErrors['amount'] = 'Credit amounts must be positive.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('credit', true)), 'default', array('class' => 'warning'));
				return;
			}

			$payment['Payment']['refunded_amount'] += $this->data['Payment']['amount'];
			if ($payment['Payment']['refunded_amount'] > $payment['Payment']['payment_amount']) {
				$this->Registration->Payment->validationErrors['amount'] = 'This would credit more than the amount paid.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('credit', true)), 'default', array('class' => 'warning'));
				return;
			}

			$transaction = new DatabaseTransaction($this->Registration->Payment);

			if ($this->Registration->Payment->save($payment['Payment'])) {
				$this->Registration->Payment->create();
				if (!$this->Registration->Payment->save(array(
						'registration_id' => $payment['Payment']['registration_id'],
						'payment_type' => 'Credit',
						'payment_amount' => - $this->data['Payment']['amount'],
						'notes' => $this->data['Payment']['payment_notes'],
				)))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('credit', true)), 'default', array('class' => 'warning'));
					return;
				}

				$this->Registration->Person->Credit->create();
				if (!$this->Registration->Person->Credit->save(array(
						'affiliate_id' => $payment['Registration']['Event']['affiliate_id'],
						'person_id' => $payment['Registration']['person_id'],
						'amount' => $this->data['Payment']['amount'],
						'notes' => $this->data['Payment']['credit_notes'],
				)))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved.', true), __('credit', true)), 'default', array('class' => 'warning'));
					return;
				}
				$this->UserCache->clear('Credits', $payment['Registration']['person_id']);

				if ($this->data['Payment']['mark_refunded']) {
					$this->Registration->id = $payment['Registration']['id'];
					if (!$this->Registration->saveField('payment', 'Refunded')) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						return;
					}

					if (in_array ($payment['Registration']['payment'], Configure::read('registration_paid'))) {
						$this->UserCache->clear('Registrations', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsPaid', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsUnpaid', $payment['Registration']['person_id']);

						$event_obj = $this->_getComponent ('EventType', $payment['Registration']['Event']['EventType']['type'], $this);
						$result = $event_obj->unpaid($payment['Registration'], $payment['Registration']);
						if (!$result) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						} else if (is_array($result) && !$this->Registration->Response->deleteAll (array('id' => $result), false)) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						}
					}
				}

				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('credit', true)), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('credit', true)), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $payment;
		}
	}

	function transfer_payment() {
		$id = $this->_arg('payment');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Registration->Payment->contain (array(
			'Registration' => array(
				'Person',
				'Event' => array(
					// Some of these details are needed if we're unregistering someone
					'EventType',
					'Questionnaire' => array(
						'Question' => array(
							'Answer' => array(
								'conditions' => array('active' => true),
							),
							'conditions' => array('active' => true),
						),
					),
					'Division' => 'League',
				),
				'Response',
			),
		));
		$payment = $this->Registration->Payment->read(null, $id);
		if (!$payment) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('payment', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if ($payment['Payment']['payment_amount'] == $payment['Payment']['refunded_amount']) {
			$this->Session->setFlash(__('This payment has already been fully refunded.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!in_array($payment['Payment']['payment_type'], Configure::read('payment_payment'))) {
			$this->Session->setFlash(__('Only payments can be transferred.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($payment['Registration']['Event']['affiliate_id']);

		$unpaid = $this->UserCache->read('RegistrationsUnpaid', $payment['Registration']['person_id']);
		if (empty($unpaid)) {
			$this->Session->setFlash(__('This user has no unpaid registrations to transfer the payment to.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('payment', 'unpaid'));

		if (!empty($this->data)) {
			if ($this->data['Payment']['amount'] <= 0) {
				$this->Registration->Payment->validationErrors['amount'] = 'Transfer amounts must be positive.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
				return;
			}

			$this->Registration->contain (array(
				'Event' => array(
					// Some of these details are needed if we're completing payment
					'EventType',
					'Questionnaire' => array(
						'Question' => array(
							'Answer' => array(
								'conditions' => array('active' => true),
							),
							'conditions' => array('active' => true),
						),
					),
					'Division' => 'League',
				),
				'Response',
				'Payment',
			));
			$registration = $this->Registration->read(null, $this->data['Payment']['registration_id']);
			if (!$registration) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
			}

			if (!in_array($registration['Registration']['payment'], Configure::read('registration_unpaid'))) {
				$this->Session->setFlash(sprintf(__('This registration is marked as %s.', true), __($registration['Registration']['payment'], true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
			}

			$outstanding = $registration['Registration']['total_amount'] - array_sum(Set::extract('/Payment/payment_amount', $registration));
			if ($outstanding <= 0) {
				$this->Session->setFlash(__('This registration is already paid in full; you may need to edit it manually to mark it as paid.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
			}

			$amount = min($outstanding, $this->data['Payment']['amount']);
			$payment['Payment']['refunded_amount'] += $amount;
			if ($payment['Payment']['refunded_amount'] > $payment['Payment']['payment_amount']) {
				$this->Registration->Payment->validationErrors['amount'] = 'This would transfer more than the amount paid.';
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
				return;
			}

			$transaction = new DatabaseTransaction($this->Registration->Payment);

			if ($this->Registration->Payment->save($payment['Payment'])) {
				$this->Registration->Payment->create();
				if (!$this->Registration->Payment->save(array(
						'registration_id' => $payment['Payment']['registration_id'],
						'payment_type' => 'Transfer',
						'payment_amount' => - $amount,
						'notes' => $this->data['Payment']['transfer_from_notes'],
				)))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
					return;
				}

				$this->Registration->Payment->create();
				if (!$this->Registration->Payment->save(array(
						'registration_id' => $this->data['Payment']['registration_id'],
						'payment_type' => 'Transfer',
						'payment_amount' => $amount,
						'notes' => $this->data['Payment']['transfer_to_notes'],
				)))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
					return;
				}

				if ($this->data['Payment']['mark_refunded']) {
					$this->Registration->id = $payment['Registration']['id'];
					if (!$this->Registration->saveField('payment', 'Refunded')) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						return;
					}

					if (in_array ($payment['Registration']['payment'], Configure::read('registration_paid'))) {
						$this->UserCache->clear('Registrations', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsPaid', $payment['Registration']['person_id']);
						$this->UserCache->clear('RegistrationsUnpaid', $payment['Registration']['person_id']);

						$event_obj = $this->_getComponent ('EventType', $payment['Registration']['Event']['EventType']['type'], $this);
						$result = $event_obj->unpaid($payment['Registration'], $payment['Registration']);
						if (!$result) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						} else if (is_array($result) && !$this->Registration->Response->deleteAll (array('id' => $result), false)) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						}
					}
				}

				if ($amount == $outstanding) {
					$new_payment = 'Paid';
				} else {
					$new_payment = 'Pending';
				}

				$this->Registration->id = $registration['Registration']['id'];
				if (!$this->Registration->saveField('payment', $new_payment)) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
					return;
				}

				$paid = Configure::read('registration_paid');
				$was_paid = in_array ($registration['Registration']['payment'], $paid);
				$now_paid = in_array ($new_payment, $paid);
				if (!$was_paid && $now_paid) {
					$this->UserCache->clear('Registrations', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsPaid', $registration['Registration']['person_id']);
					$this->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);

					$result = $event_obj->paid($registration, $registration);
					if (!$result) {
						$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
						return;
					}
					if (is_array ($result)) {
						// Now manually add the event id to all of the responses :-(
						foreach (array_keys ($result) as $key) {
							$result[$key]['event_id'] = $registration['Event']['id'];
						}

						if (!$this->Registration->Response->saveAll($result, array('validate' => false))) {
							$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
							return;
						}
					}
				}

				$this->Session->setFlash(sprintf(__('Transferred $%0.2f', true), $amount), 'default', array('class' => 'success'));
				$transaction->commit();

				// Which registration we redirect to from here depends on how much was transferred
				if ($payment['Payment']['refunded_amount'] < $payment['Payment']['payment_amount']) {
					// There is still unrefunded money on the old registration, go back there
					$this->redirect(array('action' => 'view', 'registration' => $payment['Registration']['id']));
				} else {
					// Go to the registration the money was just transferred to
					$this->redirect(array('action' => 'view', 'registration' => $registration['Registration']['id']));
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('transfer', true)), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $payment;
		}
	}

	function edit() {
		$id = $this->_arg('registration');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Registration->contain (array(
			'Person',
			'Event' => array(
				'EventType',
				'Questionnaire' => array(
					'Question' => array(
						'Answer' => array(
							'conditions' => array('active' => true),
						),
						'conditions' => array('active' => true),
					),
				),
				'Division' => 'League',
			),
			'Response',
		));
		$registration = $this->Registration->read(null, $id);
		if (!$registration) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('registration', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($registration['Event']['affiliate_id']);

		$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
		$this->_mergeAutoQuestions ($registration, $event_obj, $registration['Event']['Questionnaire'], $registration['Person']['id']);
		$this->set(compact('registration'));

		if (!empty($this->data)) {
			$this->Registration->Response->validate = array_merge(
				$this->Questionnaire->validation($registration['Event']['Questionnaire'], true),
				$event_obj->registrationFieldsValidation ($registration, true)
			);

			// Remove any unchecked checkboxes; we only save the checked ones.
			list ($data, $delete) = $this->_splitResponses ($this->data);

			// This is all a little fragile, because of the weird format of the data we're saving.
			// We need to first set the response data, then validate it.  We can't rely on
			// Registration->saveAll to validate properly.
			$this->Registration->Response->set ($data);

			if (!$this->Registration->Response->validates()) {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
				return;
			}

			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Registration);

			// Use array_values here to get numeric keys in the data to be saved
			if (is_array($data) && array_key_exists('Response', $data)) {
				$data['Response'] = array_values($data['Response']);
			}

			// If the payment status has changed, we may need to do extra processing
			$paid = Configure::read('registration_paid');
			$was_paid = in_array ($registration['Registration']['payment'], $paid);
			$now_paid = in_array ($data['Registration']['payment'], $paid);
			if (!$was_paid && $now_paid) {
				$this->UserCache->clear('Registrations', $registration['Registration']['person_id']);
				$this->UserCache->clear('RegistrationsPaid', $registration['Registration']['person_id']);
				$this->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);

				// When it's marked as paid, the responses that the event object
				// should use are the new ones just now submitted.
				$result = $event_obj->paid($registration, $data);
				if (!$result) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
					return;
				}
				if (is_array ($result)) {
					$data['Response'] = array_merge($data['Response'], $result);
				}
			} else if ($was_paid && !$now_paid) {
				$this->UserCache->clear('Registrations', $registration['Registration']['person_id']);
				$this->UserCache->clear('RegistrationsPaid', $registration['Registration']['person_id']);
				$this->UserCache->clear('RegistrationsUnpaid', $registration['Registration']['person_id']);

				// When it's marked as unpaid, the responses that the event object
				// should use are the saved ones.
				$result = $event_obj->unpaid($registration, $registration);
				if (!$result) {
					$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
					return;
				}
				if (is_array ($result)) {
					$delete = array_merge ($delete, $result);
				}
			}

			// TODO: Redo the event registration, in case anything has changed. But
			// how will this interact with the payment status change handling above?
			$result = true; //$event_obj->reregister($registration, $data);
			if (!$result) {
				$this->Session->setFlash(__('Failed to perform additional registration-related operations.', true), 'default', array('class' => 'warning'));
				return;
			}

			// Now manually add the event id to all of the responses :-(
			if (is_array($data) && array_key_exists('Response', $data)) {
				foreach (array_keys ($data['Response']) as $key) {
					$data['Response'][$key]['event_id'] = $registration['Event']['id'];
				}
			}

			if (!$this->Registration->saveAll($data, array('validate' => false))) {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
				return;
			}

			// Remove any old response records that are no longer valid
			if (!empty($delete)) {
				if (!$this->Registration->Response->deleteAll (array(
					'id' => $delete,
					), false))
				{
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('registration', true)), 'default', array('class' => 'warning'));
					return;
				}
			}

			if ($transaction->commit() !== false) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('registration', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'people', 'action' => 'registrations', 'person' => $registration['Person']['id']));
			}
		} else {
			// Convert saved response data into the format required by the output
			$this->data = $registration;
			$responses = array();
			foreach ($registration['Event']['Questionnaire']['Question'] as $question) {
				if (array_key_exists ('id', $question)) {
					$saved = Set::extract ("/Response[question_id={$question['id']}]", $registration);
					if (!empty ($saved)) {
						if ($question['type'] == 'checkbox') {
							// Deal with both checkbox groups and single checkboxes
							if (!empty($question['Answer'])) {
								foreach ($question['Answer'] as $answer) {
									$id = Set::extract ("/Response[answer_id={$answer['id']}]", $saved);
									if (!empty ($id)) {
										$responses[Question::_formName($question, $answer)] = $id[0]['Response'];
									}
								}
							} else {
								$responses[Question::_formName($question)] = $saved[0]['Response'];
							}
						} else {
							$responses[Question::_formName($question)] = $saved[0]['Response'];
						}
					}
				}
			}
			$this->data['Response'] = $responses;
		}
	}

	function unpaid() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$registrations = $this->Registration->find('all', array(
			'conditions' => array(
				'Registration.payment' => Configure::read('registration_unpaid'),
				'Event.affiliate_id' => $affiliates,
			),
			'contain' => array(
				'Event' => array('EventType', 'Affiliate'),
				'Price',
				'Person',
			),
			'order' => array('Event.affiliate_id', 'Registration.payment', 'Registration.modified'),
		));
		if (empty($registrations)) {
			$this->Session->setFlash(__('There are no unpaid registrations.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('registrations', 'affiliates'));
	}

	function credits() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$credits = $this->Registration->Person->Credit->find('all', array(
			'conditions' => array(
				'Credit.amount != Credit.amount_used',
				'Credit.affiliate_id' => $affiliates,
			),
			'contain' => array(
				'Affiliate',
				'Person',
			),
			'order' => array('Credit.affiliate_id', 'Credit.created'),
		));
		if (empty($credits)) {
			$this->Session->setFlash(__('There are no unused credits.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('credits', 'affiliates'));
	}

	function waiting() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'wizard'));
		}

		$this->Registration->Event->contain ();
		$event = $this->Registration->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		$registrations = $this->Registration->find('all', array(
			'conditions' => array(
				'Registration.payment' => array('Waiting'),
				'Registration.event_id' => $id,
			),
			'contain' => array(
				'Person',
				'Price',
			),
			'order' => array('Registration.created'),
		));
		if (empty($registrations)) {
			$this->Session->setFlash(__('There is nobody on the waiting list for this event.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('event', 'registrations'));
	}

	function _mergeAutoQuestions($event, $event_obj, &$questionnaire, $user_id = null, $for_output = false) {
		if (!array_key_exists ('Question', $questionnaire)) {
			$questionnaire['Question'] = array();
		}
		$questionnaire['Question'] = array_merge (
			$questionnaire['Question'], $event_obj->registrationFields($event, $user_id, $for_output)
		);
	}

	function _splitResponses($data) {
		// Make a list of old entries that now have answer_id = 0 (to delete)
		$delete = Set::extract ('/Response[answer_id=0][id>0]/id', $data);

		// Next, we remove any new checkbox entries with answer_id = 0 (not to be saved)
		if (is_array($data) && array_key_exists('Response', $data)) {
			foreach ($data['Response'] as $key => $response) {
				if (strpos ($key, 'a') !== false && $response['answer_id'] === '0') {
					unset ($data['Response'][$key]);
				}
			}
		}

		return array($data, $delete);
	}
}
?>
