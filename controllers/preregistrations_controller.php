<?php
class PreregistrationsController extends AppController {

	var $name = 'Preregistrations';
	var $components = array('CanRegister');

	function index() {
		$this->paginate['Preregistration'] = array(
			'contain' => array(
				'Person',
				'Event',
			),
		);
		if ($this->_arg('event')) {
			$id = $this->_arg('event');
			$this->paginate['Preregistration']['conditions'] = array('event_id' => $id);
			$this->set('event', $this->Preregistration->Event->read(null, $id));
		}
		$this->set('preregistrations', $this->paginate('Preregistration'));
	}

	function add() {
		$params = $url = $this->_extractSearchParams();
		unset ($params['event']);
		if (array_key_exists('event', $url)) {
			$event = $this->Preregistration->Event->read(null, $url['event']);
		}

		if (array_key_exists('event', $url) && array_key_exists('person', $url)) {
			$data = array(
				'event_id' => $url['event'],
				'person_id' => $url['person'],
			);
			$found = $this->Preregistration->find('count', array('conditions' => $data));
			if ($found) {
				$this->Session->setFlash(__('This player already has a preregistration for this event', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'add', 'event' => $url['event']));
			}
			$test = $this->CanRegister->test ($url['person'], $event, true);
			if (!$test['allowed']) {
				$this->Session->setFlash(implode ('<br>', Set::extract('/messages/text', $test)), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'add', 'event' => $url['event']));
			}
			$this->Preregistration->create();
			if ($this->Preregistration->save($data))
			{
				$this->Session->setFlash(__('The preregistration has been saved', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The preregistration could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
			}
		} else {
			if (!empty($params)) {
				$test = trim (@$params['first_name'], ' *') . trim (@$params['last_name'], ' *');
				if (strlen ($test) < 2) {
					$this->set('short', true);
				} else {
					// This pagination needs the model at the top level
					$this->Person = $this->Preregistration->Person;
					$this->_mergePaginationParams();
					$this->paginate['Person'] = array(
						'conditions' => $this->_generateSearchConditions($params, 'Person'),
						'contain' => false,
					);
					$this->set('people', $this->paginate('Person'));
				}
			}
			$this->set(compact('url'));
			if (array_key_exists('event', $url)) {
				$this->set(compact('event'));
				$this->render('add_to_event');
			} else {
				$events = $this->Preregistration->Event->find('list', array(
						'conditions' => array(
							// Unlikely that we want to let someone post-register for something
							// that closed more than 3 months ago
							'Event.close > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)',
						),
						'order' => 'Event.open DESC, Event.id DESC',
				));
				$this->set(compact('events'));
			}
		}
	}

	function delete() {
		$id = $this->_arg('prereg');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for preregistration', true), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Preregistration->delete($id)) {
			$this->Session->setFlash(__('Preregistration deleted', true), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Preregistration was not deleted', true), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}
}
?>