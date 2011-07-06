<?php
class EventsController extends AppController {

	var $name = 'Events';
	var $components = array('CanRegister');

	function index() {
		if ($this->is_admin) {
			$close = 'DATE_ADD(CURDATE(), INTERVAL -30 DAY)';
		} else {
			$close = 'CURDATE()';
		}
		$this->set('events', $this->Event->find('all', array(
			'conditions' => array(
				'Event.open < DATE_ADD(CURDATE(), INTERVAL 30 DAY)',
				"Event.close > $close",
			),
			'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			'contain' => array('EventType'),
		)));
	}

	function wizard($step = null) {
		if (!$this->is_logged_in) {
			$this->redirect(array('action' => 'index'));
		}
		$id = $this->Auth->user('id');

		// Find all the events that are potentially available
		// TODO: Include preregistrations in this query
		// TODO: Eliminate the events that don't match the step, if any
		$events = $this->Event->find('all', array(
			'conditions' => array(
				'Event.open < DATE_ADD(CURDATE(), INTERVAL 30 DAY)',
				'Event.close > CURDATE()',
			),
			'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			'contain' => array('EventType'),
		));

		$types = $this->Event->EventType->find('all', array(
			'order' => array('EventType.id'),
		));

		// Prune out the events that are not possible
		foreach ($events as $key => $event) {
			$test = $this->CanRegister->test ($id, $event);
			if (!$test['allowed']) {
				unset ($events[$key]);
			}
		}

		$this->set(compact('events', 'types', 'step'));
	}

	function view() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'wizard'));
		}
		$this->Event->contain (array(
			'EventType',
		));
		$event = $this->Event->read(null, $id);
		if ($event === false) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'wizard'));
		}

		// Extract some more details, if it's a league registration
		if (array_key_exists ('team_league', $event['Event']) && $event['Event']['team_league'] != null) {
			$league = ClassRegistry::init ('League');
			$league->contain (array(
					'LeagueGameslotAvailability' => array(
						'GameSlot' => array(
							'Field' => 'ParentField',
						),
					),
			));
			$event += $league->read(null, $event['Event']['team_league']);

			// Find the list of sites and time slots
			$sites = $times = array();
			foreach ($event['LeagueGameslotAvailability'] as $avail) {
				$slot = $avail['GameSlot'];
				if ($slot['Field']['parent_id'] === null) {
					$sites[$slot['Field']['id']] = $slot['Field']['name'];
				}
				$times[$slot['game_start']] = $slot['game_end'];
			}
			asort ($times);
		}

		if ($this->is_logged_in) {
			$this->set ($this->CanRegister->test ($this->Auth->user('id'), $event));
		}

		$this->set(compact ('id', 'event', 'sites', 'times'));
	}

	function add() {
		if (!empty($this->data)) {
			// Validation requires this information
			$this->data = array_merge ($this->data, $this->Event->EventType->read(null, $this->data['Event']['event_type_id']));

			$this->Event->create();
			if ($this->Event->save($this->data)) {
				$this->Session->setFlash(__('The event has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		} else {
			// Set up defaults
			$this->data = array('Event' => array(
					'EventType' => array(
						'type' => 'generic',
					),
			));
		}
		$this->set('eventTypes', $this->Event->EventType->find('list'));
		$this->set('questionnaires', $this->Event->Questionnaire->find('list'));
		$this->set('event_obj', $this->_getComponent ('EventType', $this->data['Event']['EventType']['type'], $this));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('event');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			// Validation requires this information
			$type = $this->Event->EventType->read(null, $this->data['Event']['event_type_id']);
			if (empty ($type)) {
				// We need something here to avoid errors
				$type = array('EventType' => array('type' => null));
			}
			$this->data = array_merge ($this->data, $type);

			if ($this->Event->save($this->data)) {
				$this->Session->setFlash(__('The event has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->Event->contain (array (
				'EventType',
			));
			$this->data = $this->Event->read(null, $id);
		}

		$this->set('eventTypes', $this->Event->EventType->find('list'));
		$this->set('questionnaires', $this->Event->Questionnaire->find('list', array('conditions' => array(
				'Questionnaire.active' => true,
		))));
		$this->set('event_obj', $this->_getComponent ('EventType', $this->data['EventType']['type'], $this));
	}

	function event_type_fields() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->Event->contain (array (
			'EventType',
		));
		$type = $this->Event->EventType->read(null, $this->params['url']['data']['Event']['event_type_id']);
		$this->set('event_obj', $this->_getComponent ('EventType', $type['EventType']['type'], $this));
	}

	function delete() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for event', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Event->delete($id)) {
			$this->Session->setFlash(__('Event deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Event was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>
