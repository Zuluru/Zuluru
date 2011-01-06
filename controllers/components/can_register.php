<?php
class CanRegisterComponent extends Object
{
	function initialize(&$controller, $settings = array()) {
		// Save the controller reference for later use
		$this->controller =& $controller;
	}

	function test($user_id, $event) {
		if (!isset ($this->Html)) {
			App::import ('helper', 'Html');
			$this->Html = new HtmlHelper();
		}

		// Get everything from the user record that the decisions below might need
		if (!isset ($this->controller->Person)) {
			$this->controller->Person = ClassRegistry::init ('Person');
		}
		$this->controller->Person->contain (array (
			'Registration' => array(
				'Event' => array(
					'EventType',
				),
				'conditions' => array('payment !=' => 'Refunded'),
				'order' => 'payment DESC',	// This only works because Unpaid > Pending > Paid
			),
			'Preregistration' => array('conditions' => array('event_id' => $event['Event']['id'])),
		));
		// TODO: Cache any of this for multiple calls?
		$person = $this->controller->Person->read(null, $user_id);

		// Pull out the registration record(s) for the current event, if any.
		$registrations = Set::extract ("/Event[id={$event['Event']['id']}]/..", $person['Registration']);
		$is_registered = !empty ($registrations);

		// Check the registration rule, if any
		if (!empty ($event['Event']['register_rule'])) {
			$rule_obj = AppController::_getComponent ('Rule');
			if (!$rule_obj->init ($event['Event']['register_rule'])) {
				$this->Session->setFlash(__('Failed to parse the rule', true));
			}

			$rule_allowed = $rule_obj->evaluate ($person);
			$rule_reason = $rule_obj->reason;
		}

		// Find the registration cap and how many are already registered.
		$conditions = array(
			'event_id' => $event['Event']['id'],
			'payment' => array('Paid', 'Pending'),
		);
		if ($event['Event']['cap_female'] != -2) {
			$conditions['gender'] = $person['Person']['gender'];
		}

		$cap = Event::cap($event['Event']['cap_male'], $event['Event']['cap_female'], $person['Person']['gender']);
		if (!isset ($this->controller->Event)) {
			$this->controller->Event = ClassRegistry::init ('Event');
		}
		if ($cap != -1) {
			$paid = $this->controller->Event->Registration->find ('count', array('conditions' => $conditions));
		}

		// Check whether this user is considered active for the purposes of registration
		$is_active = ($this->controller->Session->read('Zuluru.Person.status') == 'active');
		// If the user is not yet approved, we may let them register but not pay
		if ($this->controller->Session->read('Zuluru.Person.status') == 'new' && Configure::read('registration.allow_tentative')) {
			$duplicates = $this->controller->Person->findDuplicates ($person);
			if (empty ($duplicates)) {
				$is_active = true;
			}
		}

		// Now, we determine whether they are allowed to register
		$continue = true;
		$messages = array();

		// First, some tests based on whether the person has already registered for this.
		if ($is_registered) {
			if ($registrations[0]['payment'] == 'Paid' ) {
				$messages[] = __('You have already registered and paid for this event.', true);
			} else {
				$messages[] = __('You have already registered for this event, but not yet paid.', true);

				// An unpaid registration might have been pre-empted by someone who paid.
				if ($registrations[0]['payment'] == 'Unpaid' && $cap > 0 && $paid >= $cap ) {
					$messages[] = __('Your payment was not received in time, so your registration has been moved to a waiting list. If you have any questions about this, please contact the head office.', true);
				} else {
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

		// If there is a preregistration record, we ignore open and close times.
		if (empty ($person['Preregistration'])) {
			// Admins can test registration before it opens...
			if (!$this->controller->is_admin) {
				if (strtotime ($event['Event']['open']) > time()) {
					$messages[] = __('This event is not yet open for registration.', true);
					$continue = false;
				}
			}
			if (time() > strtotime ($event['Event']['close'])) {
				$messages[] = __('Registration for this event has closed.', true);
				$continue = false;
			}
		}

		if ($continue && !$is_active) {
			$messages[] = __('You may not register for this event until your account has been approved by an administrator. This normally happens in less than one business day, and often in just a few minutes.', true);
			$continue = false;
		}

		if ($continue) {
			if ($cap == 0) {
				// 0 means that nobody of this gender is allowed.
				$messages[] = __('This event is for the opposite gender only.', true);
				$continue = false;
			} else if ($cap > 0) {
				// Check if this event is already full
				// -1 means there is no cap, so don't check in that case.
				if ($paid >= $cap) {
					// TODO: Allow people to put themselves on a waiting list
					$admin_name = Configure::read('email.admin_name');
					$admin_addr = Configure::read('email.admin_email');
					$messages[] = sprintf (__('This event is already full.  You may email the %s or phone the head office to be put on a waiting list in case others drop out.', true),
							$this->Html->link ($admin_name, "mailto:$admin_addr"));
					$continue = false;
				}
			}
		}

		$allowed = false;
		if ($continue) {
			if ($continue !== true) {
				$messages[] = $continue;
			}
			if (isset ($rule_allowed)) {
				if ($rule_allowed) {
					$messages[] = __('You may register for this event.', true);
					$allowed = true;
				} else {
					$messages[] = __('You may not register for this event', true) . ': ' . $rule_reason;
				}
			} else {
				$messages[] = __('You may register for this because there are no prerequisites.', true);
				$allowed = true;
			}
		}

		return compact('allowed', 'messages');
	}
}
?>