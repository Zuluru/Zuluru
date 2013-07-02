<?php
class EventsController extends AppController {

	var $name = 'Events';
	var $components = array('Lock', 'CanRegister');

	function publicActions() {
		return array('cron', 'index', 'view', 'wizard');
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'count',
		)))
		{
			return true;
		}

		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'add',
					'event_type_fields',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'connections',
					'delete',
			)))
			{
				// If an event id is specified, check if we're a manager of that event's affiliate
				$event = $this->_arg('event');
				if ($event) {
					if (in_array($this->Event->affiliate($event), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		if ($this->is_admin || $this->is_manager) {
			// Admins and managers see things that have recently close, or open far in the future
			$close = 'DATE_ADD(CURDATE(), INTERVAL -30 DAY)';
			$open = 'DATE_ADD(CURDATE(), INTERVAL 180 DAY)';
		} else {
			$close = 'CURDATE()';
			$open = 'DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
		}

		if (empty($this->params['requested'])) {
			$affiliates = $this->_applicableAffiliateIDs();
		} else {
			$affiliates = $this->_applicableAffiliateIDs(true);
		}

		$events = $this->Event->find('all', array(
			'conditions' => array(
				"Event.open < $open",
				"Event.close > $close",
				'Event.affiliate_id' => $affiliates,
			),
			'order' => array('Affiliate.name', 'Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			'contain' => array('EventType', 'Affiliate'),
		));

		if (!empty($this->params['requested'])) {
			return $events;
		}

		$this->set(compact('affiliates', 'events'));
	}

	function wizard($step = null) {
		if (!$this->is_logged_in) {
			$this->redirect(array('action' => 'index'));
		}
		$id = $this->Auth->user('id');

		// Find any preregistrations
		$prereg = $this->Event->Preregistration->find('list', array(
			'conditions' => array('person_id' => $id),
			'fields' => array('id', 'event_id'),
		));

		// Find all the events that are potentially available
		// TODO: Eliminate the events that don't match the step, if any
		$affiliates = $this->_applicableAffiliateIDs();
		$events = $this->Event->find('all', array(
			'conditions' => array(
				'OR' => array(
					array(
						'Event.open < DATE_ADD(CURDATE(), INTERVAL 30 DAY)',
						'Event.close > CURDATE()',
					),
					'Event.id' => $prereg,
				),
				'Event.affiliate_id' => $affiliates,
			),
			'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			'contain' => array('EventType', 'Affiliate'),
		));

		$types = $this->Event->EventType->find('all', array(
			'order' => array('EventType.id'),
		));

		// Prune out the events that are not possible
		foreach ($events as $key => $event) {
			$test = $this->CanRegister->test ($id, $event, false, false, true);
			if (!$test['allowed']) {
				unset ($events[$key]);
			}
		}

		$this->set(compact('events', 'types', 'affiliates', 'step'));
	}

	function view() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'wizard'));
		}

		if ($this->is_manager && !in_array($this->Event->affiliate($id), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
			$this->is_manager = false;
		}
		if ($this->is_admin || $this->is_manager) {
			// Admins and managers see things that have recently close, or open far in the future
			$close = 'DATE_ADD(CURDATE(), INTERVAL -30 DAY)';
			$open = 'DATE_ADD(CURDATE(), INTERVAL 180 DAY)';
		} else {
			$close = 'CURDATE()';
			$open = 'DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
		}

		$this->Event->contain (array(
			'EventType',
			'Division' => array(
				'DivisionGameslotAvailability' => array(
					'GameSlot' => array(
						'Field' => 'Facility',
					),
				),
				'Day',
				'Event' => array(
					'EventType',
					'conditions' => array('Event.id !=' => $id),
				),
			),
			'Alternate' => array(
				'EventType',
				'conditions' => array(
					"Alternate.open < $open",
					"Alternate.close > $close",
				),
			),
			'Affiliate',
		));
		$event = $this->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'wizard'));
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		// Extract some more details, if it's a division registration
		if (!empty($event['Event']['division_id'])) {
			// Find the list of facilities and time slots
			$facilities = $times = array();
			foreach ($event['Division']['DivisionGameslotAvailability'] as $avail) {
				$slot = $avail['GameSlot'];
				$facilities[$slot['Field']['Facility']['id']] = $slot['Field']['Facility']['name'];
				$times[$slot['game_start']] = $slot['game_end'];
			}
			asort ($times);
		}

		if ($this->is_logged_in) {
			$this->set ($this->CanRegister->test ($this->Auth->user('id'), $event));
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact ('id', 'event', 'facilities', 'times', 'affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			// Validation requires this information
			$type = $this->Event->EventType->read(null, $this->data['Event']['event_type_id']);
			if (!$type) {
				// We need something here to avoid errors
				$type = array('EventType' => array('type' => null));
			}
			$this->data = array_merge ($this->data, $type);

			$this->Event->create();
			if ($this->Event->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('event', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('event', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Event']['affiliate_id']);
			}
		} else {
			// Set up defaults
			$this->data = array('EventType' => array(
					'type' => 'generic',
			));
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set('eventTypes', $this->Event->EventType->find('list'));
		$this->set('questionnaires', $this->Event->Questionnaire->find('list', array('conditions' => array(
				'Questionnaire.active' => true,
				'Questionnaire.affiliate_id' => array_keys($affiliates),
		))));
		$this->set('event_obj', $this->_getComponent ('EventType', $this->data['EventType']['type'], $this));
		$this->set(compact('affiliates'));
		$this->set('add', true);

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('event');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			// Validation requires this information
			$type = $this->Event->EventType->read(null, $this->data['Event']['event_type_id']);
			if (!$type) {
				// We need something here to avoid errors
				$type = array('EventType' => array('type' => null));
			}
			$this->data = array_merge ($this->data, $type);

			if ($this->Event->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('event', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('event', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Event->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Event->contain (array (
				'EventType',
			));
			$this->data = $this->Event->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Event']['affiliate_id']);
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set('eventTypes', $this->Event->EventType->find('list'));
		$this->set('questionnaires', $this->Event->Questionnaire->find('list', array('conditions' => array(
				'Questionnaire.active' => true,
				'Questionnaire.affiliate_id' => array_keys($affiliates),
		))));
		$this->set('event_obj', $this->_getComponent ('EventType', $this->data['EventType']['type'], $this));
		$this->set(compact('affiliates'));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function event_type_fields() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->Event->contain (array (
			'EventType',
		));
		$type = $this->Event->EventType->read(null, $this->params['url']['data']['Event']['event_type_id']);
		$this->set('event_obj', $this->_getComponent ('EventType', $type['EventType']['type'], $this));
		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function delete() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Event->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this event, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Event->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Event', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Event', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function connections() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (!empty($this->data)) {
			$transaction = new DatabaseTransaction($this->Event->EventsConnection);
			$success = true;

			// Alternates always go both ways
			$this->data['Event']['AlternateTo'] = $this->data['Event']['Alternate'];

			foreach (Configure::read('event_connection') as $type => $name) {
				$save = array();
				$success &= $this->Event->EventsConnection->deleteAll(array(
						'event_id' => $id,
						'connection' => $type,
				));
				$success &= $this->Event->EventsConnection->deleteAll(array(
						'connected_event_id' => $id,
						'connection' => $type,
				));

				if (!empty($this->data['Event'][$name])) {
					foreach ($this->data['Event'][$name] as $connection) {
						$save[] = array(
							'event_id' => $id,
							'connection' => $type,
							'connected_event_id' => $connection,
						);
					}
				}

				if (!empty($this->data['Event'][$name . 'To'])) {
					foreach ($this->data['Event'][$name . 'To'] as $connection) {
						$save[] = array(
							'event_id' => $connection,
							'connection' => $type,
							'connected_event_id' => $id,
						);
					}
				}

				if (!empty($save)) {
					$success &= $this->Event->EventsConnection->saveAll($save);
				}
			}
			if ($success) {
				$transaction->commit();
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('connections', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'event' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('connections', true)), 'default', array('class' => 'warning'));
			}
		}

		if (empty($this->data)) {
			$this->Event->contain (array(
				'Predecessor',
				'Successor',
				'Alternate',
				'PredecessorTo',
				'SuccessorTo',
			));
			$this->data = $this->Event->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
		}

		$events = $this->Event->find('all', array(
			'conditions' => array(
				'Event.id !=' => $id,
				"Event.open > DATE_ADD('{$this->data['Event']['open']}', INTERVAL -18 MONTH)",
				"Event.open < DATE_ADD('{$this->data['Event']['open']}', INTERVAL 18 MONTH)",
				"Event.close > DATE_ADD('{$this->data['Event']['close']}', INTERVAL -18 MONTH)",
				"Event.close < DATE_ADD('{$this->data['Event']['close']}', INTERVAL 18 MONTH)",
				'Event.affiliate_id' => $this->data['Event']['affiliate_id'],
			),
			'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			'fields' => array('Event.id', 'Event.name', 'Event.open', 'Event.close', 'Event.event_type_id'),
			'contain' => array('EventType'),
		));
		$event_types = $this->Event->EventType->find('list');

		$this->set(compact('events', 'event_types'));
	}

	function count($membership = false) {
		if (!Configure::read('feature.registration')) {
			return 0;
		}

		$conditions = array(
			'open < CURDATE()',
			'close > CURDATE()',
			'affiliate_id' => $this->_applicableAffiliateIDs(),
		);
		$membership_types = $this->Event->EventType->find('list', array(
			'conditions' => array('type' => 'membership'),
			'fields' => array('id', 'id'),
		));
		if ($membership) {
			$conditions['event_type_id'] = $membership_types;
		} else {
			$conditions['NOT'] = array('event_type_id' => $membership_types);
		}

		return $this->Event->find('count', array(
				'conditions' => $conditions,
				'contain' => array(),
		));
	}

	function cron() {
		if (!Configure::read('feature.registration') || !Configure::read('feature.badges')) {
			return;
		}

		$badges = $this->Event->Registration->Person->Badge->find('all', array(
				'conditions' => array(
					'Badge.category' => 'registration',
					'Badge.active' => true,
				),
				'contain' => array(),
		));
		if (empty($badges)) {
			return;
		}
		$badge_obj = $this->_getComponent('badge', '', $this);

		$this->layout = 'bare';
		if (!ini_get('safe_mode')) { 
			set_time_limit(0);
		}

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		$activity_log = ClassRegistry::init('ActivityLog');
		$today = date('Y-m-d');
		$transaction = new DatabaseTransaction($this->Event);

		// Find all membership events for which the membership has started,
		// but we haven't opened it. The only ones that can possibly be
		// opened are ones that are closed, but not even all of those will be.
		$events = $this->Event->find('all', array(
				'conditions' => array(
					'NOT' => array('id' => $activity_log->find('list', array(
						'conditions' => array('type' => 'membership_opened'),
						'fields' => array('id', 'custom'),
					))),
					'open <= CURDATE()',
					'affiliate_id' => $this->_applicableAffiliateIDs(),
					'event_type_id' => $this->Event->EventType->find('list', array(
						'conditions' => array('type' => 'membership'),
						'fields' => array('id', 'id'),
					)),
				),
				'contain' => array(),
		));

		foreach ($events as $event) {
			if ($event['Event']['membership_begins'] <= $today) {
				$this->Event->contain(array('Registration' => array(
						'conditions' => array('Registration.payment' => array('Paid', 'Pending')),
				)));
				$event = $this->Event->read(null, $event['Event']['id']);
				foreach ($event['Registration'] as $person) {
					// We are only dealing with paid and pending registrations, so the $extra parameter is true
					$badge_obj->update('registration', array('Registration' => $person), true);
				}
				$activity_log->create();
				$activity_log->save(array('type' => 'membership_opened', 'custom' => $event['Event']['id']));
			}
		}

		// Find all membership events for which the membership has ended,
		// but we haven't closed it. The only ones that can possibly be
		// ended are ones that are closed, but not even all of those will be.
		$events = $this->Event->find('all', array(
				'conditions' => array(
					'NOT' => array('id' => $activity_log->find('list', array(
						'conditions' => array('type' => 'membership_closed'),
						'fields' => array('id', 'custom'),
					))),
					'close < CURDATE()',
					'affiliate_id' => $this->_applicableAffiliateIDs(),
					'event_type_id' => $this->Event->EventType->find('list', array(
						'conditions' => array('type' => 'membership'),
						'fields' => array('id', 'id'),
					)),
				),
				'contain' => array(),
		));

		foreach ($events as $event) {
			if ($event['Event']['membership_ends'] < $today) {
				$this->Event->contain(array('Registration' => array(
						'conditions' => array('Registration.payment' => array('Paid', 'Pending')),
				)));
				$event = $this->Event->read(null, $event['Event']['id']);
				foreach ($event['Registration'] as $person) {
					// We are only dealing with paid and pending registrations, so the $extra parameter is true
					$badge_obj->update('registration', array('Registration' => $person), true);
				}
				$activity_log->create();
				$activity_log->save(array('type' => 'membership_closed', 'custom' => $event['Event']['id']));
			}
		}

		$transaction->commit();
		$this->Lock->unlock();
	}
}
?>
