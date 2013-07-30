<?php
class DivisionsController extends AppController {

	var $name = 'Divisions';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock', 'CanRegister');

	function publicActions() {
		$actions = array('view', 'schedule', 'standings', 'tooltip');
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}
		return $actions;
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'scores',
				'stats',
		)))
		{
			return true;
		}

		// Managers and coordinators can perform these operations
		if (in_array ($this->params['action'], array(
				'scheduling_fields',
		)))
		{
			if ($this->Session->read('Zuluru.DivisionIDs') || $this->is_manager) {
				return true;
			}
		}

		// People can perform these operations on divisions they coordinate
		if (in_array ($this->params['action'], array(
				'edit',
				'add_teams',
				'approve_scores',
				'fields',
				'slots',
				'status',
				'allstars',
				'emails',
				'spirit',
				'ratings',
				'seeds',
				'initialize_ratings',
				'initialize_dependencies',
				'delete_stage',
		)))
		{
			// If a division id is specified, check if we're a coordinator of that division
			$division = $this->_arg('division');
			if ($division && in_array ($division, $this->Session->read('Zuluru.DivisionIDs'))) {
				return true;
			}
		}

		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'select',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if ($affiliate && in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
					return true;
				}
			}

			if (in_array ($this->params['action'], array(
					'add',
			)))
			{
				// If a league id is specified, check if we're a manager of that league's affiliate
				$league = $this->_arg('league');
				if ($league) {
					if (in_array($this->Division->League->affiliate($league), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			if (in_array ($this->params['action'], array(
					'edit',
					'add_coordinator',
					'delete',
					'add_teams',
					'approve_scores',
					'fields',
					'slots',
					'status',
					'allstars',
					'emails',
					'spirit',
					'ratings',
					'seeds',
					'initialize_ratings',
					'initialize_dependencies',
					'delete_stage',
			)))
			{
				// If a division id is specified, check if we're a manager of that division's affiliate
				$division = $this->_arg('division');
				if ($division) {
					if (in_array($this->Division->affiliate($division), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function view() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain(array (
			'Person',
			'Day' => array('order' => 'day_id'),
			'Team' => array ('Person', 'Franchise'),
			'League',
			'Event' => 'EventType',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		$this->Division->addPlayoffs($division);
		Configure::load("sport/{$division['League']['sport']}");

		// If there's anyone without seed information, we get game data, sort and save the seeds
		$unseeded = Set::extract('/Team[seed=0]', $division);
		if (!empty($unseeded)) {
			// Find all games played by teams that are currently in this division,
			// or tournament games for this division
			$teams = Set::extract ('/Team/id', $division);
			$division['Game'] = $this->Division->Game->find('all', array(
					'conditions' => array(
						array('OR' => array(
							'Game.home_team' => $teams,
							'Game.away_team' => $teams,
							'AND' => array(
								'Game.division_id' => $id,
								'Game.type !=' => SEASON_GAME,
							),
						)),
						array('OR' => array(
							'Game.home_dependency_type !=' => 'copy',
							'Game.home_dependency_type' => null,
						)),
					),
					'contain' => array(
						'GameSlot',
						'HomePoolTeam' => 'Pool',
						'AwayPoolTeam' => 'Pool',
						'SpiritEntry',
					),
			));
		}

		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
		$league_obj->sort($division, $spirit_obj, false);

		if (!empty($unseeded)) {
			$seed = 0;
			foreach ($division['Team'] as $key => $team) {
				$this->Division->Team->id = $team['id'];
				$this->Division->Team->saveField('seed', ++$seed);
				$division['Team'][$key]['seed'] = $seed;
			}
		}

		if ($division['Division']['is_playoff']) {
			foreach ($division['Team'] as $key => $team) {
				$affiliate_id = $this->_getAffiliateId($division['Division'], $team);
				if ($affiliate_id !== null) {
					$this->Division->Team->contain('Division');
					$affiliate = $this->Division->Team->read(null, $affiliate_id);
					$division['Team'][$key]['affiliate_division'] = $affiliate['Division']['name'];
				}
			}
		}

		// Eliminate any events that cannot be registered for
		$my_id = $this->Auth->user('id');
		if ($my_id) {
			foreach ($division['Event'] as $key => $event) {
				$test = $this->CanRegister->test ($my_id, array('Event' => $event), false, false);
				if (!$test['allowed']) {
					unset ($division['Event'][$key]);
				}
			}
		}

		if (!empty($this->params['requested'])) {
			return array($division, $league_obj);
		}

		$this->set(compact ('division', 'league_obj'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function tooltip() {
		$id = $this->_arg('division');
		if (!$id) {
			return;
		}
		$this->Division->contain(array (
			'Person',
			'Day' => array('order' => 'day_id'),
			'Team',
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			return;
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");
		$this->set(compact('division'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function stats() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$contain = array(
			'League' => array('StatType' => array('conditions' => array('StatType.type' => Configure::read('stat_types.team')))),
			'Day',
			'Team',
		);
		$this->Division->contain($contain);

		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if (!League::hasStats($division['League'])) {
			$this->Session->setFlash(__('This league does not have stat tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'division' => $id));
		}

		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");
		$sport_obj = $this->_getComponent ('Sport', $division['League']['sport'], $this);

		// Hopefully, everything we need is already cached
		$cache_file = CACHE . 'queries' . DS . 'division_stats_' . intval($id) . '.data';
		if (file_exists($cache_file) && !Configure::read('debug')) {
			$stats = unserialize(file_get_contents($cache_file));
		}
		if (!empty($stats)) {
			$division += $stats;
		} else {
			// Calculate some stats.
			$teams = Set::extract('/Team/id', $division);
			$stats = $this->Division->Team->Stat->find('all', array(
					'conditions' => array(
						'team_id' => $teams,
					),
			));

			$division['Person'] = $this->Division->Team->TeamsPerson->find('all', array(
					'contain' => array('Person', 'Team'),
					'conditions' => array(
						'TeamsPerson.team_id' => $teams,
						'TeamsPerson.role' => Configure::read('extended_playing_roster_roles'),
					),
			));
			usort($division['Person'], array('Person', 'comparePerson'));
			AppModel::_reindexOuter($division['Person'], 'Person', 'id');

			foreach ($division['League']['StatType'] as $stat_type) {
				switch ($stat_type['type']) {
					case 'season_total':
						$sport_obj->_season_total($stat_type, $stats);
						break;
					case 'season_avg':
						$sport_obj->_season_avg($stat_type, $stats);
						break;
					case 'season_calc':
						$func = "{$stat_type['handler']}_season";
						if (method_exists($sport_obj, $func)) {
							$sport_obj->$func($stat_type, $stats);
						} else {
							trigger_error("Season stat handler {$stat_type['handler']} was not found in the {$stat_type['sport']} component!", E_USER_ERROR);
						}
						break;
				}
			}

			if (!empty($stats['Calculated'])) {
				$division['Calculated'] = $stats['Calculated'];
			} else {
				$division['Calculated'] = array();
			}

			file_put_contents($cache_file, serialize(array(
					'Person' => $division['Person'],
					'Calculated' => $division['Calculated'],
			)));
		}

		$this->set(compact('division', 'sport_obj'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		if ($this->params['url']['ext'] == 'csv') {
			$this->set('download_file_name', "Stats - {$division['Division']['name']}");
			Configure::write ('debug', 0);
		}
	}

	function add() {
		$league_id = $this->_arg('league');
		if (!empty($this->data['Division']['league_id'])) {
			$league_id = $this->data['Division']['league_id'];
		}
		if (!$league_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$affiliate_id = $this->Division->League->affiliate($league_id);
		$this->Configuration->loadAffiliate($affiliate_id);
		$sport = $this->Division->League->field('sport', array('id' => $league_id));
		Configure::load("sport/$sport");

		if (!empty($this->data)) {
			$this->Division->create();

			$this->Division->set($this->data);

			// Does the division need to be opened immediately?
			$open_division = false;
			if ($this->Division->data['Division']['open'] < date('Y-m-d', time() + 21 * DAY)) {
				$this->Division->set(array('is_open' => true));
				$open_division = true;
			}

			if ($this->Division->save()) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('division', true)), 'default', array('class' => 'success'));

				// Does the league need to be opened?
				if ($open_division) {
					$this->Division->League->updateAll(array('League.is_open' => true), array('League.id' => $this->data['Division']['league_id']));
				}

				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('division', true)), 'default', array('class' => 'warning'));
			}
		} else if ($this->_arg('division')) {
			// To clone a division, read the old one and remove the id
			$this->Division->contain('Day');
			$this->data = $this->Division->read(null, $this->_arg('division'));
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			unset($this->data['Division']['id']);
		}

		if ($this->_arg('league')) {
			$this->data['Division']['league_id'] = $league_id;
		}

		$leagues = $this->Division->League->find('all', array(
				'conditions' => array(
					'OR' => array(
						'League.is_open' => true,
						'League.open > NOW()',
						'League.open' => '0000-00-00',
					),
					'League.sport' => $sport,
					'League.affiliate_id' => $affiliate_id,
				),
				'contain' => array('Affiliate'),
		));
		usort ($leagues, array('League', 'compareLeagueAndDivision'));
		$this->set('leagues', Set::combine($leagues, '{n}.League.id', '{n}.League.full_name'));

		$this->set('days', $this->Division->Day->find('list'));
		if (isset($this->data['Division']['schedule_type'])) {
			$this->set('league_obj', $this->_getComponent ('LeagueType', $this->data['Division']['schedule_type'], $this));
		}
		$this->set('is_coordinator', false);
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('division');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$league_id = $this->Division->field('league_id', array('id' => $id));
		$this->Division->League->contain(array());
		$league = $this->Division->League->read(null, $league_id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");

		if (!empty($this->data)) {
			if ($this->Division->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('division', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('division', true)), 'default', array('class' => 'warning'));
			}
		}

		if (empty($this->data)) {
			$this->Division->contain(array (
				'Day' => array('order' => 'day_id'),
			));
			$this->data = $this->Division->read(null, $id);
		}
		$this->data['League'] = $league['League'];

		$this->set('days', $this->Division->Day->find('list'));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->data['Division']['schedule_type'], $this));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		$this->_addDivisionMenuItems ($this->data['Division'], $this->data['League']);
	}

	function scheduling_fields() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->params['url']['data']['Division']['schedule_type'], $this));
	}

	function add_coordinator() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain('Person', 'League');
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");

		$this->set(compact('division'));

		$person_id = $this->_arg('person');
		if ($person_id != null) {
			$this->Division->Person->contain(array('Division' => array('conditions' => array('Division.id' => $id))));
			$person = $this->Division->Person->read(null, $person_id);
			if (!empty ($person['Division'])) {
				$this->Session->setFlash(__("{$person['Person']['full_name']} is already a coordinator of this division", true), 'default', array('class' => 'info'));
			} else {
				$division['Person'] = Set::extract ('/Person/id', $division);
				$division['Person'][] = $person['Person']['id'];
				// TODO: If we add more coordinator types, we need to save the position here
				if ($this->Division->saveAll ($division)) {
					$this->Session->setFlash(__("Added {$person['Person']['full_name']} as coordinator", true), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'view', 'division' => $id));
				} else {
					$this->Session->setFlash(__("Failed to add {$person['Person']['full_name']} as coordinator", true), 'default', array('class' => 'warning'));
					$this->redirect(array('action' => 'add_coordinator', 'division' => $id));
				}
			}
		}

		$params = $url = $this->_extractSearchParams();
		unset ($params['division']);
		unset ($params['person']);
		$this->_handlePersonSearch($params, $url, $this->Division->Person,
				array('Group.name' => array('Volunteer', 'Manager', 'Administrator')));
	}

	function remove_coordinator() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'division' => $id));
		}

		$join = ClassRegistry::init('DivisionsPerson');
		if ($join->deleteAll (array('division_id' => $id, 'person_id' => $person_id))) {
			$this->Session->setFlash(__('Successfully removed coordinator', true), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(__('Failed to remove coordinator!', true), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'division' => $id));
	}

	function add_teams() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if (!empty($this->data)) {
			$teams = $this->data['Team'];
			$default = $teams[0];
			unset($teams[0]);
			foreach ($teams as $key => $team) {
				if (!empty($team['name'])) {
					$teams[$key] += $default;
				} else {
					unset($teams[$key]);
				}
			}
			if ($this->Division->Team->saveAll($teams)) {
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('teams', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'division' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('teams', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Division->affiliate($id));

				// Adjust validation errors; some might really refer to the shared "0" key
				foreach ($this->Division->Team->validationErrors as $errors) {
					foreach ($errors as $field => $error) {
						if (array_key_exists($field, $default)) {
							$this->Division->Team->validationErrors[0][$field] = $error;
						}
					}
				}
			}
		}

		if (empty($this->data)) {
			$this->Division->contain('Person', 'League');
			$this->data = $this->Division->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
		}
	}

	function ratings() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if (!empty($this->data)) {
			if ($this->Division->Team->saveAll($this->data['Team'])) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('division', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'division' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('division', true)), 'default', array('class' => 'warning'));
			}
		}

		$this->Division->contain(array (
			'Day' => array('order' => 'day_id'),
			'Team' => array(
				'Person',
				'order' => array('rating' => 'DESC'),
			),
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$this->set(compact ('division'));
		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function seeds() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if (!empty($this->data)) {
			$seeds = Set::extract('/Team/initial_seed', $this->data);
			if (count($this->data['Team']) != count(array_unique($seeds))) {
				$this->Session->setFlash(__('Each team must have a unique initial seed.', true), 'default', array('class' => 'warning'));
			} else if (min($seeds) != 1 || count($this->data['Team']) != max($seeds)) {
				$this->Session->setFlash(__('Initial seeds must start at 1 and not skip any.', true), 'default', array('class' => 'warning'));
			} else {
				// Reset current seeds; they'll be recalculated as required
				foreach (array_keys($this->data['Team']) as $key) {
					$this->data['Team'][$key]['seed'] = 0;
				}
				if ($this->Division->Team->saveAll($this->data['Team'])) {
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('division', true)), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'view', 'division' => $id));
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('division', true)), 'default', array('class' => 'warning'));
				}
			}
		}

		$this->Division->contain(array (
			'Day' => array('order' => 'day_id'),
			'Team' => array(
				'Person',
				'order' => array('initial_seed'),
			),
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$this->set(compact ('division'));
		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function delete() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$dependencies = $this->Division->dependencies($id, array('Day'));
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this division, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		if ($this->Division->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Division', true)), 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Division', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
	}

	function schedule() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		// Hopefully, everything we need is already cached
		$cache_file = CACHE . 'queries' . DS . 'schedule_' . intval($id) . '.data';
		if (empty($this->data) && file_exists($cache_file)) {
			$division = unserialize(file_get_contents($cache_file));
		}
		if (empty($division)) {
			$this->Division->contain(array (
				'Day' => array('order' => 'day_id'),
				'Team',
				'League',
				'Game' => array(
					'conditions' => array(
						'OR' => array(
							'Game.home_dependency_type !=' => 'copy',
							'Game.home_dependency_type' => null,
						),
					),
					'GameSlot' => array('Field' => 'Facility'),
					'ScoreEntry',
					'HomeTeam',
					'HomePoolTeam' => 'DependencyPool',
					'AwayTeam',
					'AwayPoolTeam' => 'DependencyPool',
				),
			));
			$division = $this->Division->read(null, $id);
			if (!$division) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			if (empty ($division['Game'])) {
				$this->Session->setFlash(__('This division has no games scheduled yet.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			// Sort games by date, time and field
			usort ($division['Game'], array ('Game', 'compareDateAndField'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");

		$is_coordinator = in_array($id, $this->Session->read('Zuluru.DivisionIDs'));
		$is_manager = $this->is_manager && in_array($division['League']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
		if ($this->is_admin || $is_manager || $is_coordinator) {
			$edit_date = $this->_arg('edit_date');
			if (!empty ($this->data)) {
				$edit_date = $this->data['Game']['edit_date'];
				unset ($this->data['Game']['edit_date']);
			}
		} else {
			$edit_date = null;
		}

		if ($edit_date) {
			$tournament_games = Set::extract ('/Game[type!=' . SEASON_GAME . "]/GameSlot[game_date=$edit_date]", $division);
			$is_tournament = !empty($tournament_games);
			$game_slots = $this->Division->DivisionGameslotAvailability->GameSlot->getAvailable($id, $edit_date, $is_tournament);
		} else {
			$is_tournament = false;
		}

		// Save posted data
		if (!empty ($this->data) && ($this->is_admin || $is_manager || $is_coordinator)) {
			if ($this->_validateAndSaveSchedule($game_slots)) {
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				$cache_file = CACHE . 'queries' . DS . 'division_' . intval($id) . '.data';
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				$this->redirect (array('action' => 'schedule', 'division' => $id));
			}
		}

		file_put_contents($cache_file, serialize($division));

		$this->set(compact ('id', 'division', 'edit_date', 'game_slots', 'is_coordinator', 'is_tournament'));

		$this->_addDivisionMenuItems ($division['Division'], $division['League']);
	}

	function _validateAndSaveSchedule($available_slots) {
		$publish = $this->data['Game']['publish'];
		unset ($this->data['Game']['publish']);
		if (array_key_exists('double_header', $this->data['Game'])) {
			$allow_double_header = $this->data['Game']['double_header'];
			unset ($this->data['Game']['double_header']);
		} else {
			$allow_double_header = false;
		}

		$games = count($this->data['Game']);
		// TODO: Remove workaround for Set::extract bug
		$this->data['Game'] = array_values($this->data['Game']);
		$slots = Set::extract ('/Game/GameSlot/id', $this->data);
		if (in_array ('', $slots)) {
			$this->Session->setFlash(__('You cannot choose the "---" as the game time/place!', true), 'default', array('class' => 'info'));
			return false;
		}

		$slot_counts = array_count_values ($slots);
		foreach ($slot_counts as $slot_id => $count) {
			if ($count > 1) {
				$this->Division->Game->GameSlot->contain(array(
						'Field' => 'Facility',
				));
				$slot = $this->Division->Game->GameSlot->read(null, $slot_id);
				$slot_field = $slot['Field']['long_name'];
				$slot_time = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
				$this->Session->setFlash(sprintf (__('Game slot at %s on %s was selected more than once!', true), $slot_field, $slot_time), 'default', array('class' => 'info'));
				return false;
			}
		}

		$teams = array_merge (
				Set::extract ('/Game/home_team', $this->data),
				Set::extract ('/Game/away_team', $this->data)
		);
		if (in_array ('', $teams)) {
			$this->Session->setFlash(__('You cannot choose the "---" as the team!', true), 'default', array('class' => 'info'));
			return false;
		}

		$team_counts = array_count_values ($teams);
		foreach ($team_counts as $team_id => $count) {
			if ($count > 1) {
				$this->Division->Team->contain();
				$team = $this->Division->Team->read(null, $team_id);

				if ($allow_double_header) {
					// Check that the double-header doesn't cause conflicts; must be at the same facility, but different times
					$team_slot_ids = array_merge(
						Set::extract ("/Game[home_team=$team_id]/GameSlot/id", $this->data),
						Set::extract ("/Game[away_team=$team_id]/GameSlot/id", $this->data)
					);
					if (count ($team_slot_ids) != count (array_unique ($team_slot_ids))) {
						$this->Session->setFlash(sprintf (__('Team %s was scheduled twice in the same time slot!', true), $team['Team']['name']), 'default', array('class' => 'info'));
						return false;
					}

					$this->Division->Game->GameSlot->contain(array(
							'Field',
					));
					$team_slots = $this->Division->Game->GameSlot->find('all', array('conditions' => array(
							'GameSlot.id' => $team_slot_ids,
					)));
					foreach ($team_slots as $key1 => $slot1) {
						foreach ($team_slots as $key2 => $slot2) {
							if ($key1 != $key2) {
								if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] &&
									$slot1['GameSlot']['game_start'] >= $slot2['GameSlot']['game_start'] &&
									$slot1['GameSlot']['game_start'] < $slot2['GameSlot']['display_game_end'])
								{
									$this->Session->setFlash(sprintf (__('Team %s was scheduled in overlapping time slots!', true), $team['Team']['name']), 'default', array('class' => 'info'));
									return false;
								}
								if ($slot1['Field']['facility_id'] != $slot2['Field']['facility_id']) {
									$this->Session->setFlash(sprintf (__('Team %s was scheduled on %s at different facilities!', true), $team['Team']['name'], Configure::read('ui.fields')), 'default', array('class' => 'info'));
									return false;
								}
							}
						}
					}
				} else {
					$this->Session->setFlash(sprintf (__('Team %s was selected more than once!', true), $team['Team']['name']), 'default', array('class' => 'info'));
					return false;
				}
			}
		}

		if (!$this->Lock->lock ('scheduling', $this->Division->affiliate($this->_arg('division')), 'schedule creation or edit')) {
			return false;
		}
		if (!$this->Division->Game->_saveGames($this->data['Game'], $publish)) {
			$this->Lock->unlock();
			return false;
		}
		$this->Lock->unlock();

		$unused_slots = array_diff (Set::extract ('/GameSlot/id', $available_slots), $slots);
		if ($this->Division->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $unused_slots))) {
			$this->Session->setFlash(__('Schedule changes saved!', true), 'default', array('class' => 'success'));
			return true;
		} else {
			$this->Session->setFlash(__('Saved schedule changes, but failed to clear unused slots!', true), 'default', array('class' => 'warning'));
			return false;
		}
	}

	function standings() {
		$id = $this->_arg('division');
		$teamid = $this->_arg('team');
		$showall = $this->_arg('full');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		// Hopefully, everything we need is already cached
		$cache_file = CACHE . 'queries' . DS . 'division_' . intval($id) . '.data';
		if (file_exists($cache_file)) {
			$division = unserialize(file_get_contents($cache_file));
		}
		if (!empty($division)) {
			$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
			Configure::load("sport/{$division['League']['sport']}");
			$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
			$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
		} else {
			$this->Division->contain(array (
				'Day' => array('order' => 'day_id'),
				'Team',
				'League',
			));
			$division = $this->Division->read(null, $id);
			if (!$division) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
			Configure::load("sport/{$division['League']['sport']}");

			// Find all games played by teams that are currently in this division,
			// or tournament games for this division
			$teams = Set::extract ('/Team/id', $division);
			if (empty($teams)) {
				$conditions = array(
					'Game.division_id' => $id,
					'Game.type !=' => SEASON_GAME,
				);
			} else {
				$conditions = array('OR' => array(
					'Game.home_team' => $teams,
					'Game.away_team' => $teams,
					'AND' => array(
						'Game.division_id' => $id,
						'Game.type !=' => SEASON_GAME,
					),
				));
			}
			$conditions['NOT'] = array('Game.status' => Configure::read('unplayed_status'));

			$division['Game'] = $this->Division->Game->find('all', array(
					'conditions' => $conditions,
					'contain' => array(
						'GameSlot',
						'HomePoolTeam' => array('Pool', 'DependencyPool'),
						'AwayPoolTeam' => array('Pool', 'DependencyPool'),
						'ScoreEntry',
						'SpiritEntry',
					),
			));

			if (empty ($division['Game'])) {
				$this->Session->setFlash(__('Cannot generate standings for a division with no schedule.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			// Sort games by date, time and field
			usort ($division['Game'], array ('Game', 'compareDateAndField'));
			Game::_adjustEntryIndices($division['Game']);

			$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
			$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
			$league_obj->sort($division, $spirit_obj, false);

			// If there's anyone without seed information, save the seeds
			$unseeded = Set::extract('/Team[seed=0]', $division);
			if (!empty($unseeded)) {
				$seed = 0;
				foreach ($division['Team'] as $key => $team) {
					$this->Division->Team->id = $team['id'];
					$this->Division->Team->saveField('seed', ++$seed);
					$division['Team'][$key]['seed'] = $seed;
				}
			}

			file_put_contents($cache_file, serialize($division));
		}

		// If we're asking for "team" standings, only show the 5 teams above and 5 teams below this team.
		// Don't bother if there are 24 teams or less (24 is probably the largest fall division size).
		// If $showall is set, don't remove teams.
		if (!$showall && $teamid != null && count($division['Team']) > 24) {
			$index_of_this_team = false;
			foreach ($division['Team'] as $i => $team) {
				if ($team['id'] == $teamid) {
					$index_of_this_team = $i;
					break;
				}
			}

			$first = $index_of_this_team - 5;
			if ($first <= 0) {
				$first = 0;
			} else {
				$more_before = $first; // need to add this to the first seed
			}
			$last = $index_of_this_team + 5;
			if ($last < count($division['Team']) - 1) {
				$more_after = true; // we never need to know how many after
			}

			$show_teams = array_slice ($division['Team'], $first, $last + 1 - $first);
		} else {
			$show_teams = $division['Team'];
		}
		$this->set(compact ('division', 'league_obj', 'spirit_obj', 'teamid', 'show_teams', 'more_before', 'more_after'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		$this->_addDivisionMenuItems ($division['Division'], $division['League']);
	}

	function scores() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain(array (
			'Day' => array('order' => 'day_id'),
			'Team',
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");

		// Find all games played by teams that are currently in this division,
		// or tournament games for this division
		$teams = Set::extract ('/Team/id', $division);
		$this->Division->Game->contain (array(
				'HomeTeam',
				'HomePoolTeam' => 'Pool',
				'AwayTeam',
				'AwayPoolTeam' => 'Pool',
				'GameSlot' => array('Field' => 'Facility'),
		));
		$division['Game'] = $this->Division->Game->find('all', array(
				'conditions' => array(
					array('OR' => array(
						'Game.home_team' => $teams,
						'Game.away_team' => $teams,
						'AND' => array(
							'Game.division_id' => $id,
							'Game.type !=' => SEASON_GAME,
						),
					)),
					array('OR' => array(
						'Game.home_dependency_type !=' => 'copy',
						'Game.home_dependency_type' => null,
					)),
					'NOT' => array('Game.status' => Configure::read('unplayed_status')),
				),
		));
		if (empty ($division['Game'])) {
			$this->Session->setFlash(__('This division has no games scheduled yet.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		// Sort games by date, time and field
		usort ($division['Game'], array ('Game', 'compareDateAndField'));
		Game::_adjustEntryIndices($division['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
		$league_obj->sort($division, $spirit_obj, false);

		// Move the teams into an array indexed by team id, for easier use in the view
		$teams = array();
		foreach ($division['Team'] as $team) {
			$teams[$team['id']] = $team;
		}
		$division['Team'] = $teams;

		$this->set(compact ('division'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function fields() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$conditions = array(
			'OR' => array(
				'Game.home_dependency_type !=' => 'copy',
				'Game.home_dependency_type' => null,
			),
			'NOT' => array('Game.status' => Configure::read('unplayed_status')),
		);

		if ($this->_arg('published')) {
			$conditions['Game.published'] = true;
			$this->set('published', true);
		}

		$this->Division->contain(array (
			'Team' => array(
				'Field' => 'Facility',
				'Region',
			),
			'League',
			'Game' => array(
				'conditions' => $conditions,
				'GameSlot' => array('Field' => 'Facility'),
				'HomeTeam',
				'HomePoolTeam' => 'Pool',
				'AwayTeam',
				'AwayPoolTeam' => 'Pool',
			),
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		if (empty ($division['Game'])) {
			$this->Session->setFlash(__('This division has no games scheduled yet.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");
		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
		$league_obj->sort($division, $spirit_obj, false);

		// Gather all possible facility/time slot combinations this division can use
		$join = array(
			array(
				'table' => "{$this->Division->tablePrefix}game_slots",
				'alias' => 'GameSlot',
				'type' => 'INNER',
				'foreignKey' => false,
				'conditions' => 'DivisionGameslotAvailability.game_slot_id = GameSlot.id',
			),
			array(
				'table' => "{$this->Division->tablePrefix}fields",
				'alias' => 'Field',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Field.id = GameSlot.field_id',
			),
			array(
				'table' => "{$this->Division->tablePrefix}facilities",
				'alias' => 'Facility',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Facility.id = Field.facility_id',
			),
			array(
				'table' => "{$this->Division->tablePrefix}regions",
				'alias' => 'Region',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Region.id = Facility.region_id',
			),
		);
		$facilities = $this->Division->DivisionGameslotAvailability->find('all', array(
			'fields' => array('DISTINCT Facility.id', 'Facility.code', 'Facility.name', 'Region.name',
					'GameSlot.game_start'),
			'conditions' => array('DivisionGameslotAvailability.division_id' => $id),
			'contain' => false,
			'order' => 'Region.id, Facility.code, GameSlot.game_start',
			'joins' => $join,
		));

		// Re-index the facilities array
		foreach ($facilities as $key => $facility) {
			$new_key = "{$facility['Facility']['code']} {$facility['GameSlot']['game_start']}";
			$facilities[$new_key] = $facilities[$key];
			unset($facilities[$key]);
		}

		$this->set(compact ('division', 'league_obj', 'facilities'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function _compareRegionAndCodeAndStart($a, $b) {
		if ($a['Region']['name'] < $b['Region']['name']) {
			return -1;
		} else if ($a['Region']['name'] > $b['Region']['name']) {
			return 1;
		} else if ($a['Facility']['code'] < $b['Facility']['code']) {
			return -1;
		} else if ($a['Facility']['code'] > $b['Facility']['code']) {
			return 1;
		} else if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		} else if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		}
		return 0;
	}

	function slots() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain('League');
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		Configure::load("sport/{$division['League']['sport']}");

		$this->Division->DivisionGameslotAvailability->GameSlot->contain();
		$join = array( array(
				'table' => "{$this->Division->tablePrefix}division_gameslot_availabilities",
				'alias' => 'DivisionGameslotAvailability',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'DivisionGameslotAvailability.game_slot_id = GameSlot.id',
		));
		$dates = $this->Division->DivisionGameslotAvailability->GameSlot->find('all', array(
			'fields' => array('DISTINCT GameSlot.game_date'),
			'conditions' => array('DivisionGameslotAvailability.division_id' => $id),
			'order' => 'GameSlot.game_date',
			'joins' => $join,
		));
		$dates = Set::extract ('/GameSlot/game_date', $dates);
		$dates = array_combine (array_values ($dates), array_values ($dates));

		$date = $this->_arg('date');
		if (!empty ($this->data) && array_key_exists ('date', $this->data)) {
			$date = $this->data['date'];
		}
		if (!empty ($date)) {
			$this->Division->DivisionGameslotAvailability->GameSlot->contain (array (
					'Game' => array(
						'conditions' => array(
							'OR' => array(
								'Game.home_dependency_type !=' => 'copy',
								'Game.home_dependency_type' => null,
							),
						),
						'Division',
						'Pool',
						'HomeTeam' => array(
							'Field' => 'Facility',
							'Region',
						),
						'HomePoolTeam' => 'DependencyPool',
						'AwayTeam' => array(
							'Field' => 'Facility',
							'Region',
						),
						'AwayPoolTeam' => 'DependencyPool',
					),
					'Field' => array(
						'Facility' => 'Region',
					),
			));
			$slots = $this->Division->DivisionGameslotAvailability->GameSlot->find('all', array(
				'conditions' => array('DivisionGameslotAvailability.division_id' => $id, 'GameSlot.game_date' => $date),
				'joins' => $join,
				'order' => array('GameSlot.game_date', 'GameSlot.game_start', 'Field.id'),
			));

			$tournament_games = Set::extract ('/Game[type!=' . SEASON_GAME . "]", $slots);
			$is_tournament = !empty($tournament_games);
		}

		$this->set(compact('division', 'dates', 'date', 'slots', 'is_tournament'));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function status() { // TODO
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

	}

	function allstars() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$min = $this->_arg('min');
		if (!$min) {
			$min = 2;
		}

		$this->Division->contain('League');
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$allstars = $this->Division->Game->Allstar->find ('all', array(
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender', 'Person.email',
					'COUNT(Allstar.game_id) AS count',
				),
				'conditions' => array(
					'Game.division_id' => $id,
				),
				'group' => "Allstar.person_id HAVING count >= $min",
				'order' => array('Person.gender' => 'DESC', 'count' => 'DESC', 'Person.last_name', 'Person.first_name'),
		));

		$this->set(compact('division', 'allstars', 'min'));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function emails() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain(array (
			'Team' => array (
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		$this->set(compact('division'));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function spirit() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain(array (
			'Team',
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		$teams = Set::extract('/Team/id', $division);
		$this->Division->Game->contain(array (
			'GameSlot',
			'SpiritEntry',
			'HomeTeam',
			'AwayTeam',
		));
		$division['Game'] = $this->Division->Game->find('all', array(
			'order' => 'Game.id',
			'conditions' => array(
				array('OR' => array(
					'home_team' => $teams,
					'away_team' => $teams,
				)),
				array('OR' => array(
					'Game.home_dependency_type !=' => 'copy',
					'Game.home_dependency_type' => null,
				)),
				'NOT' => array('Game.status' => Configure::read('unplayed_status')),
			),
		));
		if (empty ($division['Game'])) {
			$this->Session->setFlash(__('This division has no games scheduled yet.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);

		$this->set(compact('division', 'spirit_obj'));

		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);

		// This is in case we're doing CSV output
		$this->set('download_file_name', "Spirit - {$division['Division']['full_league_name']}");
	}

	function approve_scores() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain('League');
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$this->Division->Game->contain (array (
			// Get the list of captains for each team, for building the email link
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'HomePoolTeam' => 'DependencyPool',
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayPoolTeam' => 'DependencyPool',
			'GameSlot',
			'ScoreEntry',
		));
		$games = $this->Division->Game->find ('all', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'Game.approved_by' => null,
					'OR' => array(
						'GameSlot.game_date < CURDATE()',
						array(
							'GameSlot.game_date = CURDATE()',
							'GameSlot.game_end < CURTIME()',
						),
					),
				),
				'order' => array('GameSlot.game_date', 'GameSlot.game_start', 'Game.id'),
		));
		if (empty ($games)) {
			$this->Session->setFlash(__('There are currently no games to approve in this division.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		Game::_adjustEntryIndices($games);

		$this->set(compact ('division', 'games'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.DivisionIDs')));

		// TODO: Add this type of links everywhere. Maybe do it in beforeRender?
		$this->_addDivisionMenuItems ($this->Division->data['Division'], $this->Division->data['League']);
	}

	function initialize_ratings() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain (array(
			'Team' => array(
				'Franchise',
			),
			'League',
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		$this->Division->addPlayoffs($division);

		if (!$division['Division']['is_playoff']) {
			$this->Session->setFlash(__('Only playoff divisions can be initialized', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'division' => $id));
		}

		// Wrap the whole thing in a transaction, for safety.
		$transaction = new DatabaseTransaction($this->Division);

		// Initialize all teams ratings with their regular season ratings
		foreach ($division['Team'] as $key => $team) {
			$affiliate_id = $this->_getAffiliateId($division['Division'], $team);
			if ($affiliate_id === null) {
				$this->Session->setFlash($team['name'] . ' ' . __('does not have a unique affiliated team in the correct division', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'division' => $id));
			}
			$this->Division->Team->contain(array('Division' => 'League'));
			$affiliate = $this->Division->Team->read(null, $affiliate_id);
			$division['Team'][$key]['rating'] = $affiliate['Team']['rating'];

			$this->Division->Team->id = $team['id'];
			if (!$this->Division->Team->saveField('rating', $affiliate['Team']['rating']) ||
				!$this->Division->Team->saveField('initial_rating', $affiliate['Team']['rating']))
			{
				$this->Session->setFlash(__('Failed to update team rating', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'division' => $id));
			}
		}

		$this->Session->setFlash(__('Team ratings have been initialized.', true), 'default', array('class' => 'success'));
		$transaction->commit();

		$cache_file = CACHE . 'queries' . DS . 'division_' . intval($id) . '.data';
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}

		$cache_file = CACHE . 'queries' . DS . 'schedule_' . intval($id) . '.data';
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}

		$this->redirect(array('action' => 'view', 'division' => $id));
	}

	function initialize_dependencies() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$date = $this->_arg('date');
		$pool = $this->_arg('pool');

		$this->Division->contain(array(
			'Team' => array(
				'Franchise',
			),
			'League',
			// We may need all of the games, as some league types use game results
			// to determine sort order.
			'Game' => array(
				'GameSlot',
				'HomePoolTeam' => array('Pool', 'DependencyPool'),
				'AwayPoolTeam' => array('Pool', 'DependencyPool'),
				'conditions' => array(
					'NOT' => array('Game.status' => Configure::read('unplayed_status')),
				),
				'order' => 'Game.id',	// need to ensure that "copy" games come after the ones they're copied from
			),
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$conditions = array(
				'Game.division_id' => $id,
				'Game.type !=' => SEASON_GAME,
				'Game.approved_by' => null,
				'OR' => array(
					'HomePoolTeam.dependency_type' => array('seed', 'pool', 'ordinal', 'copy'),
					'AwayPoolTeam.dependency_type' => array('seed', 'pool', 'ordinal', 'copy'),
				),
		);
		// If there are tournament pools with finalized games in them, we do not want to
		// initialize any games in those pools.
		$finalized_pools = array_unique(Set::extract('/Game[approved_by!=][pool_id!=]/pool_id', $division));
		if (!empty($finalized_pools)) {
			$conditions['NOT'] = array('Game.pool_id' => $finalized_pools);
		}

		if ($pool) {
			if (in_array($pool, $finalized_pools)) {
				$this->Session->setFlash(__('There are already games finalized in this pool. Unable to proceed.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'schedule', 'division' => $id));
			}
			$conditions['Game.pool_id'] = $pool;
		}

		$count = $this->Division->Game->find('count', array(
				'contain' => array(
					'HomePoolTeam',
					'AwayPoolTeam',
				),
				'conditions' => $conditions,
		));
		if ($count == 0) {
			$this->Session->setFlash(__('There are currently no dependencies to initialize in this division.', true), 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'schedule', 'division' => $id));
		}

		if ($division['Division']['schedule_type'] == 'tournament') {
			$seeds = Set::extract('/Team/initial_seed', $division);
			if (count($division['Team']) != count(array_unique($seeds))) {
				$this->Session->setFlash(__('Each team must have a unique initial seed.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'seeds', 'division' => $id));
			}
		}

		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$spirit_obj = $this->_getComponent ('Spirit', $division['League']['sotg_questions'], $this);
		$league_obj->sort($division, $spirit_obj, false);
		$reset = $this->_arg('reset');
		$operation = ($reset ? 'reset' : 'update');

		// Wrap the whole thing in a transaction, for safety.
		$transaction = new DatabaseTransaction($this->Division);

		// Go through all games, updating seed dependencies
		foreach ($division['Game'] as $game) {
			if ($date && ($date != $game['GameSlot']['game_date'])) {
				continue;
			}
			if ($pool && ($pool != $game['pool_id'])) {
				continue;
			}
			if (in_array($game['pool_id'], $finalized_pools)) {
				continue;
			}
			if (Game::_is_finalized($game)) {
				continue;
			}

			$this->Division->Game->id = $game['id'];
			foreach (array('Home', 'Away') as $type) {
				if (!empty($game["{$type}PoolTeam"])) {
					$field = low($type) . '_team';
					$this->Division->Game->HomePoolTeam->id = $game["{$type}PoolTeam"]['id'];

					if ($reset) {
						$team_id = null;
					} else {
						switch ($game["{$type}PoolTeam"]['dependency_type']) {
							case 'seed':
								$seed = $game["{$type}PoolTeam"]['dependency_id'];
								if ($seed > count($division['Team'])) {
									$this->Session->setFlash(__('Not enough teams in the division to fulfill all scheduled seeds', true), 'default', array('class' => 'warning'));
									$this->redirect(array('action' => 'schedule', 'division' => $id));
								}
								$team_id = $division['Team'][$seed - 1]['id'];
								break;

							case 'pool':
								$stage = $game["{$type}PoolTeam"]['DependencyPool']['stage'];
								$pool_id = $game["{$type}PoolTeam"]['dependency_pool_id'];
								$seed = $game["{$type}PoolTeam"]['dependency_id'];
								$results = $division['Pool'][$stage][$pool_id]['Results'];
								usort($results, array($league_obj, 'compareTeamsResults'));
								$league_obj->detectAndResolveTies($results);
								$team_id = $results[$seed - 1]['id'];
								break;

							case 'ordinal':
								// The stage we're looking at for these results might be the
								// one before this one, or it might be two stages ago, if
								// the previous stage was crossover games.
								$stage = $game["{$type}PoolTeam"]['Pool']['stage'] - 1;
								$pool_id = array_shift(array_keys($division['Pool'][$stage]));
								if ($division['Pool'][$stage][$pool_id]['Game'][0]['HomePoolTeam']['Pool']['type'] == 'crossover') {
									-- $stage;
								}

								$ordinal = $game["{$type}PoolTeam"]['dependency_ordinal'];
								$teams = array();
								foreach ($division['Pool'][$stage] as $pool_id => $results) {
									usort($results['Team'], array($league_obj, 'compareTeamsResults'));
									$league_obj->detectAndResolveTies($results['Team']);
									$teams[] = $results['Team'][$ordinal - 1];
								}
								usort($teams, array($league_obj, 'compareTeamsResultsCrossPool'));
								$seed = $game["{$type}PoolTeam"]['dependency_id'];
								$team_id = $teams[$seed - 1]['id'];
								break;
						}
					}

					if (!$this->Division->Game->saveField($field, $team_id) ||
						!$this->Division->Game->HomePoolTeam->saveField('team_id', $team_id))
					{
						$this->Session->setFlash(sprintf(__('Failed to %s game dependency', true), __($operation, true)), 'default', array('class' => 'warning'));
						$this->redirect(array('action' => 'schedule', 'division' => $id));
					}
					$game[$field] = $team_id;
				}
			}

			// Handle any carried-forward results
			if ($game['home_dependency_type'] == 'copy') {
				if ($reset) {
					$save = array(
							'home_score' => null,
							'away_score' => null,
							'approved_by' => null,
							'status' => 'normal',
					);
				} else {
					$copy = Set::extract("/Game[home_team={$game['home_team']}][away_team={$game['away_team']}][pool_id={$game['HomePoolTeam']['dependency_pool_id']}]", $division);
					if (empty($copy)) {
						$copy = Set::extract("/Game[home_team={$game['away_team']}][away_team={$game['home_team']}][pool_id={$game['HomePoolTeam']['dependency_pool_id']}]", $division);
						$home = 'away';
						$away = 'home';
					} else {
						$home = 'home';
						$away = 'away';
					}
					if (empty($copy)) {
						$this->Session->setFlash(sprintf(__('Failed to %s game dependency', true), __('locate', true)), 'default', array('class' => 'warning'));
						$this->redirect(array('action' => 'schedule', 'division' => $id));
					}
					$copy = $copy[0]['Game'];
					$save = array(
							'home_score' => $copy["{$home}_score"],
							'away_score' => $copy["{$away}_score"],
							'approved_by' => $copy['approved_by'],
							'status' => $copy['status'],
							'updated' => $copy['updated'],
					);
				}
				if (!$this->Division->Game->save($save)) {
					$this->Session->setFlash(sprintf(__('Failed to %s game dependency', true), __($operation, true)), 'default', array('class' => 'warning'));
					$this->redirect(array('action' => 'schedule', 'division' => $id));
				}
			}
		}
		$this->Session->setFlash(sprintf(__('Dependencies have been %s.', true), __($reset ? 'reset' : 'resolved', true)),
				'default', array('class' => 'success'));
		$transaction->commit();

		$cache_file = CACHE . 'queries' . DS . 'division_' . intval($id) . '.data';
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}

		$cache_file = CACHE . 'queries' . DS . 'schedule_' . intval($id) . '.data';
		if (file_exists($cache_file)) {
			unlink($cache_file);
		}

		$this->redirect(array('action' => 'schedule', 'division' => $id));
	}

	function delete_stage() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$stage = $this->_arg('stage');
		if (!$stage) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('stage', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain(array(
			'League',
			'Pool' => array(
				'conditions' => array('Pool.stage' => $stage),
				'Game',
			),
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		if (empty($division['Pool'])) {
			$this->Session->setFlash(__('There are currently no pools to delete in this stage.', true), 'default', array('class' => 'warning'));
			$this->redirect(array('controller' => 'schedules', 'action' => 'add', 'division' => $id));
		}

		$games = Set::extract('/Game/id', $division['Pool']);
		$transaction = new DatabaseTransaction($this->Division->Pool);

		// We'll overwrite this flash message if it succeeds
		$this->Session->setFlash(sprintf(__('%s were not deleted', true), __('Pools in this stage', true)), 'default', array('class' => 'warning'));

		if ($this->Division->Pool->deleteAll(array('Pool.division_id' => $id, 'Pool.stage >=' => $stage))) {
			if (empty($games) || (
					$this->Division->Pool->Game->deleteAll(array('Game.id' => $games)) &&
					$this->Division->Pool->Game->GameSlot->updateAll (array('GameSlot.game_id' => null), array('GameSlot.game_id' => $games))
				))
			{
				$transaction->commit();

				$cache_file = CACHE . 'queries' . DS . 'division_' . intval($id) . '.data';
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				$cache_file = CACHE . 'queries' . DS . 'schedule_' . intval($id) . '.data';
				if (file_exists($cache_file)) {
					unlink($cache_file);
				}

				$this->Session->setFlash(sprintf(__('%s deleted', true), __('All pools in this stage', true)), 'default', array('class' => 'success'));
			}
		}
		$this->redirect(array('controller' => 'schedules', 'action' => 'add', 'division' => $id));
	}

	/**
	 * Override the redirect function; if it's a view and there's only one division, view the league instead
	 */
	function redirect($url = null, $next = null) {
		if (in_array($url['action'], array('edit', 'view')) && (!array_key_exists('controller', $url) || $url['controller'] == 'divisions')) {
			$league = $this->Division->league($url['division']);
			$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
					array('named' => compact('league')));
			if ($division_count == 1) {
				parent::redirect(array('controller' => 'leagues', 'action' => $url['action'], 'league' => $league), $next);
			}
		}
		parent::redirect($url, $next);
	}

	/**
	 * Ajax functionality
	 */

	function select($date) {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->set('divisions', $this->Division->readByDate($date, $this->_arg('affiliate')));
	}
}
?>
