<?php
class BadgesController extends AppController {

	var $name = 'Badges';
	var $uses = array('Badge', 'Game', 'Team', 'Registration');

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'index', 'view', 'tooltip',
		)))
		{
			return true;
		}

		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'add',
					'deactivated',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'activate',
					'deactivate',
			)))
			{
				// If a badge id is specified, check if we're a manager of that badge's affiliate
				$badge = $this->_arg('badge');
				if ($badge) {
					if (in_array($this->Badge->affiliate($badge), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate['conditions'] = array(
			'Badge.active' => true,
			'Badge.affiliate_id' => $affiliates,
		);
		$this->paginate['contain'] = array();
		if (!$this->is_admin && !$this->is_manager) {
			// TODO: if manager, check we're not looking at another affiliate
			$this->paginate['conditions']['Badge.visibility !='] = BADGE_VISIBILITY_ADMIN;
		}

		$badges = $this->paginate();
		foreach ($badges as $key => $badge) {
			$badges[$key]['count'] = $this->Badge->BadgesPerson->find('count', array(
				'conditions' => array(
					'BadgesPerson.approved' => true,
					'BadgesPerson.badge_id' => $badge['Badge']['id'],
				),
				'fields' => 'DISTINCT BadgesPerson.person_id',
			));
		}

		$this->set('active', true);
		$this->set(compact('affiliates', 'badges'));
	}

	function deactivated() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate['conditions'] = array(
			'Badge.active' => false,
			'Badge.affiliate_id' => $affiliates,
		);
		$this->paginate['contain'] = array();
		if (!$this->is_admin && !$this->is_manager) {
			// TODO: if manager, check we're not looking at another affiliate
			$this->paginate['conditions']['Badge.visibility !='] = BADGE_VISIBILITY_ADMIN;
		}

		$this->set('badges', $this->paginate());
		$this->set('active', false);
		$this->set(compact('affiliates'));
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('badge');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Badge->contain(array());
		$badge = $this->Badge->read(null, $id);
		if (!$badge) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if ((!$badge['Badge']['active'] || $badge['Badge']['visibility'] == BADGE_VISIBILITY_ADMIN) && (!$this->is_admin && !$this->is_manager)) {
			// TODO: if manager, check we're not looking at another affiliate
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($badge['Badge']['affiliate_id']);

		$this->paginate = array('BadgesPerson' => array(
				'conditions' => array(
					'BadgesPerson.approved' => true,
					'BadgesPerson.badge_id' => $id,
				),
				'contain' => array(
					'Person',
				),
				'fields' => array('DISTINCT Person.id', 'Person.first_name', 'Person.last_name'),
				'order' => array('Person.first_name','Person.last_name'),
				'limit' => Configure::read('feature.items_per_page'),
		));
		$badge['Person'] = $this->paginate('BadgesPerson');

		// Rearrange the data into a more helpful form. Fill in some details
		// that containment doesn't seem to want to handle
		foreach ($badge['Person'] as $key => $person) {
			$badge['Person'][$key]['BadgesPerson'] = array();
			$records = $this->Badge->BadgesPerson->find('all', array(
				'conditions' => array(
					'badge_id' => $id,
					'person_id' => $person['Person']['id'],
				),
				'contain' => array(),
			));

			foreach ($records as $record) {
				if (!empty($record['BadgesPerson']['game_id'])) {
					$this->Game->contain(array(
						'Division' => 'League',
						'GameSlot'
					));
					$record['BadgesPerson']['Game'] = $this->Game->read(null, $record['BadgesPerson']['game_id']);
				} else if (!empty($record['BadgesPerson']['team_id'])) {
					$this->Team->contain(array(
						'Division' => 'League',
					));
					$record['BadgesPerson']['Team'] = $this->Team->read(null, $record['BadgesPerson']['team_id']);
				} else if (!empty($record['BadgesPerson']['registration_id'])) {
					$this->Registration->contain(array(
						'Event'
					));
					$record['BadgesPerson']['Registration'] = $this->Registration->read(null, $record['BadgesPerson']['registration_id']);
				}
				$badge['Person'][$key]['BadgesPerson'][] = $record['BadgesPerson'];
			}
		}

		$this->set(compact('badge'));
	}

	function initialize() {
		$id = $this->_arg('badge');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Badge->contain();
		$badge = $this->Badge->read(null, $id);
		if (!$badge) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (in_array($badge['Badge']['category'], array('nominated', 'assigned', 'runtime', 'aggregate'))) {
			$this->Session->setFlash(sprintf(__('This badge is %s, not calculated, so it cannot be initialized.', true), __($badge['Badge']['category'], true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($badge['Badge']['handler'])) {
			$this->Session->setFlash(__('This badge has no handler.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Configuration->loadAffiliate($badge['Badge']['affiliate_id']);
		$this->set(compact('badge'));

		// TODO: Graceful handling of missing handlers
		$handler = $this->_getComponent('Badge', $badge['Badge']['handler'], $this);
		$count = 0;

		$transaction = new DatabaseTransaction($this->Badge);
		$this->Badge->BadgesPerson->deleteAll(array('badge_id' => $badge['Badge']['id']));

		switch ($badge['Badge']['category']) {
			case 'team':
				$this->Team->contain(array('Division'));
				$teams = $this->Team->find('all');
				foreach ($teams as $team) {
					if ($handler->applicable($team)) {
						$people = $this->Team->TeamsPerson->find('all', array(
							'conditions' => array(
								'TeamsPerson.team_id' => $team['Team']['id'],
								'TeamsPerson.status' => ROSTER_APPROVED,
								'TeamsPerson.role' => Configure::read('regular_roster_roles'),
							),
							'contain' => array(),
						));
						foreach ($people as $person) {
							$badge_data = array(
								'badge_id' => $badge['Badge']['id'],
								'person_id' => $person['TeamsPerson']['person_id'],
								'team_id' => $person['TeamsPerson']['team_id'],
								'approved' => true,
							);
							$this->Badge->BadgesPerson->create();
							$this->Badge->BadgesPerson->save($badge_data);
							++ $count;
						}
					}
				}
				break;

			case 'game':
				$this->Game->contain();
				$games = $this->Game->find('all');
				foreach ($games as $game) {
					$people = array();
					$this->Team->contain(array('Person' => array(
						'conditions' => array(
							'TeamsPerson.role' => Configure::read('regular_roster_roles'),
							'TeamsPerson.status' => ROSTER_APPROVED,
						),
					)));
					if ($handler->applicable($game, $game['Game']['home_team'])) {
						$people = $this->Team->read(null, $game['Game']['home_team']);
					} else if ($handler->applicable($game, $game['Game']['away_team'])) {
						$people = $this->Team->read(null, $game['Game']['away_team']);
					}
					if (!empty($people)) {
						foreach ($people['Person'] as $person) {
							$badge_data = array(
								'badge_id' => $badge['Badge']['id'],
								'person_id' => $person['id'],
								'team_id' => $people['Team']['id'],
								'game_id' => $game['Game']['id'],
								'approved' => true,
							);
							$this->Badge->BadgesPerson->create();
							$this->Badge->BadgesPerson->save($badge_data);
							++ $count;
						}
					}
				}
				break;

			case 'registration':
				$this->Registration->Event->contain(array('EventType'));
				$events = $this->Registration->Event->find('all');
				foreach ($events as $event) {
					if ($handler->applicable($event)) {
						$people = $this->Registration->find('all', array(
							'conditions' => array(
								'Registration.event_id' => $event['Event']['id'],
								'Registration.payment' => array('Paid', 'Pending'),
							),
							'contain' => array(),
						));
						foreach ($people as $person) {
							$badge_data = array(
								'badge_id' => $badge['Badge']['id'],
								'person_id' => $person['Registration']['person_id'],
								'registration_id' => $person['Registration']['id'],
								'approved' => true,
							);
							$this->Badge->BadgesPerson->create();
							$this->Badge->BadgesPerson->save($badge_data);
							++ $count;
						}
					}
				}
				break;

			default:
				$this->Session->setFlash(sprintf(__('Unrecognized badge category "%s".', true), __($badge['Badge']['category'], true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
				break;
		}

		$transaction->commit();
		$this->Session->setFlash(sprintf(__('Badge has been initialized, and has been awarded %d times.', true), $count), 'default', array('class' => 'info'));
		$this->redirect(array('action' => 'view', 'badge' => $badge['Badge']['id']));
	}

	function tooltip() {
		$id = $this->_arg('badge');
		if (!$id) {
			return;
		}
		$this->Badge->contain(array('Person' => array('conditions' => array('BadgesPerson.approved' => true))));
		$badge = $this->Badge->read(null, $id);
		if (!$badge) {
			return;
		}
		$this->Configuration->loadAffiliate($badge['Badge']['affiliate_id']);
		$this->set(compact('badge'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function add() {
		if (!empty($this->data)) {
			$this->Badge->create();
			if ($this->Badge->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('badge', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('badge', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Badge']['affiliate_id']);
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('badge');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Badge->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('badge', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('badge', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->MailingList->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Badge->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Badge']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Badge->field('name', array('id' => $badge));

		$success = $this->Badge->updateAll (array('Badge.active' => true), array(
				'Badge.id' => $badge,
		));
		$this->set(compact('success', 'name'));
	}

	function deactivate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Badge->field('name', array('id' => $badge));

		$success = $this->Badge->updateAll (array('Badge.active' => 0), array(
				'Badge.id' => $badge,
		));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('badge');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Badge->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this badge, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Badge->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Badge', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Badge', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

}
?>