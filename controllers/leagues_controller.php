<?php
class LeaguesController extends AppController {

	var $name = 'Leagues';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	function publicActions() {
		return array('cron', 'index', 'view', 'schedule', 'standings', 'tooltip', 'division_count');
	}

	function freeActions() {
		return array('index');
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
					'slots',
			)))
			{
				// If a league id is specified, check if we're a manager of that league's affiliate
				$league = $this->_arg('league');
				if ($league) {
					if (in_array($this->League->affiliate($league), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		// Coordinators can perform these operations on leagues where they coordinate the only division
		if (in_array ($this->params['action'], array(
				'add',
				'edit',
				'slots',
		)))
		{
			// If a league id is specified, check if we're a coordinator of all of that league's divisions
			$league = $this->_arg('league');
			if ($league && $this->League->is_coordinator($league, null, true)) {
				return true;
			}
		}

		return false;
	}

	function index() {
		$year = $this->_arg('year');
		if ($year === null) {
			$conditions = array('OR' => array(
				'League.is_open' => true,
				'League.open > CURDATE()',
			));
		} else {
			$conditions = array('YEAR(League.open)' => $year);
		}

		$affiliate = $this->_arg('affiliate');
		if (empty($this->params['requested'])) {
			$affiliates = $this->_applicableAffiliateIDs();
		} else {
			$affiliates = $this->_applicableAffiliateIDs(true);
		}
		$conditions['League.affiliate_id'] = $affiliates;

		// Include any newly created leagues with no divisions, for administrators
		if ($this->is_admin || $this->is_manager) {
			$conditions = array('OR' => array(
				$conditions,
				array(
					'League.open' => '0000-00-00',
					'League.affiliate_id' => $this->_applicableAffiliateIDs(true),
				),
			));
		}

		$sport = $this->_arg('sport');
		if ($sport) {
			$conditions['League.sport'] = $sport;
		}

		$leagues = $this->League->find('all', array(
			'conditions' => $conditions,
			'contain' => array(
				'Affiliate',
				'Division' => array('Day'),
			),
		));
		$this->League->Division->addPlayoffs($leagues);

		usort ($leagues, array('League', 'compareLeagueAndDivision'));
		$this->set(compact('leagues', 'affiliate', 'affiliates', 'sport'));

		if (!empty($this->params['requested'])) {
			return $leagues;
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
			'Division',
			'Affiliate',
		));
		$league = $this->League->read(null, $id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");

		if (count($league['Division']) == 1) {
			list($division, $league_obj) = $this->requestAction(array('controller' => 'divisions', 'action' => 'view'),
					array('named' => array('division' => $league['Division'][0]['id'])));

			// Put the requested division information into a form that matches what's expected here
			$league['Division'][0] = $division['Division'];
			unset($division['Division']);
			$league['Division'][0] += $division;
		}

		$this->set(compact ('league', 'league_obj'));

		$this->set('is_manager', $this->is_manager && in_array($league['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs')));
		$this->set('is_coordinator', $this->League->is_coordinator($league));

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->_addLeagueMenuItems ($league);
	}

	function tooltip() {
		$id = $this->_arg('league');
		if (!$id) {
			return;
		}
		$this->League->contain(array (
			'Division' => array(
				'Person',
				'Day' => array('order' => 'day_id'),
				'Team',
			),
		));
		$league = $this->League->read(null, $id);
		if (!$league) {
			return;
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");
		$this->set(compact('league'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
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

		$contain = array(
			'Division' => array(
				'Team' => array(
					'Person' => array(),
				),
			),
		);
		if ($this->params['url']['ext'] == 'csv') {
			$contain['Division']['Team']['Person'] = array($this->Auth->authenticate->name, 'Related' => $this->Auth->authenticate->name);
		}
		$this->League->contain($contain);
		$league = $this->League->read(null, $id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
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
			Configure::load("sport/{$this->data['League']['sport']}");
			if (array_key_exists('Day', $this->data['League'])) {
				$this->data['Day'] = $this->data['League']['Day'];
			}

			// A new league's dates will be the same as the division's
			$this->data['League']['open'] = $this->data['Division']['open'];
			$this->data['League']['close'] = $this->data['Division']['close'];

			// Division->saveAll needs to not have League data in it
			$division = $this->data;
			unset($division['League']);

			$this->League->create();
			$transaction = new DatabaseTransaction($this->League);
			if ($this->data['Division']['schedule_type'] != 'none' &&
					(empty($this->data['Day']) || empty($this->data['Day'][0])))
			{
				$this->League->save($this->data, array('validate' => 'only'));
				$this->League->Division->saveAll($division, array('validate' => 'only'));
				$this->League->validationErrors['Day'] = sprintf(__('You must select at least one %s!', true), __('day', true));
			} else if ($this->League->save($this->data)) {
				$division['Division']['league_id'] = $this->League->id;
				if ($this->League->Division->saveAll($division)) {
					$transaction->commit();
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('league', true)), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'index'));
				}
			}
			$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('league', true)), 'default', array('class' => 'warning'));
			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
		} else if ($this->_arg('league')) {
			// To clone a league, read the old one and remove the id
			$this->League->contain(array('Division' => 'Day'));
			$this->data = $this->League->read(null, $this->_arg('league'));
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			// Just keep the first division's data
			$this->data['Division'] = reset($this->data['Division']);
			$this->data['Day'] = $this->data['Division']['Day'];

			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
			unset($this->data['League']['id']);
		}

		$this->set('days', $this->League->Division->Day->find('list'));
		$sports = Configure::read('options.sport');
		if (count($sports) == 1) {
			$sport = reset(array_keys($sports));
			$this->set('stat_types', $this->League->StatType->find('all', array(
				'conditions' => array(
					'sport' => $sport,
				)
			)));
		} else {
			// TODO: Limit by sport, presumably with JavaScript later on
		}
		Configure::load("sport/$sport");
		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('league');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			$this->Configuration->loadAffiliate($this->data['League']['affiliate_id']);
			Configure::load("sport/{$this->data['League']['sport']}");
			if (array_key_exists('Day', $this->data['League'])) {
				$this->data['Day'] = $this->data['League']['Day'];
			}
			$transaction = new DatabaseTransaction($this->League);

			// Division->saveAll needs to not have League data in it
			if (array_key_exists('Division', $this->data)) {
				$division = $this->data;
				unset($division['League']);
			}
			if ($this->data['Division']['schedule_type'] != 'none' &&
					(empty($this->data['Day']) || empty($this->data['Day'][0])))
			{
				$this->League->save($this->data, array('validate' => 'only'));
				$this->League->Division->saveAll($division, array('validate' => 'only'));
				$this->League->validationErrors['Day'] = sprintf(__('You must select at least one %s!', true), __('day', true));
			} else if ($this->League->save($this->data) && (!isset($division) || $this->League->Division->saveAll($division))) {
				// Any time that this is called, the division seeding might change.
				// We just reset it here, and it will be recalculated as required elsewhere.
				if (isset($division)) {
					$divisions = array($this->data['Division']['id']);
				} else {
					$divisions = $this->League->divisions($id);
				}
				$this->League->Division->Team->updateAll(array('seed' => 0), array('Team.division_id' => $divisions));

				foreach ($divisions as $division) {
					Cache::delete("division/$division/standings", 'long_term');
					Cache::delete("division/$division/schedule", 'long_term');
				}
				Cache::delete("league/$id/standings", 'long_term');
				Cache::delete("league/$id/schedule", 'long_term');

				$transaction->commit();
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('league', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('league', true)), 'default', array('class' => 'warning'));
			$this->Configuration->loadAffiliate($this->League->affiliate($id));
		}

		// Very likely that we need to read existing league information for menu purposes
		$this->League->contain(array(
			'Division' => array(
				'Person',
				'Day' => array('order' => 'day_id'),
			),
			'StatType',
		));

		$v = $this->League->validationErrors;
		$this->League->read(null, $id);
		$this->League->validationErrors = $v;

		if (!$this->League->data) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		foreach (array_keys($this->League->data['Division']) as $key) {
			Division::_addNames($this->League->data['Division'][$key], $this->League->data['League']);
		}
		$this->_addLeagueMenuItems ($this->League->data);

		$this->Configuration->loadAffiliate($this->League->data['League']['affiliate_id']);
		Configure::load("sport/{$this->League->data['League']['sport']}");
		if (count($this->League->data['Division']) == 1) {
			// Adjust loaded data
			$this->League->data['Division'] = array_pop($this->League->data['Division']);
			$this->League->data['Day'] = $this->League->data['Division']['Day'];

			$this->set('league_obj', $this->_getComponent ('LeagueType', $this->League->data['Division']['schedule_type'], $this));
			$this->set('is_coordinator', false);
		}

		if (empty($this->data)) {
			$this->data = $this->League->data;
		}

		$this->set('days', $this->League->Division->Day->find('list'));
		$this->set('stat_types', $this->League->StatType->find('all', array(
			'conditions' => array(
				'sport' => $this->data['League']['sport'],
			)
		)));
		$this->set('affiliates', $this->_applicableAffiliates(true));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
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

	function schedule() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		// Hopefully, everything we need is already cached
		$cache_key = 'league/' . intval($id) . '/schedule';
		$cached = Cache::read($cache_key, 'long_term');
		if ($cached) {
			$league = $cached;
		} else {
			$this->League->contain(array('Division' => array('Day' => array('order' => 'day_id'))));
			$league = $this->League->read(null, $id);
			if (!$league) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}

			$divisions = Set::extract('/Division/id', $league);
			if (empty($divisions)) {
				$this->Session->setFlash(__('This league has no divisions yet.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}

			$divisions = $this->League->Division->find('all', array (
				'contain' => array(
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
				),
				'conditions' => array('Division.id' => $divisions),
			));

			$league['Game'] = array();
			foreach ($divisions as $division) {
				foreach ($division['Game'] as $game) {
					$game['Division'] = $division['Division'];
					$league['Game'][] = $game;
				}
			}
			if (empty ($league['Game'])) {
				$this->Session->setFlash(__('This league has no games scheduled yet.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}

			// Sort games by date, time and field
			usort ($league['Game'], array ('Game', 'compareDateAndField'));

			Cache::write($cache_key, $league, 'long_term');
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");

		$is_coordinator = $this->League->is_coordinator($league);
		$is_manager = $this->is_manager && in_array($league['League']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));
		if ($this->is_admin || $is_manager || $is_coordinator) {
			$edit_date = $this->_arg('edit_date');
			if (!empty ($this->data)) {
				$edit_date = $this->data['Game']['edit_date'];
				unset ($this->data['Game']['edit_date']);
			}
		} else {
			$edit_date = null;
		}

		$multi_day = (count(array_unique(Set::extract('/Division[schedule_type!=tournament]/Day/id', $league))) > 1);

		if ($edit_date) {
			$tournament_games = Set::extract ('/Game[type!=' . SEASON_GAME . "]/GameSlot[game_date=$edit_date]", $league);
			$is_tournament = !empty($tournament_games);
			$divisions = array();
			$double_booking = false;
			foreach ($league['Division'] as $division) {
				if ($this->is_admin || $is_manager || in_array($division['id'], $this->UserCache->read('DivisionIDs'))) {
					$divisions[] = $division['id'];
					$double_booking |= $division['double_booking'];
				}
			}
			$game_slots = $this->League->Division->DivisionGameslotAvailability->GameSlot->getAvailable($divisions, $edit_date, $is_tournament, $double_booking, $multi_day);
		} else {
			$tournament_games = Set::extract ('/Game[type!=' . SEASON_GAME . ']', $league);
			$is_tournament = !empty($tournament_games);
		}

		// Save posted data
		if (!empty ($this->data) && ($this->is_admin || $is_manager || $is_coordinator)) {
			if ($this->Lock->lock ('scheduling', $this->League->affiliate($league['League']['id']), 'schedule creation or edit')) {
				$ret = $this->League->Division->Game->_validateAndSaveSchedule($this->data, $game_slots);
				if ($ret === true) {
					$this->Session->setFlash(__('Schedule changes saved!', true), 'default', array('class' => 'success'));
				} else {
					$this->Session->setFlash($ret['text'], 'default', array('class' => $ret['class']));
					$this->Session->setFlash($ret['text'], 'default', array('class' => $ret['class']), 'schedule_edit');
				}
				if ($ret === true || !empty($ret['result'])) {
					Cache::delete($cache_key, 'long_term');
					Cache::delete("league/$id/standings", 'long_term');
					Cache::delete("league/$id/schedule", 'long_term');
					foreach ($league['Division'] as $division) {
						Cache::delete("division/{$division['id']}/standings", 'long_term');
						Cache::delete("division/{$division['id']}/schedule", 'long_term');
					}
					$this->redirect (array('action' => 'schedule', 'league' => $id));
				}
			}
		}

		$this->set(compact ('id', 'league', 'edit_date', 'game_slots', 'is_coordinator', 'is_tournament', 'multi_day'));

		$this->_addLeagueMenuItems ($league);
	}

	function standings() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		// Hopefully, everything we need is already cached
		$cache_key = 'league/' . intval($id) . '/standings';
		$cached = Cache::read($cache_key, 'long_term');
		if ($cached) {
			$league = $cached;
		}
		if (!empty($league)) {
			$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
			Configure::load("sport/{$league['League']['sport']}");
			$spirit_obj = $this->_getComponent ('Spirit', $league['League']['sotg_questions'], $this);
		} else {
			$this->League->contain(array (
				'Division' => array(
					'Day' => array('order' => 'day_id'),
					'Team',
				),
			));
			$league = $this->League->read(null, $id);
			if (!$league) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
			Configure::load("sport/{$league['League']['sport']}");

			$spirit_obj = $this->_getComponent ('Spirit', $league['League']['sotg_questions'], $this);

			$has_games = false;
			foreach ($league['Division'] as $key => $division) {
				// Find all games played by teams that are currently in this division,
				// or tournament games for this division
				$teams = Set::extract ('/Team/id', $division);
				if (empty($teams)) {
					$conditions = array(
						'Game.division_id' => $division['id'],
						'Game.type !=' => SEASON_GAME,
					);
				} else {
					$conditions = array('OR' => array(
						'Game.home_team' => $teams,
						'Game.away_team' => $teams,
						'AND' => array(
							'Game.division_id' => $division['id'],
							'Game.type !=' => SEASON_GAME,
						),
					));
				}
				$conditions['NOT'] = array('Game.status' => Configure::read('unplayed_status'));

				$league['Division'][$key]['Game'] = $this->League->Division->Game->find('all', array(
						'conditions' => $conditions,
						'contain' => array(
							'GameSlot',
							'HomePoolTeam' => array('Pool', 'DependencyPool'),
							'AwayPoolTeam' => array('Pool', 'DependencyPool'),
							'ScoreEntry',
							'SpiritEntry',
						),
				));

				if (!empty ($league['Division'][$key]['Game'])) {
					// Sort games by date, time and field
					usort ($league['Division'][$key]['Game'], array ('Game', 'compareDateAndField'));
					Game::_adjustEntryIndices($league['Division'][$key]['Game']);
					$has_games = true;

					// If there's anyone without seed information, save the seeds
					$league_obj = $this->_getComponent ('LeagueType', $division['schedule_type'], $this);
					$league_obj->sort($league['Division'][$key]['Team'], $league['Division'][$key], $league['League'], $league['Division'][$key]['Game'], $spirit_obj, false);
					$league['Division'][$key]['render_element'] = $league_obj->render_element;
					$unseeded = Set::extract('/Team[seed=0]', $division);
					if (!empty($unseeded)) {
						$seed = 0;
						foreach ($league['Division'][$key]['Team'] as $tkey => $team) {
							$this->League->Division->Team->id = $team['id'];
							$this->League->Division->Team->saveField('seed', ++$seed);
							$league['Division'][$key]['Team'][$tkey]['seed'] = $seed;
						}
					}
				}
			}

			if (!$has_games) {
				$this->Session->setFlash(__('Cannot generate standings for a league with no schedule.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}

			Cache::write($cache_key, $league, 'long_term');
		}

		$this->set(compact ('league', 'league_obj', 'spirit_obj'));
		$this->set('is_coordinator', $this->League->is_coordinator($league));

		$this->_addLeagueMenuItems ($league);
	}

	function slots() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain('Division');
		$league = $this->League->read(null, $id);
		if (!$league) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
		Configure::load("sport/{$league['League']['sport']}");

		$divisions = Set::extract('/Division/id', $league);
		$this->League->Division->DivisionGameslotAvailability->GameSlot->contain();
		$join = array( array(
				'table' => "{$this->League->Division->tablePrefix}division_gameslot_availabilities",
				'alias' => 'DivisionGameslotAvailability',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'DivisionGameslotAvailability.game_slot_id = GameSlot.id',
		));
		$dates = $this->League->Division->DivisionGameslotAvailability->GameSlot->find('all', array(
			'fields' => array('DISTINCT GameSlot.game_date'),
			'conditions' => array('DivisionGameslotAvailability.division_id' => $divisions),
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
			$this->League->Division->DivisionGameslotAvailability->GameSlot->contain (array (
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
			$slots = $this->League->Division->DivisionGameslotAvailability->GameSlot->find('all', array(
				'conditions' => array(
					'DivisionGameslotAvailability.division_id' => $divisions,
					'GameSlot.game_date' => $date,
				),
				'fields' => array('DISTINCT *'),
				'joins' => $join,
				'order' => array('GameSlot.game_date', 'GameSlot.game_start', 'Field.id'),
			));

			$tournament_games = Set::extract ('/Game[type!=' . SEASON_GAME . "]", $slots);
			$is_tournament = !empty($tournament_games);
		}

		$this->set(compact('league', 'dates', 'date', 'slots', 'is_tournament'));

		$this->_addLeagueMenuItems ($league['League']);
	}

	function cron() {
		$this->layout = 'bare';
		if (!ini_get('safe_mode')) { 
			set_time_limit(1800);
		}

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		$this->set('leagues', $this->League->recalculateRatings());

		// Update the badges in any divisions that are about to open or have recently closed
		$contain = array('League');
		if (Configure::read('feature.badges')) {
			$badges = $this->League->Division->Person->Badge->find('all', array(
					'conditions' => array(
						'Badge.category' => 'team',
						'Badge.active' => true,
					),
					'contain' => array(),
			));

			// Read team rosters, if there are badges that might be affected
			if (!empty($badges)) {
				$badge_obj = $this->_getComponent('badge', '', $this);
				$contain['Team'] = 'Person';
			}
		}

		$to_close = $this->League->Division->find('all', array(
				'conditions' => array(
					'Division.is_open' => true,
					'OR' => array(
						'Division.open > DATE_ADD(NOW(), INTERVAL 21 DAY)',
						'Division.close < DATE_ADD(NOW(), INTERVAL -7 DAY)',
					),
				),
				'contain' => $contain,
				'order' => 'Division.open',
		));
		$to_open = $this->League->Division->find('all', array(
				'conditions' => array(
					'Division.is_open' => 0,
					'Division.open < DATE_ADD(NOW(), INTERVAL 21 DAY)',
					'Division.close > DATE_ADD(NOW(), INTERVAL -7 DAY)',
				),
				'contain' => $contain,
				'order' => 'Division.open',
		));

		$this->set(compact('to_close', 'to_open'));

		if (!empty($to_close)) {
			$this->League->Division->updateAll (array('Division.is_open' => 0), array('Division.id' => Set::extract('/Division/id', $to_close)));
			if (isset($badge_obj)) {
				foreach ($to_close as $division) {
					foreach ($division['Team'] as $team) {
						foreach ($team['Person'] as $person) {
							$badge_obj->update('team', $person['TeamsPerson']);
							$this->UserCache->_deleteTeamData($person['id']);
						}
					}
				}
			}
		}
		if (!empty($to_open)) {
			$this->League->Division->updateAll (array('Division.is_open' => true), array('Division.id' => Set::extract('/Division/id', $to_open)));
			if (isset($badge_obj)) {
				foreach ($to_open as $division) {
					foreach ($division['Team'] as $team) {
						foreach ($team['Person'] as $person) {
							$badge_obj->update('team', $person['TeamsPerson']);
							$this->UserCache->_deleteTeamData($person['id']);
						}
					}
				}
			}
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
