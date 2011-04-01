<?php
class LeaguesController extends AppController {

	var $name = 'Leagues';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	function isAuthorized() {
		if (in_array ($this->params['action'], array(
				'index',
				'view',
				'schedule',
				'standings',
				'scores',
		)))
		{
			return true;
		}

		// People can perform these operations on leagues they coordinate
		if (in_array ($this->params['action'], array(
				'edit',
				'approve_scores',
				'fields',
				'slots',
				'status',
				'allstars',
				'emails',
				'spirit',
				'ratings',
				'validate_ratings',
		)))
		{
			// If a league id is specified, check if we're a coordinator of that league
			$league = $this->_arg('league');
			if ($league && in_array ($league, $this->Session->read('Zuluru.LeagueIDs'))) {
				return true;
			}
		}

		return false;
	}

	function index() {
		$year = $this->_arg('year');
		if ($year === null) {
			$conditions = array('League.is_open' => true);
		} else {
			$conditions = array('YEAR(League.open)' => $year);
		}
		$this->set('leagues', $this->League->findSortByDay('all', array(
			'conditions' => $conditions,
		)));

		$this->League->recursive = -1;
		$this->set('years', $this->League->find('all', array(
			'fields' => 'DISTINCT YEAR(open) AS year',
			'conditions' => array('YEAR(open) !=' => 0),
			'order' => 'open',
		)));
	}

	function view() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Person',
			'Day' => array('order' => 'day_id'),
			'Team' => array ('Person'),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
		$league_obj->sort($league);

		$this->set(compact ('league', 'league_obj'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.LeagueIDs')));

		$this->_addLeagueMenuItems ($this->League->data);

		// Set up a couple more variables that the player popup block needs
		$this->set('my_id', $this->Auth->user('id'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->League->create();
			if ($this->League->save($this->data)) {
				$this->Session->setFlash(__('The league has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The league could not be saved. Please, try again.', true));
			}
		}

		$this->set('days', $this->League->Day->find('list'));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->data['League']['schedule_type'], $this));
		$this->set('is_coordinator', false);
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('league');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->League->saveAll($this->data)) {
				$this->Session->setFlash(__('The league has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The league could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->League->contain (array (
				'Day' => array('order' => 'day_id'),
			));
			$this->data = $this->League->read(null, $id);
		}
		$this->set('days', $this->League->Day->find('list'));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->data['League']['schedule_type'], $this));
		$this->set('is_coordinator',
			array_key_exists ('LeaguesPerson', $this->data['League']) &&
			array_key_exists ('position', $this->data['League']['LeaguesPerson']) &&
			$this->data['League']['LeaguesPerson']['position'] == 'coordinator');

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function scheduling_fields() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->params['url']['data']['League']['schedule_type'], $this));
	}

	function add_coordinator() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain('Person');
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact('league'));

		$person_id = $this->_arg('person');
		if ($person_id != null) {
			$this->League->Person->contain(array('League' => array('conditions' => array('League.id' => $id))));
			$person = $this->League->Person->read(null, $person_id);
			if (!empty ($person['League'])) {
				$this->Session->setFlash(__("{$person['Person']['full_name']} is already a coordinator of this league", true));
			} else {
				$people = Set::extract ('/Person/id', $league);
				$people[] = $person['Person']['id'];
				// TODO: If we add more coordinator types, we need to save the position here
				if ($this->League->saveAll (array(
						'League' => array('id' => $id),
						'Person' => $people,
				)))
				{
					$this->Session->setFlash(__("Added {$person['Person']['full_name']} as coordinator", true));
					$this->redirect(array('action' => 'view', 'league' => $id));
				}
			}
		}

		$params = $url = $this->_extractSearchParams();
		unset ($params['league']);
		unset ($params['person']);
		if (!empty($params)) {
			$test = trim ($params['first_name'], ' *') . trim ($params['last_name'], ' *');
			if (strlen ($test) < 2) {
				$this->set('short', true);
			} else {
				// This pagination needs the model at the top level
				$this->Person = $this->League->Person;
				$this->_mergePaginationParams();
				$this->paginate['Person'] = array(
					'conditions' => array_merge (
						$this->_generateSearchConditions($params, 'Person'),
						array('Group.name' => array('Volunteer', 'Administrator'))
					),
					'contain' => array('Group', 'Upload'),
				);
				$this->set('people', $this->paginate('Person'));
			}
		}
		$this->set(compact('url'));
	}

	function remove_coordinator() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(__('Invalid person', true));
			$this->redirect(array('action' => 'view', 'league' => $id));
		}

		$join = ClassRegistry::init('LeaguesPerson');
		if ($join->deleteAll (array('league_id' => $id, 'person_id' => $person_id))) {
			$this->Session->setFlash(__('Successfully removed coordinator', true));
		} else {
			$this->Session->setFlash(__('Failed to remove coordinator!', true));
		}
		$this->redirect(array('action' => 'view', 'league' => $id));
	}

	function ratings() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		if (!empty($this->data)) {
			if ($this->League->Team->saveAll($this->data['Team'])) {
				$this->Session->setFlash(__('The league has been saved', true));
				$this->redirect(array('action' => 'view', 'league' => $id));
			} else {
				$this->Session->setFlash(__('The league could not be saved. Please, try again.', true));
			}
		}

		$this->League->contain (array (
			'Day' => array('order' => 'day_id'),
			'Team' => array(
				'Person',
				'order' => 'rating DESC',
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact ('league'));
		$this->_addLeagueMenuItems ($this->League->data);
	}

	function delete() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for league', true));
			$this->redirect(array('action'=>'index'));
		}

		// TODO: Handle deletions
		$this->Session->setFlash(__('Deletions are not currently supported', true));
		$this->redirect('/');

		if ($this->League->delete($id)) {
			$this->Session->setFlash(__('League deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('League was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}

	function schedule() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$is_coordinator = in_array($id, $this->Session->read('Zuluru.LeagueIDs'));
		if ($this->is_admin || $is_coordinator) {
			$edit_date = $this->_arg('edit_date');
			if (!empty ($this->data)) {
				$edit_date = $this->data['Game']['edit_date'];
				unset ($this->data['Game']['edit_date']);
			}
		} else {
			$edit_date = null;
		}

		if ($edit_date) {
			$game_slots = $this->League->LeagueGameslotAvailability->GameSlot->getAvailable($id, $edit_date);
		}

		// Save posted data
		if (!empty ($this->data) && ($this->is_admin || $is_coordinator)) {
			if ($this->_validateAndSaveSchedule($game_slots)) {
				$this->redirect (array('action' => 'schedule', 'league' => $id));
			}
		}

		$this->League->contain (array (
			'Day' => array('order' => 'day_id'),
			'Team',
			'Game' => array(
				'GameSlot' => array('Field' => array('ParentField')),
				'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $this->Session->read('Zuluru.TeamIDs'))),
				// Get the list of captains for each team, for the popup
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
						'fields' => array('id', 'first_name', 'last_name'),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
						'fields' => array('id', 'first_name', 'last_name'),
					),
				),
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('This league has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}
		// Sort games by date, time and field
		usort ($league['Game'], array ('League', 'compareDateAndField'));

		$this->set(compact ('id', 'league', 'edit_date', 'game_slots', 'is_coordinator'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function _validateAndSaveSchedule($available_slots) {
		$publish = $this->data['Game']['publish'];
		unset ($this->data['Game']['publish']);
		$allow_double_header = $this->data['Game']['double_header'];
		unset ($this->data['Game']['double_header']);

		$games = count($this->data['Game']);
		// TODO: Remove workaround for Set::extract bug
		$this->data['Game'] = array_values($this->data['Game']);
		$slots = Set::extract ('/Game/GameSlot/id', $this->data);
		if (in_array ('', $slots)) {
			$this->Session->setFlash(__('You cannot choose the "---" as the game time/place!', true));
			return false;
		}

		$slot_counts = array_count_values ($slots);
		foreach ($slot_counts as $slot_id => $count) {
			if ($count > 1) {
				$this->League->Game->GameSlot->contain(array(
						'Field' => 'ParentField',
				));
				$slot = $this->League->Game->GameSlot->read(null, $slot_id);
				$slot_field = $slot['Field']['long_name'];
				$slot_time = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
				$this->Session->setFlash(sprintf (__('Game slot at %s on %s was selected more than once!', true), $slot_field, $slot_time));
				return false;
			}
		}

		$teams = array_merge (
				Set::extract ('/Game/home_team', $this->data),
				Set::extract ('/Game/away_team', $this->data)
		);
		if (in_array ('', $teams)) {
			$this->Session->setFlash(__('You cannot choose the "---" as the team!', true));
			return false;
		}

		$team_counts = array_count_values ($teams);
		foreach ($team_counts as $team_id => $count) {
			if ($count > 1) {
				$this->League->Team->recursive = -1;
				$team = $this->League->Team->read(null, $team_id);

				if ($allow_double_header) {
					// Check that the double-header doesn't cause conflicts; must be at the same site, but different times
					$team_slot_ids = array_merge(
						Set::extract ("/Game[home_team=$team_id]/GameSlot/id", $this->data),
						Set::extract ("/Game[away_team=$team_id]/GameSlot/id", $this->data)
					);
					if (count ($team_slot_ids) != count (array_unique ($team_slot_ids))) {
						$this->Session->setFlash(sprintf (__('Team %s was scheduled twice in the same time slot!', true), $team['Team']['name']));
						return false;
					}

					$this->League->Game->GameSlot->contain(array(
							'Field' => 'ParentField',
					));
					$team_slots = $this->League->Game->GameSlot->find('all', array('conditions' => array(
							'GameSlot.id' => $team_slot_ids,
					)));
					foreach ($team_slots as $key1 => $slot1) {
						foreach ($team_slots as $key2 => $slot2) {
							if ($key1 != $key2) {
								if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] &&
									$slot1['GameSlot']['game_start'] >= $slot2['GameSlot']['game_start'] &&
									$slot1['GameSlot']['game_start'] < $slot2['GameSlot']['display_game_end'])
								{
									$this->Session->setFlash(sprintf (__('Team %s was scheduled in overlapping time slots!', true), $team['Team']['name']));
									return false;
								}
								$site1 = ($slot1['Field']['parent_id'] == null ? $slot1['Field']['Field']['id'] : $slot1['ParentField']['id']);
								$site2 = ($slot2['Field']['parent_id'] == null ? $slot2['Field']['Field']['id'] : $slot2['ParentField']['id']);
								if ($site1 != $site2) {
									$this->Session->setFlash(sprintf (__('Team %s was scheduled on fields at different sites!', true), $team['Team']['name']));
									return false;
								}
							}
						}
					}
				} else {
					$this->Session->setFlash(sprintf (__('Team %s was selected more than once!', true), $team['Team']['name']));
					return false;
				}
			}
		}

		if (!$this->Lock->lock ('scheduling')) {
			return false;
		}
		if (!$this->League->Game->_saveGames($this->data['Game'], $publish)) {
			$this->Lock->unlock();
			return false;
		}
		$this->Lock->unlock();

		$unused_slots = array_diff (Set::extract ('/GameSlot/id', $available_slots), $slots);
		if ($this->League->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $unused_slots))) {
			$this->Session->setFlash(__('Schedule changes saved!', true));
			return true;
		} else {
			$this->Session->setFlash(__('Saved schedule changes, but failed to clear unused slots!', true));
			return false;
		}
	}

	// TODO: Remove this entire function once ratings calculations are 100%
	function validate_ratings() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		$correct = $this->_arg('correct');

		$this->League->contain (array (
			'Team',
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		// Find all games played by teams that are currently in this league
		$teams = Set::extract ('/Team/id', $league);
		$this->League->Game->contain (array('GameSlot', 'HomeTeam', 'AwayTeam'));
		$league['Game'] = $this->League->Game->find('all', array(
				'conditions' => array(
					'OR' => array(
						'home_team' => $teams,
						'away_team' => $teams,
					),
				),
		));

		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('This league has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);

		// Sort games by date, time and field
		usort ($league['Game'], array ('League', 'compareDateAndField'));

		$teams = array();
		foreach ($league['Team'] as $team) {
			$teams[$team['id']] = $team;
		}
		$league['Team'] = $teams;
		$moved_teams = array();
		$game_updates = array();

		foreach ($league['Game'] as $key => $game) {
			// Handle teams that have moved
			if (!array_key_exists ($game['Game']['home_team'], $league['Team'])) {
				$moved_teams[] = $game['Game']['home_team'];
				$league['Team'][$game['Game']['home_team']] = $game['HomeTeam'];
			}
			if (!array_key_exists ($game['Game']['away_team'], $league['Team'])) {
				$moved_teams[] = $game['Game']['away_team'];
				$league['Team'][$game['Game']['away_team']] = $game['AwayTeam'];
			}

			if (!array_key_exists ('current_rating', $league['Team'][$game['Game']['home_team']])) {
				$league['Team'][$game['Game']['home_team']]['current_rating'] = $game['Game']['rating_home'];
			}
			if (!array_key_exists ('current_rating', $league['Team'][$game['Game']['away_team']])) {
				$league['Team'][$game['Game']['away_team']]['current_rating'] = $game['Game']['rating_away'];
			}

			$league['Game'][$key]['Game']['calc_rating_home'] = $league['Team'][$game['Game']['home_team']]['current_rating'];
			$league['Game'][$key]['Game']['calc_rating_away'] = $league['Team'][$game['Game']['away_team']]['current_rating'];

			// Note: We don't check config for whether rating points are transferred on defaults, but this
			// is only being used in the interim for a league where they are, so it's not an issue.
			if ($this->League->Game->_is_finalized ($game) && $game['Game']['status'] != 'rescheduled') {
				if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
					$league['Game'][$key]['Game']['expected'] = $this->League->Game->_calculate_expected_win($league['Team'][$game['Game']['home_team']]['current_rating'], $league['Team'][$game['Game']['away_team']]['current_rating']);
					$change = $league_obj->calculateRatingsChange($game['Game']['home_score'], $game['Game']['away_score'], $league['Game'][$key]['Game']['expected']);
					$league['Team'][$game['Game']['home_team']]['current_rating'] += $change;
					$league['Team'][$game['Game']['away_team']]['current_rating'] -= $change;
				} else {
					$league['Game'][$key]['Game']['expected'] = $this->League->Game->_calculate_expected_win($league['Team'][$game['Game']['away_team']]['current_rating'], $league['Team'][$game['Game']['home_team']]['current_rating']);
					$change = $league_obj->calculateRatingsChange($game['Game']['home_score'], $game['Game']['away_score'], $league['Game'][$key]['Game']['expected']);
					$league['Team'][$game['Game']['home_team']]['current_rating'] -= $change;
					$league['Team'][$game['Game']['away_team']]['current_rating'] += $change;
				}
				$league['Game'][$key]['Game']['calc_rating_points'] = $change;
			} else {
				$league['Game'][$key]['Game']['calc_rating_points'] = $league['Game'][$key]['Game']['expected'] = null;
			}

			// Only save updates for games that actually changed
			$update = array('id' => $game['Game']['id']);
			if ($league['Game'][$key]['Game']['calc_rating_home'] != $game['Game']['rating_home']) {
				$update['rating_home'] = $league['Game'][$key]['Game']['calc_rating_home'];
			}
			if ($league['Game'][$key]['Game']['calc_rating_away'] != $game['Game']['rating_away']) {
				$update['rating_away'] = $league['Game'][$key]['Game']['calc_rating_away'];
			}
			if ($league['Game'][$key]['Game']['calc_rating_points'] != $game['Game']['rating_points']) {
				$update['rating_points'] = $league['Game'][$key]['Game']['calc_rating_points'];
			}
			if (count($update) > 1) {
				$game_updates[] = $update;
			}
		}

		if ($correct && !empty ($game_updates)) {
			$this->League->Game->saveAll ($game_updates);
		}

		// Remove moved teams, and update the rest
		foreach ($moved_teams as $team) {
			unset ($league['Team'][$team]);
		}
		if ($correct && !empty ($game_updates)) {
			$team_updates = array();
			foreach ($league['Team'] as $key => $team) {
				$team_updates[] = array(
					'id' => $team['id'],
					'rating' => $team['current_rating'],
				);
			}
			$this->League->Team->saveAll ($team_updates);
		}

		// Find new rankings for each team, and sort by old ranking
		$new = Set::sort (array_values ($league['Team']), '/current_rating', 'DESC');
		foreach ($new as $key => $team) {
			$league['Team'][$team['id']]['rank'] = $key + 1;
		}
		$league['Team'] = Set::sort (array_values ($league['Team']), '/rating', 'DESC');

		$this->set(compact ('id', 'league', 'league_obj', 'correct'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function standings() {
		$id = $this->_arg('league');
		$teamid = $this->_arg('team');
		$showall = $this->_arg('full');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Day' => array('order' => 'day_id'),
			// Get the list of captains for each team, for the popup
			'Team' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name'),
				),
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		// Find all games played by teams that are currently in this league
		$teams = Set::extract ('/Team/id', $league);
		$this->League->Game->contain (array('GameSlot', 'SpiritEntry'));
		$league['Game'] = $this->League->Game->find('all', array(
				'conditions' => array(
					'OR' => array(
						'home_team' => $teams,
						'away_team' => $teams,
					),
				),
		));

		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('Cannot generate standings for a league with no schedule.', true));
			$this->redirect(array('action' => 'index'));
		}

		// Sort games by date, time and field
		usort ($league['Game'], array ('League', 'compareDateAndField'));
		$this->League->Game->_adjustEntryIndices($league['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
		$league_obj->sort($league);

		$spirit_obj = $this->_getComponent ('Spirit', $league['League']['sotg_questions'], $this);

		// If we're asking for "team" standings, only show the 5 teams above and 5 teams below this team.
		// Don't bother if there are 24 teams or less (24 is probably the largest fall league size).
		// If $showall is set, don't remove teams.
		if (!$showall && $teamid != null && count($league['Team']) > 24) {
			$index_of_this_team = false;
			foreach ($league['Team'] as $i => $team) {
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
			if ($last < count($league['Team']) - 1) {
				$more_after = true; // we never need to know how many after
			}

			$league['Team'] = array_slice ($league['Team'], $first, $last + 1 - $first);
		}
		$this->set(compact ('league', 'league_obj', 'spirit_obj', 'teamid', 'showall', 'more_before', 'more_after'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.LeagueIDs')));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function scores() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Day' => array('order' => 'day_id'),
			'Team',
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		// Find all games played by teams that are currently in this league
		$teams = Set::extract ('/Team/id', $league);
		$this->League->Game->contain (array(
				'HomeTeam',
				'AwayTeam',
				'GameSlot' => array('Field' => array('ParentField')),
		));
		$league['Game'] = $this->League->Game->find('all', array(
				'conditions' => array(
					'OR' => array(
						'home_team' => $teams,
						'away_team' => $teams,
					),
				),
		));
		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('This league has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}

		// Sort games by date, time and field
		usort ($league['Game'], array ('League', 'compareDateAndField'));
		$this->League->Game->_adjustEntryIndices($league['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
		$league_obj->sort($league);

		// Move the teams into an array indexed by team id, for easier use in the view
		$teams = array();
		foreach ($league['Team'] as $team) {
			$teams[$team['id']] = $team;
		}
		$league['Team'] = $teams;

		$this->set(compact ('league'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.LeagueIDs')));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function fields() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Team',
			'Game' => array(
				'GameSlot' => array('Field' => array('ParentField')),
				'HomeTeam',
				'AwayTeam',
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('This league has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
		$league_obj->sort($league);

		// Gather all possible fields this league can use
		$join = array(
			array(
				'table' => "{$this->League->tablePrefix}game_slots",
				'alias' => 'GameSlot',
				'type' => 'INNER',
				'foreignKey' => false,
				'conditions' => 'LeagueGameslotAvailability.game_slot_id = GameSlot.id',
			),
			array(
				'table' => "{$this->League->tablePrefix}fields",
				'alias' => 'Field',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Field.id = GameSlot.field_id',
			),
			array(
				'table' => "{$this->League->tablePrefix}regions",
				'alias' => 'Region',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Region.id = Field.region_id',
			),
			array(
				'table' => "{$this->League->tablePrefix}fields",
				'alias' => 'ParentField',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'ParentField.id = Field.parent_id',
			),
			array(
				'table' => "{$this->League->tablePrefix}regions",
				'alias' => 'ParentRegion',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'ParentRegion.id = ParentField.region_id',
			),
		);
		$this->League->LeagueGameslotAvailability->contain (array ());
		// TODO: Fix this to use DISTINCT Field.code, once we've restructured the field model
		$temp_fields = $this->League->LeagueGameslotAvailability->find('all', array(
			'fields' => array('DISTINCT Field.id', 'Field.code', 'Field.name', 'Region.name',
					'ParentField.id', 'ParentField.code', 'ParentField.name', 'ParentRegion.name',
					'GameSlot.game_start'),
			'conditions' => array('LeagueGameslotAvailability.league_id' => $id),
//			'order' => 'Region.name, Field.code, GameSlot.game_start',
			'joins' => $join,
		));

		// Put the field information into a useful form
		$fields = array();
		foreach ($temp_fields as $field) {
			if (empty ($field['Field']['code'])) {
				$field['Field']['code'] = $field['ParentField']['code'];
				$field['Field']['name'] = $field['ParentField']['name'];
				$field['Region']['name'] = $field['ParentRegion']['name'];
			}
			$key = "{$field['Field']['code']} {$field['GameSlot']['game_start']}";
			if (!array_key_exists ($key, $fields)) {
				unset ($field['ParentField']);
				unset ($field['ParentRegion']);
				$fields[$key] = $field;
			}
		}
		usort ($fields, array ($this, '_compareRegionAndCodeAndStart'));

		$this->set(compact ('league', 'league_obj', 'fields'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.LeagueIDs')));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function _compareRegionAndCodeAndStart($a, $b) {
		if ($a['Region']['name'] < $b['Region']['name']) {
			return -1;
		} else if ($a['Region']['name'] > $b['Region']['name']) {
			return 1;
		} else if ($a['Field']['code'] < $b['Field']['code']) {
			return -1;
		} else if ($a['Field']['code'] > $b['Field']['code']) {
			return 1;
		} else if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		} else if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		}
		return 0;
	}

	function slots() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->recursive = -1;
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->LeagueGameslotAvailability->GameSlot->recursive = -1;
		$join = array( array(
				'table' => "{$this->League->tablePrefix}league_gameslot_availabilities",
				'alias' => 'LeagueGameslotAvailability',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'LeagueGameslotAvailability.game_slot_id = GameSlot.id',
		));
		$dates = $this->League->LeagueGameslotAvailability->GameSlot->find('all', array(
			'fields' => array('DISTINCT GameSlot.game_date'),
			'conditions' => array('LeagueGameslotAvailability.league_id' => $id),
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
			$this->League->LeagueGameslotAvailability->GameSlot->contain (array (
					'Game' => array(
						'HomeTeam',
						'AwayTeam',
					),
					'Field' => array(
						'ParentField' => array('Region'),
						'Region',
					),
			));
			$slots = $this->League->LeagueGameslotAvailability->GameSlot->find('all', array(
				'conditions' => array('LeagueGameslotAvailability.league_id' => $id, 'GameSlot.game_date' => $date),
				'joins' => $join,
			));
			$slots = Set::sort($slots, '{n}.Field.code', 'asc');
		}

		$this->set(compact('league', 'dates', 'date', 'slots'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function status() { // TODO
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

	}

	function allstars() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		$min = $this->_arg('min');
		if (!$min) {
			$min = 2;
		}

		$this->League->recursive = -1;
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$allstars = $this->League->Game->Allstar->find ('all', array(
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender', 'Person.email',
					'COUNT(Allstar.game_id) AS count',
				),
				'conditions' => array(
					'Game.league_id' => $id,
				),
				'group' => "Allstar.person_id HAVING count >= $min",
				'order' => array('Person.gender' => 'DESC', 'count' => 'DESC', 'Person.last_name', 'Person.first_name'),
		));

		$this->set(compact('league', 'allstars', 'min'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function emails() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Team' => array (
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('league'));

		$this->_addLeagueMenuItems ($this->League->data);
	}

	function spirit() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->contain (array (
			'Game' => array(
				'GameSlot',
				'SpiritEntry',
				'HomeTeam',
				'AwayTeam',
				'order' => 'Game.id',
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}
		if (empty ($league['Game'])) {
			$this->Session->setFlash(__('This league has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}

		$spirit_obj = $this->_getComponent ('Spirit', $league['League']['sotg_questions'], $this);

		$this->set(compact('league', 'spirit_obj'));

		$this->_addLeagueMenuItems ($this->League->data);

		// This is in case we're doing CSV output
		$this->set('download_file_name', "Spirit - {$league['League']['long_name']}");
	}

	function approve_scores() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->recursive = -1;
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->League->Game->contain (array (
			// Get the list of captains for each team, for building the email link
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'GameSlot',
			'ScoreEntry',
		));
		$games = $this->League->Game->find ('all', array(
				'conditions' => array(
					'Game.league_id' => $id,
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
			$this->Session->setFlash(__('There are currently no games to approve in this league.', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->League->Game->_adjustEntryIndices($games);

		$this->set(compact ('league', 'games'));
		$this->set('is_coordinator', in_array($id, $this->Session->read('Zuluru.LeagueIDs')));

		// TODO: Add this type of links everywhere. Maybe do it in beforeRender?
		$this->_addLeagueMenuItems ($this->League->data);
	}

	/**
	 * Ajax functionality
	 */

	function select($date) {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->set('leagues', $this->League->readByDate($date));
	}

	function cron() {
		// TODO UPDATE leagues SET is_open = IF(`open` < DATE_ADD(NOW(), INTERVAL 30 DAY) AND `close` > DATE_ADD(NOW(), INTERVAL -30 DAY), 1, 0);
	}
}
?>
