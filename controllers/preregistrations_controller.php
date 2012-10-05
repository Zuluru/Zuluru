<?php
class PreregistrationsController extends AppController {

	var $name = 'Preregistrations';
	var $components = array('CanRegister');

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				// If an event id is specified, check if we're a manager of that event's affiliate
				$event = $this->_arg('event');
				if ($event) {
					$affiliate = $this->Preregistration->Event->field('affiliate_id', array('Event.id' => $event));
					if (in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				} else {
					// If there's no event id, this is a top-level operation that all managers can perform
					return true;
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate['Preregistration'] = array(
			'contain' => array(
				'Person',
				'Event' => 'Affiliate',
			),
			'limit' => Configure::read('feature.items_per_page'),
			'conditions' => array('Event.affiliate_id' => $affiliates),
		);
		if ($this->_arg('event')) {
			$id = $this->_arg('event');
			$this->paginate['Preregistration']['conditions']['event_id'] = $id;
			$this->Preregistration->Event->contain('Affiliate');
			$this->set('event', $this->Preregistration->Event->read(null, $id));
		}
		$this->set('preregistrations', $this->paginate('Preregistration'));
		$this->set(compact('affiliates'));
	}

	function add() {
		$params = $url = $this->_extractSearchParams();
		unset ($params['event']);
		if (array_key_exists('event', $url)) {
			$event = $this->Preregistration->Event->read(null, $url['event']);
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

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
						'conditions' => $this->_generateSearchConditions($params, 'Person', 'AffiliatePerson'),
						'contain' => array('Affiliate'),
						'limit' => Configure::read('feature.items_per_page'),
						'joins' => array(array(
							'table' => "{$this->Person->tablePrefix}affiliates_people",
							'alias' => 'AffiliatePerson',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'AffiliatePerson.person_id = Person.id',
						)),
					);
					$this->set('people', $this->paginate('Person'));
				}
			}
			$this->set(compact('url'));
			if (!empty($url['event'])) {
				$this->set(compact('event'));
				$this->render('add_to_event');
			} else {
				$events = $this->Preregistration->Event->find('all', array(
						'conditions' => array(
							// Unlikely that we want to let someone post-register for something
							// that closed more than 3 months ago
							'Event.close > DATE_SUB(CURDATE(), INTERVAL 3 MONTH)',
							'Event.affiliate_id' => $affiliates,
						),
						'contain' => array('Affiliate'),
						'order' => array('Affiliate.name', 'Event.open DESC', 'Event.id DESC'),
				));

				if (count($affiliates) > 1) {
					$names = array();
					foreach ($events as $event) {
						$names[$event['Affiliate']['name']][$event['Event']['id']] = $event['Event']['name'];
					}
					$events = $names;
				} else {
					$events = Set::combine($events, '{n}.Event.id', '{n}.Event.name');
				}

				$this->set(compact('events'));

				if (array_key_exists('event', $url)) {
					$this->Session->setFlash(__('You must select an event!', true), 'default', array('class' => 'warning'));
				}
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
