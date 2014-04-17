<?php
class CanRegisterComponent extends Object
{
	// Cached versions of some data, for when we call test() again and again
	var $person = null;
	var $person_id = null;
	var $person_duplicates = null;

	function initialize(&$controller, $settings = array()) {
		// Save the controller reference for later use
		$this->_controller =& $controller;
	}

	/**
	 * Test whether a user is allowed to register for something
	 *
	 * @param mixed $person_id Person id
	 * @param mixed $event Event id
	 * @param mixed $options Options controlling the returned data
	 *		'ignore_date': If true, ignore open and close dates (default false)
	 *		'strict': If false, allow things with prerequisites that are not yet filled but can easily be (default true)
	 *		'waiting' If true, ignore the cap to allow waiting list registrations (default false)
	 *		'all_rules': If true, test tries all price points instead of exiting as soon as an allowed one is found (default false)
	 *		'for_edit': If true, we are editing a registration, so skip tests related to already being registered (default false)
	 *		'simple_output': If true, output messages are consolidated into a single string (default false)
	 * @return mixed True if the user can register for the event
	 */
	function test($person_id, $event, $options = array()) {
		extract(array_merge(array('ignore_date' => false, 'strict' => true, 'waiting' => false, 'all_rules' => false, 'for_edit' => false, 'simple_output' => false), $options));

		if (!isset ($this->Html)) {
			App::import ('helper', 'Html');
			$this->Html = new HtmlHelper();
		}

		// Get everything from the user record that the decisions below might need
		if ($person_id != $this->person_id) {
			$this->person_id = $person_id;
			$this->person = array(
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
			$this->person_duplicates = null;
		}

		// If we're editing a registration, remove it from the user's list,
		// as it only causes problems with rules in the CanRegister test
		if ($for_edit && !empty($event['Registration'])) {
			$this->person['Registration'] = Set::extract ("/Registration[id!={$event['Registration']['id']}]/..", $this->person['Registration']);
		}

		// Pull out the registration record(s) for the current event, if any.
		$registrations = Set::extract ("/Event[id={$event['Event']['id']}]/..", $this->person['Registration']);
		$is_registered = !empty ($registrations);

		// Find the registration cap and how many are already registered.
		$conditions = array(
			'Registration.event_id' => $event['Event']['id'],
			'Registration.payment' => Configure::read('registration_reserved'),
		);
		if ($event['Event']['cap_female'] != -2) {
			$conditions['Person.gender'] = $this->person['Person']['gender'];
		}

		$cap = Event::cap($event['Event']['cap_male'], $event['Event']['cap_female'], $this->person['Person']['gender']);
		if (!isset ($this->_controller->Event)) {
			$this->_controller->Event = ClassRegistry::init ('Event');
		}
		if ($cap != -1) {
			$this->_controller->Event->Registration->contain ('Person');
			$paid = $this->_controller->Event->Registration->find ('count', array('conditions' => $conditions));
		}

		// Check whether this user is considered active for the purposes of registration
		$is_active = ($this->person['Person']['status'] == 'active');
		// If the user is not yet approved, we may let them register but not pay
		if ($this->person['Person']['status'] == 'new' && Configure::read('registration.allow_tentative')) {
			if ($this->person_duplicates === null) {
				if (!isset ($this->_controller->Person)) {
					$this->_controller->Person = ClassRegistry::init ('Person');
				}
				$this->person_duplicates = $this->_controller->Person->findDuplicates ($this->person);
			}
			if (empty ($this->person_duplicates)) {
				$is_active = true;
			}
		}

		// Now, we determine whether they are allowed to register
		$continue = true;
		$messages = array();

		if (in_array($this->person['Person']['status'], array('inactive', 'locked'))) {
			$messages[] = array('text' => __('Your account is currently inactive, please contact an administrator to reactivate it.', true), 'class' => 'warning-message');
			$continue = false;
		}

		// First, some tests based on whether the person has already registered for this.
		if ($continue && $is_registered && !$for_edit) {
			if ($registrations[0]['Registration']['payment'] == 'Paid' ) {
				$messages[] = array('text' => __('You have already registered and paid for this event.', true), 'class' => 'open');
			} else if (Configure::read('feature.waiting_list') && $registrations[0]['Registration']['payment'] == 'Waiting' ) {
				$messages[] = array('text' => __('You have already been added to the waiting list for this event.', true), 'class' => 'open');
			} else {
				// An unpaid registration might have been pre-empted by someone who paid.
				// TODO: After a while, all such items will have been taken care of elsewhere
				// and this whole block can go away.
				if ($registrations[0]['Registration']['payment'] == 'Unpaid' && $cap > 0 && $paid >= $cap ) {
					if (Configure::read('feature.waiting_list')) {
						// Move this, and any other Unpaid registrations for this event
						$conditions = array(
							'Registration.event_id' => $event['Event']['id'],
							'Registration.payment' => 'Unpaid',
						);
						if ($event['Event']['cap_female'] != -2) {
							$conditions['Person.gender'] = $this->person['Person']['gender'];
						}
						if (Configure::read('registration.delete_unpaid')) {
							$event_obj = $this->_controller->_getComponent ('EventType', $event['EventType']['type'], $this->_controller);
							$this->_controller->Event->Registration->contain ('Person');
							$unpaid = $this->_controller->Event->Registration->find ('all', array('conditions' => $conditions));
							foreach ($unpaid as $registration) {
								$event_obj->unregister($registration, $registration);
							}
							$messages[] = array('text' => __('Your payment was not received in time, so your registration has been removed. If you have any questions about this, please contact the head office.', true), 'class' => 'error-message');
						} else {
							$this->_controller->Event->Registration->updateAll(array('payment' => '"Waiting"'), $conditions);
							$messages[] = array('text' => __('Your payment was not received in time, so your registration has been moved to a waiting list. If you have any questions about this, please contact the head office.', true), 'class' => 'error-message');
						}

						$this->_controller->UserCache->clear('Registrations', $this->person['Person']['id']);
						$this->_controller->UserCache->clear('RegistrationsUnpaid', $this->person['Person']['id']);
					} else {
						$messages[] = array('text' => __('Your payment was not received in time, so you will not be able to complete this registration. If you have any questions about this, please contact the head office.', true), 'class' => 'error-message');
					}
				} else {
					$messages[] = array('text' => __('You have already registered for this event, but not yet paid.', true), 'class' => 'warning-message');
					$messages[] = sprintf (__('To complete your payment, please proceed to the %s.', true),
						$this->Html->link(__('checkout page', true), array('controller' => 'registrations', 'action' => 'checkout')));
				}
				$messages[] = sprintf (__('If you registered in error or have changed your mind about participating, you can remove this from your %s.', true),
					$this->Html->link(__('registration list', true), array('controller' => 'registrations', 'action' => 'checkout')));
			}

			// If we allow multiple registrations, remember this.
			// Don't echo it right now, as it would be confusing if it's full or closed.
			if ($event['Event']['multiple'])
			{
				$continue = __('This event allows multiple registrations (e.g. the same person can register teams to play on different nights).', true);
			} else {
				$continue = false;
			}
		}

		// Price data comes in different forms, depending on how it was read
		if (!empty($event['Event']['Price'])) {
			$prices = $event['Event']['Price'];
		} else {
			$prices = $event['Price'];
			if (array_key_exists('id', $prices)) {
				$prices = array($prices);
			}
		}
		if (isset($price_id)) {
			$prices = Set::extract("/Price[id={$price_id}]/.", array('Price' => $prices));
		}

		// If there is a preregistration record, we ignore open and close times.
		$prereg = Set::extract ("/Preregistration[event_id={$event['Event']['id']}]", $this->person['Preregistration']);
		if ($continue && empty ($prereg) && !$ignore_date) {
			$open = strtotime(min(Set::extract('/open', $prices)));
			$close = strtotime(max(Set::extract('/close', $prices)));
			// Admins can test registration before it opens...
			if (!$this->_controller->is_admin && $open + Configure::read('timezone.adjust') * 60 > time()) {
				$messages[] = array('text' => sprintf(__('Registration for %s is not yet open.', true), __('this event', true)), 'class' => 'closed');
				$continue = false;
			}
			if (time() > $close + Configure::read('timezone.adjust') * 60) {
				$messages[] = array('text' => sprintf(__('Registration for %s has closed.', true), __('this event', true)), 'class' => 'closed');
				$continue = false;
			}
		}

		if ($continue && !$is_active) {
			$messages[] = array('text' => __('You may not register for this event until your account has been approved by an administrator. This normally happens in less than one business day, and often in just a few minutes.', true), 'class' => 'warning-message');
			$continue = false;
		}

		if ($continue && !$for_edit) {
			if ($cap == 0) {
				// 0 means that nobody of this gender is allowed.
				$messages[] = array('text' => __('This event is for the opposite gender only.', true), 'class' => 'error-message');
				$continue = false;
			} else if ($cap > 0 && !$waiting) {
				// Check if this event is already full
				// -1 means there is no cap, so don't check in that case.
				if ($paid >= $cap) {
					if (Configure::read('feature.waiting_list')) {
						$messages[] = array('text' => sprintf (__('This event is already full.  You may however %s to be put on a waiting list in case others drop out.', true),
								$this->Html->link (__('continue with registration', true), array('controller' => 'registrations', 'action' => 'register', 'event' => $event['Event']['id'], 'waiting' => true))),
								'class' => 'highlight-message');
					} else {
						$messages[] = array('text' => __('This event is already full.', true), 'class' => 'highlight-message');
					}
					$continue = false;
				}
			}
		}

		$allowed = false;
		if ($continue) {
			if ($continue !== true) {
				$messages[] = $continue;
			}

			// Check each price point
			$rule_obj = AppController::_getComponent ('Rule');
			$price_allowed = array();
			foreach ($prices as $price) {
				$name = empty($price['name']) ? __('this event', true) : $price['name'];

				// Admins can test registration before it opens...
				if (!$this->_controller->is_admin && strtotime($price['open']) + Configure::read('timezone.adjust') * 60 > time()) {
					$price_allowed[$price['id']] = array(
						'allowed' => false,
						'messages' => sprintf(__('Registration for %s is not yet open.', true), $name),
					);
					continue;
				}
				if (time() > strtotime($price['close']) + Configure::read('timezone.adjust') * 60) {
					$price_allowed[$price['id']] = array(
						'allowed' => false,
						'messages' => sprintf(__('Registration for %s has closed.', true), $name),
					);
					continue;
				}

				// Check the registration rule, if any
				if (!empty ($price['register_rule'])) {
					if (!$rule_obj->init ($price['register_rule'])) {
						$this->_controller->Session->setFlash(__('Failed to parse the rule', true), 'default', array('class' => 'error'));
					} else {
						$price_allowed[$price['id']] = array(
							'allowed' => $rule_obj->evaluate($event['Event']['affiliate_id'], $this->person, null, $strict, false),
							'reason' => $rule_obj->reason,
						);
						if ($price_allowed[$price['id']]['allowed']) {
							$allowed = true;
							$price_allowed[$price['id']]['messages'] = sprintf(__('You may register for %s.', true), $name);
							if (!$all_rules) {
								break;
							}
						} else {
							$price_allowed[$price['id']]['messages'] = sprintf(__('To register for %s, you must %s.', true), $name, $rule_obj->reason);
							$price_allowed[$price['id']]['redirect'] = $rule_obj->redirect;
						}
					}
				} else {
					$price_allowed[$price['id']] = array(
						'allowed' => true,
						'messages' => __('You may register for this because there are no prerequisites.', true),
					);
					$allowed = true;
				}
			}

			// We checked earlier that there is at least one price point currently applicable,
			// which means that at least one thing went through the rule check above.
			if ($allowed) {
				if (count($price_allowed) == 1) {
					$price_result = reset($price_allowed);
					$messages[] = $price_result['messages'];
				}
			} else if (empty($price_allowed)) {
				$messages[] = __('You may register for this because there are no prerequisites.', true);
				$allowed = true;
			} else {
				if (count($price_allowed) == 1) {
					$price_result = reset($price_allowed);
					if ($price_result['allowed']) {
						$messages[] = $price_result['messages'];
						$allowed = true;
					} else {
						$messages[] = array('text' => sprintf(__('To register for %s, you must %s.', true), __('this event', true), $price_result['reason']), 'class' => 'error-message');
						if ($strict && $price_result['redirect']) {
							$redirect = $price_result['redirect'];
						}
					}
				} else {
					$reasons = array_unique(Set::extract('/reason', $price_allowed));
					if (count($reasons) == 1) {
						$messages[] = array('text' => sprintf(__('To register for %s, you must %s.', true), __('this event', true), reset($reasons)), 'class' => 'error-message');
					} else {
						foreach ($price_allowed as $price_result) {
							$messages[] = array('text' => $price_result['messages'], 'class' => 'error-message');
						}
					}
				}
			}
		}

		if ($simple_output) {
			foreach ($messages as $key => $message) {
				if (is_array($message)) {
					$messages[$key] = $message['text'];
				}
			}
			$messages = implode('<br>', $messages);
		}

		return compact('allowed', 'messages', 'redirect', 'price_allowed');
	}
}
?>
