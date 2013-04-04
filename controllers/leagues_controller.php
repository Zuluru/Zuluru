<?php
class LeaguesController extends AppController {

	var $name = 'Leagues';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	function publicActions() {
		return array('cron', 'index', 'view', 'division_count');
	}

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'add',
					'summary',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'delete',
					'participation',
			)))
			{
				// If a league id is specified, check if we're a manager of that league's affiliate
				$league = $this->_arg('league');
				if ($league) {
					if (in_array($this->League->affiliate($league), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$year = $this->_arg('year');
		if ($year === null) {
			$conditions = array('Division.is_open' => true);
		} else {
			$conditions = array('YEAR(Division.open)' => $year);
		}

		$sport = $this->_arg('sport');
		if ($sport) {
			$conditions['League.sport'] = $sport;
		}

		$affiliate = $this->_arg('affiliate');
		if (empty($this->params['requested'])) {
			$affiliates = $this->_applicableAffiliateIDs();
		} else {
			$affiliates = $this->_applicableAffiliateIDs(true);
		}
		$conditions['League.affiliate_id'] = $affiliates;

		$divisions = $this->League->Division->find('all', array(
			'conditions' => $conditions,
			'contain' => array(
				'League' => array('Affiliate'),
				'Day',
			),
		));
		$this->League->Division->addPlayoffs($divisions);

		// Find any newly created leagues with no divisions, for administrators
		if ($this->is_admin || $this->is_manager) {
			$leagues = $this->League->find('all', array(
				'conditions' => array(
					'League.open' => '0000-00-00',
					'League.affiliate_id' => $this->_applicableAffiliateIDs(true),
				),
				'contain' => array('Affiliate'),
			));
			// Re-jig the array format
			foreach (array_keys($leagues) as $key) {
				$leagues[$key]['League']['Affiliate'] = $leagues[$key]['Affiliate'];
				unset($leagues[$key]['Affiliate']);
			}
			$divisions = array_merge($divisions, $leagues);
		}

		usort ($divisions, array('League', 'compareLeagueAndDivision'));
		$this->set(compact('divisions', 'affiliate', 'affiliates', 'sport'));

		if (!empty($this->params['requested'])) {
			return $divisions;
		}

		$this->set('years', $this->League->find('all', array(
			'fields' => 'DISTINCT YEAR(League.open) AS year',
			'conditions' => array(
				'YEAR(League.open) !=' => 0,
				'League.affiliate_id' => $affiliates,
			),
			'contain' => false,
			'order' => 'League.open',
		)));
	}

	function summary() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$divisions = $this->League->Division->find('all', array(
			'conditions' => array(
				'OR' => array(
					'League.is_open' => true,
					'League.open > CURDATE()',
				),
				'League.affiliate_id' => $affiliates,
			),
			'contain' => array('League' => 'Affiliate', 'Day'),
		));
		usort ($divisions, array('League', 'compareLeagueAndDivision'));
		$this->set(compact('divisions', 'affiliates'));
	}

	function view() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain(array(
			'Division' => array(
				'Person',
				'Day' => array('order' => 'day_id'),
				'Team' => array ('Person', 'Franchise'),
			),
			'Affiliate',
		));
		$league = $this->League->read(null, $id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");

		$this->set(compact ('league'));

		$this->set('is_manager', $this->is_manager && in_array($league['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs')));

		$divisions = $this->Session->read('Zuluru.DivisionIDs');
		if (!empty($divisions)) {
			$coordinated_divisions = array_intersect(Set::extract('/Division/id', $league), $divisions);
		} else {
			$coordinated_divisions = null;
		}
		$this->set('is_coordinator', !empty($coordinated_divisions));

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function division_count() {
		$id = $this->_arg('league');
		if (!$id) {
			return 0;
		}
		return $this->League->Division->find('count', array(
				'contain' => array(),
				'conditions' => array('Division.league_id' => $id),
		));
	}

	function participation() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->League->contain (array(
			'Division' => array('Team' => array('Person')),
		));
		$league = $this->League->read(null, $id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);

		if ($this->params['url']['ext'] == 'csv') {
			$this->set('download_file_name', "Participation - {$league['League']['full_name']}");
			Configure::write ('debug', 0);
		}
		$this->set(compact('league'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->League->create();
			if ($this->League->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('league', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('league', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
			}
		} else if ($this->_arg('league')) {
			// To clone a league, read the old one and remove the id
			$this->League->contain();
			$this->data = $this->League->read(null, $this->_arg('league'));
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
			unset($this->data['League']['id']);
		}

		$sports = Configure::read('options.sport');
		if (count($sports) == 1) {
			$sport = array_shift(array_keys($sports));
			$this->set('stat_types', $this->League->StatType->find('all', array(
				'conditions' => array(
					'sport' => $sport,
				)
			)));
		} else {
			// TODO: Limit by sport, presumably with JavaScript later on
		}
		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('league');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->League->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('league', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('league', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->League->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->League->contain(array('StatType'));
			$this->data = $this->League->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
		}

		$this->set('stat_types', $this->League->StatType->find('all', array(
			'conditions' => array(
				'sport' => $this->data['League']['sport'],
			)
		)));
		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->_addLeagueMenuItems ($this->League->data);
	}

	function delete() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->League->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this league, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->League->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('League', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('League', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function cron() {
		$this->layout = 'bare';
		if (!ini_get('safe_mode')) { 
			set_time_limit(0);
		}

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		// Find any leagues that are currently open, and possibly recalculate ratings
		$leagues = $this->League->find('all', array(
				'conditions' => array(
					'League.is_open' => true,
				),
				'contain' => array(
					'Division' => array(
						'Team',
					),
				),
				'order' => 'League.open',
		));

		foreach ($leagues as $key => $league) {
			AppModel::_reindexInner($league['Division'], 'Team', 'id');

			// Find all games played by teams that are in this league
			$games = $this->League->readFinalizedGames($league);
			foreach ($league['Division'] as $dkey => $division) {
				$ratings_obj = $this->_getComponent ('Ratings', $division['rating_calculator'], $this);
				$leagues[$key]['Division'][$dkey]['updates'] = $ratings_obj->recalculateRatings($league, $division, $games, true);
			}
		}

		$this->set(compact('leagues'));

		// Update the is_open status of any divisions that are about to open or have recently closed
		$to_close = $this->League->Division->find('all', array(
				'conditions' => array(
					'Division.is_open' => true,
					'OR' => array(
						'Division.open > DATE_ADD(NOW(), INTERVAL 21 DAY)',
						'Division.close < DATE_ADD(NOW(), INTERVAL -7 DAY)',
					),
				),
				'contain' => array('League'),
				'order' => 'Division.open',
		));
		$to_open = $this->League->Division->find('all', array(
				'conditions' => array(
					'Division.is_open' => 0,
					'Division.open < DATE_ADD(NOW(), INTERVAL 21 DAY)',
					'Division.close > DATE_ADD(NOW(), INTERVAL -7 DAY)',
				),
				'contain' => array('League'),
				'order' => 'Division.open',
		));

		$this->set(compact('to_close', 'to_open'));

		if (!empty($to_close)) {
			$this->League->Division->updateAll (array('Division.is_open' => 0), array('Division.id' => Set::extract('/Division/id', $to_close)));
		}
		if (!empty($to_open)) {
			$this->League->Division->updateAll (array('Division.is_open' => true), array('Division.id' => Set::extract('/Division/id', $to_open)));
		}

		// Update any league open and close dates that have changed because of divisions
		// being added or edited and then the is_open status
		$open = $this->League->Division->find('all', array(
				'conditions' => array('OR' => array(
					'Division.is_open' => true,
					'Division.open > NOW()',
				)),
				'contain' => false,
				'order' => 'Division.open',
		));

		$leagues = array();
		foreach (array_merge($to_open, $open) as $division) {
			if (!array_key_exists($division['Division']['league_id'], $leagues)) {
				$leagues[$division['Division']['league_id']] = array(
					'id' => $division['Division']['league_id'],
					'open' => $division['Division']['open'],
					'close' => $division['Division']['close'],
				);
			} else {
				$leagues[$division['Division']['league_id']]['open'] = min($leagues[$division['Division']['league_id']]['open'], $division['Division']['open']);
				$leagues[$division['Division']['league_id']]['close'] = max($leagues[$division['Division']['league_id']]['close'], $division['Division']['close']);
			}
		}
		$this->League->saveAll ($leagues);

		$to_close = $this->League->find('all', array(
				'conditions' => array(
					'League.is_open' => true,
					'OR' => array(
						'League.open > DATE_ADD(NOW(), INTERVAL 21 DAY)',
						'League.close < DATE_ADD(NOW(), INTERVAL -7 DAY)',
					),
				),
				'contain' => false,
		));
		$to_open = $this->League->find('all', array(
				'conditions' => array(
					'League.is_open' => 0,
					'League.open < DATE_ADD(NOW(), INTERVAL 21 DAY)',
					'League.close > DATE_ADD(NOW(), INTERVAL -7 DAY)',
				),
				'contain' => false,
		));

		if (!empty($to_close)) {
			$this->League->updateAll (array('League.is_open' => 0), array('League.id' => Set::extract('/League/id', $to_close)));
		}
		if (!empty($to_open)) {
			$this->League->updateAll (array('League.is_open' => true), array('League.id' => Set::extract('/League/id', $to_open)));
		}

		$this->Lock->unlock();
	}
}
?>
