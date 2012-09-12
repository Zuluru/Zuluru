<?php
class LeaguesController extends AppController {

	var $name = 'Leagues';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	function publicActions() {
		return array('cron', 'index', 'view');
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

		$divisions = $this->League->Division->find('all', array(
			'conditions' => $conditions,
			'contain' => array('League', 'Day'),
		));
		$this->League->Division->addPlayoffs($divisions);
		usort ($divisions, array('League', 'compareLeagueAndDivision'));

		// Find any newly created leagues with no divisions, for administrators
		if ($this->is_admin) {
			$leagues = $this->League->find('all', array(
				'conditions' => array('open' => '0000-00-00'),
				'contain' => false,
			));
		}

		$this->set(compact('divisions', 'leagues', 'sport'));

		$this->League->Division->contain();
		$this->set('years', $this->League->Division->find('all', array(
			'fields' => 'DISTINCT YEAR(Division.open) AS year',
			'conditions' => array('YEAR(Division.open) !=' => 0),
			'order' => 'Division.open',
		)));
	}

	function summary() {
		$divisions = $this->League->Division->find('all', array(
			'conditions' => array(
				'OR' => array(
					'League.is_open' => true,
					'League.open > CURDATE()',
				),
			),
			'contain' => array('League', 'Day'),
		));
		usort ($divisions, array('League', 'compareLeagueAndDivision'));
		$this->set(compact('divisions'));
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
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		Configure::load("sport/{$league['League']['sport']}");

		$this->set(compact ('league'));
		$divisions = $this->Session->read('Zuluru.DivisionIDs');
		if (!empty($divisions)) {
			$coordinated_divisions = array_intersect(Set::extract('/Division/id', $league), $divisions);
		} else {
			$coordinated_divisions = null;
		}
		$this->set('is_coordinator', !empty($coordinated_divisions));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function add() {
		if (!empty($this->data)) {
			$this->League->create();
			if ($this->League->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('league', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('league', true)), 'default', array('class' => 'warning'));
			}
		} else if ($this->_arg('league')) {
			// To clone a league, read the old one and remove the id
			$this->League->contain();
			$this->data = $this->League->read(null, $this->_arg('league'));
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			unset($this->data['League']['id']);
		}

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
			}
		}
		if (empty($this->data)) {
			$this->League->contain ();
			$this->data = $this->League->read(null, $id);
		}

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

		$this->League->Division->updateAll (array('Division.is_open' => 0), array('Division.id' => Set::extract('/Division/id', $to_close)));
		$this->League->Division->updateAll (array('Division.is_open' => true), array('Division.id' => Set::extract('/Division/id', $to_open)));

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

		$this->League->updateAll (array('League.is_open' => 0), array('League.id' => Set::extract('/League/id', $to_close)));
		$this->League->updateAll (array('League.is_open' => true), array('League.id' => Set::extract('/League/id', $to_open)));

		$this->Lock->unlock();
	}
}
?>
