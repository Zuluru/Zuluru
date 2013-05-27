<?php
/**
 * Base class for division-specific functionality.  This class defines default
 * no-op functions for all operations that divisions might need to do, as well
 * as providing some common utility functions that derived classes need.
 */

class LeagueTypeComponent extends Object
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'rounds';

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	/**
	 * Add any league-type-specific options to the menu.
	 * By default, there are no extra menu options.
	 *
	 * @param mixed $division Array containing the division data
	 * @param mixed $is_coordinator Indication of whether the user is a coordinator of this division
	 *
	 */
	function addMenuItems($division, $path, $is_coordinator = false) {
	}

	/**
	 * Sort the provided teams according to division-specific criteria.
	 * This default function is usually going to be good enough, but we put it
	 * here instead of having other code call usort directly, just in case.
	 *
	 * @param mixed $division Division to sort (teams are in ['Team'] key)
	 *
	 */
	function sort(&$division, $spirit_obj = null, $include_tournament = true) {
		$this->presort ($division, $spirit_obj);
		if ($include_tournament) {
			usort ($division['Team'], array($this, 'compareTeamsTournament'));
		} else {
			usort ($division['Team'], array($this, 'compareTeams'));
		}
	}

	/**
	 * Do any calculations that will make the comparisons more efficient, such
	 * as determining wins, losses, spirit, etc.
	 * 
	 * @param mixed $division Division to perform calculations on
	 *
	 */
	function presort(&$division, $spirit_obj) {
		$this->division = $division;
		if (array_key_exists ('Game', $division)) {
			$season = $tournament = array();
			foreach ($division['Game'] as $game) {
				// Different read methods create arrays in different formats
				if (array_key_exists ('Game', $game)) {
					$result = $game['Game'];
				} else {
					$result = $game;
				}

				if ($result['tournament']) {
					$this->addTournamentResult ($tournament, $result['home_team'], $result['away_team'],
						$result['tournament_pool'], $result['name'], $result['round'], $result['home_score'], $result['away_score']);
				} else if (Game::_is_finalized($game) && !in_array($result['status'], Configure::read('unplayed_status'))) {
					$this->addGameResult ($division, $season, $result['home_team'], $result['away_team'],
							$result['round'], $result['home_score'], $result['away_score'],
							Game::_get_spirit_entry ($game, $result['home_team']), $spirit_obj,
							$result['status'] == 'home_default');
					$this->addGameResult ($division, $season, $result['away_team'], $result['home_team'],
							$result['round'], $result['away_score'], $result['home_score'],
							Game::_get_spirit_entry ($game, $result['away_team']), $spirit_obj,
							$result['status'] == 'away_default');
				}
			}

			foreach ($division['Team'] as $key => $team) {
				if (array_key_exists ($team['id'], $season)) {
					$division['Team'][$key]['results'] = $season[$team['id']];
				} else {
					$division['Team'][$key]['results'] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0,
							'gf' => 0, 'ga' => 0, 'str' => 0, 'str_type' => '', 'spirit' => 0,
							'rounds' => array(), 'vs' => array(), 'vspm' => array());
				}

				if (array_key_exists ($team['id'], $tournament)) {
					$division['Team'][$key]['tournament'] = $tournament[$team['id']];
				} else {
					$division['Team'][$key]['tournament'] = array('pool' => 999, 'results' => array(), 'final' => null);
				}
			}
		}
	}

	function addGameResult ($division, &$results, $team, $opp, $round, $score_for, $score_against, $spirit_for, $spirit_obj, $default) {
		if (!isset($this->sport_obj)) {
			$this->sport_obj = $this->_controller->_getComponent ('Sport', $division['League']['sport'], $this->_controller);
		}

		// What type of result was this?
		if ($score_for > $score_against) {
			$type = 'W';
			$points = $this->sport_obj->winValue();
		} else if ($score_for < $score_against) {
			$type = 'L';
			$points = $this->sport_obj->lossValue();
		} else {
			$type = 'T';
			$points = $this->sport_obj->tieValue();
		}

		// Make sure the team record exists in the results
		if (! array_key_exists ($team, $results)) {
			$results[$team] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0,
									'gf' => 0, 'ga' => 0, 'str' => 0, 'str_type' => '', 'spirit' => 0,
									'rounds' => array(), 'vs' => array(), 'vspm' => array());
		}

		// Make sure a record exists for the round in the results
		// Some league types don't use rounds, but there's no real harm in calculating this
		if (! array_key_exists ($round, $results[$team]['rounds'])) {
			$results[$team]['rounds'][$round] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'gf' => 0, 'ga' => 0, 'vs' => array(), 'vspm' => array());
		}

		// Make sure a record exists for the opponent in the vs arrays
		if (! array_key_exists ($opp, $results[$team]['vs'])) {
			$results[$team]['vs'][$opp] = 0;
			$results[$team]['vspm'][$opp] = 0;
		}
		if (! array_key_exists ($opp, $results[$team]['rounds'][$round]['vs'])) {
			$results[$team]['rounds'][$round]['vs'][$opp] = 0;
			$results[$team]['rounds'][$round]['vspm'][$opp] = 0;
		}

		if ($default) {
			++ $results[$team]['def'];
			++ $results[$team]['rounds'][$round]['def'];
			-- $points;
		}

		// Add the current game
		++ $results[$team]['games'];
		++ $results[$team][$type];
		++ $results[$team]['rounds'][$round][$type];
		$results[$team]['pts'] += $points;
		$results[$team]['rounds'][$round]['pts'] += $points;
		$results[$team]['gf'] += $score_for;
		$results[$team]['rounds'][$round]['gf'] += $score_for;
		$results[$team]['ga'] += $score_against;
		$results[$team]['rounds'][$round]['ga'] += $score_against;

		// TODO: drop high and low spirit?
		if ($spirit_obj) {
			if (is_array ($spirit_for)) {
				if (!$division['League']['numeric_sotg']) {
					$results[$team]['spirit'] += $spirit_obj->calculate($spirit_for);
				} else {
					$results[$team]['spirit'] += $spirit_for['entered_sotg'];
				}
			}
		}

		$results[$team]['vs'][$opp] += $points;
		$results[$team]['rounds'][$round]['vs'][$opp] += $points;
		$results[$team]['vspm'][$opp] += $score_for - $score_against;
		$results[$team]['rounds'][$round]['vspm'][$opp] += $score_for - $score_against;

		// Add to the current streak, or reset it
		if ($type == $results[$team]['str_type']) {
			++ $results[$team]['str'];
		} else {
			$results[$team]['str_type'] = $type;
			$results[$team]['str'] = 1;
		}
	}

	function addTournamentResult (&$results, $team, $opp, $pool, $name, $round, $score_for, $score_against) {
		// Make sure the team records exist in the results
		if (! array_key_exists ($team, $results)) {
			$results[$team] = array('pool' => $pool, 'results' => array());
		}
		if (! array_key_exists ($opp, $results)) {
			$results[$opp] = array('pool' => $pool, 'results' => array());
		}

		// Check if this was a placement game
		$final_win = $final_lose = null;
		$suffix = substr($name, -2);
		if (in_array($suffix, array('st', 'nd', 'rd', 'th'))) {
			$name = substr($name, 0, -2);
			while (true) {
				$x = substr($name, -1);
				$name = substr($name, 0, -1);
				if (is_numeric($x)) {
					$final_win = "$x$final_win";
				} else {
					$final_lose = $final_win + 1;
					break;
				}
			}
		}

		// What type of result was this?
		if ($score_for > $score_against) {
			$results[$team]['results'][$round] = 1;
			$results[$team]['final'] = $final_win;

			$results[$opp]['results'][$round] = -1;
			$results[$opp]['final'] = $final_lose;
		} else if ($score_for < $score_against) {
			$results[$team]['results'][$round] = -1;
			$results[$team]['final'] = $final_lose;

			$results[$opp]['results'][$round] = 1;
			$results[$opp]['final'] = $final_win;
		} else {
			$results[$team]['results'][$round] = $results[$opp]['results'][$round] = 0;
			$results[$team]['final'] = $results[$opp]['final'] = $final_win;
		}
	}

	/**
	 * By default, we just sort by name.
	 */
	function compareTeams($a, $b) {
		return (strtolower ($a['name']) > strtolower ($b['name']));
	}

	/**
	 * Various league types might have tournaments.
	 */
	function compareTeamsTournament($a, $b) {
		if (!array_key_exists('tournament', $a) || !array_key_exists('tournament', $b)) {
			return $this->compareTeams($a, $b);
		}

		// If both teams have final placements, we use that
		if ($a['tournament']['final'] !== null && $b['tournament']['final'] !== null) {
			if ($a['tournament']['final'] > $b['tournament']['final']) {
				return 1;
			} else if ($a['tournament']['final'] < $b['tournament']['final']) {
				return -1;
			}
		}

		// If teams are not in the same pool, we use that
		if ($a['tournament']['pool'] < $b['tournament']['pool']) {
			return -1;
		} else if ($a['tournament']['pool'] > $b['tournament']['pool']) {
			return 1;
		}

		// Go through each tournament round and compare the two teams' results in that round
		$rounds = array_unique(array_merge(array_keys($a['tournament']['results']), array_keys($b['tournament']['results'])));
		sort($rounds);
		foreach ($rounds as $round) {
			// If the first team had a bye in this round and the second team lost,
			// put the first team ahead
			if (!array_key_exists($round, $a['tournament']['results']) && $b['tournament']['results'][$round] < 0) {
				return -1;
			}

			// If the second team had a bye in this round and the first team lost,
			// put the second team ahead
			if (!array_key_exists($round, $a['tournament']['results']) && $b['tournament']['results'][$round] < 0) {
				return 1;
			}

			// If both teams played in this round and had different results,
			// use that result to determine who is ahead
			if (array_key_exists($round, $a['tournament']['results']) && array_key_exists($round, $b['tournament']['results']) &&
				$a['tournament']['results'][$round] != $b['tournament']['results'][$round])
			{
				return ($a['tournament']['results'][$round] > $b['tournament']['results'][$round] ? -1 : 1);
			}
		}

		return $this->compareTeams($a, $b);
	}

	/**
	 * Sort based on configured list of tie-breakers
	 */
	function compareTeamsTieBreakers($a, $b) {
		if (array_key_exists ('results', $a))
		{
			$round = $this->division['Division']['current_round'];
			if ($round != 1) {
				if (array_key_exists($round, $a['results']['rounds'])) {
					$a_results = $a['results']['rounds'][$round];
				} else {
					$a_results = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'gf' => 0, 'ga' => 0, 'vs' => array(), 'vspm' => array());
				}
				if (array_key_exists($round, $b['results']['rounds'])) {
					$b_results = $b['results']['rounds'][$round];
				} else {
					$b_results = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'gf' => 0, 'ga' => 0, 'vs' => array(), 'vspm' => array());
				}
			} else {
				$a_results = $a['results'];
				$b_results = $b['results'];
			}

			if ($a_results['pts'] < $b_results['pts'])
				return 1;
			if ($a_results['pts'] > $b_results['pts'])
				return -1;

			if ($a_results['W'] < $b_results['W'])
				return 1;
			if ($a_results['W'] > $b_results['W'])
				return -1;

			$order = Configure::read("tie_breakers.{$this->division['League']['tie_breaker']}");
			foreach ($order as $option) {
				switch ($option) {
					case 'hth':
						if (array_key_exists ($b['id'], $a_results['vs'])) {
							// if b is in a's results, a must also exist in b's results, no point checking that
							if ($a_results['vs'][$b['id']] < $b_results['vs'][$a['id']])
								return 1;
							if ($a_results['vs'][$b['id']] > $b_results['vs'][$a['id']])
								return -1;
						}
						break;

					case 'hthpm':
						if (array_key_exists ($b['id'], $a_results['vspm'])) {
							// if b is in a's results, a must also exist in b's results, no point checking that
							if ($a_results['vspm'][$b['id']] < $b_results['vspm'][$a['id']])
								return 1;
							if ($a_results['vspm'][$b['id']] > $b_results['vspm'][$a['id']])
								return -1;
						}
						break;

					case 'pm':
						if ($a_results['gf'] - $a_results['ga'] < $b_results['gf'] - $b_results['ga'])
							return 1;
						if ($a_results['gf'] - $a_results['ga'] > $b_results['gf'] - $b_results['ga'])
							return -1;
						break;

					case 'gf':
						if ($a_results['gf'] < $b_results['gf'])
							return 1;
						if ($a_results['gf'] > $b_results['gf'])
							return -1;
						break;

					case 'loss':
						if ($a_results['L'] > $b_results['L'])
							return 1;
						if ($a_results['L'] < $b_results['L'])
							return -1;
						break;

					case 'spirit':
						if ($a['results']['spirit'] - $a['results']['games'] < $b['results']['spirit'] - $b['results']['games'])
							return 1;
						if ($a['results']['spirit'] - $a['results']['games'] > $b['results']['spirit'] - $b['results']['games'])
							return -1;
						break;
				}
			}
		}

		return 0;
	}

	/**
	 * Generate a list of extra league-type-specific edit/display fields, as
	 * field => details pairs.  Details are arrays with keys like label (mandatory)
	 * and any options to be passed to the html->input call.
	 * Titles are in English, and will be translated in the view.
	 * By default, there are no extra fields.
	 *
	 * @return mixed An array containing the extra fields
	 *
	 */
	function schedulingFields($is_admin, $is_coordinator) {
		return array();
	}

	/**
	 * Return entries for validation of any league-type-specific edit fields.
	 *
	 * @return mixed An array containing items to be added to the validation array.
	 *
	 */
	function schedulingFieldsValidation() {
		return array();
	}

	/**
	 * Returns the list of options for scheduling games in this type of division.
	 *
	 * @return mixed An array containing the list of scheduling options.
	 */
	function scheduleOptions($num_teams) {
		return array();
	}

	/**
	 * Get the description of a scheduling type.
	 *
	 * @param mixed $type The scheduling type to return the description of
	 * @param mixed $teams The number of teams to include in the description, or false to return a short description
	 * @return mixed The description
	 *
	 */
	function scheduleDescription($type, $teams = false) {
		$types = $this->scheduleOptions($teams);
		$desc = $types[$type];
		if ($teams === false) {
			$pos = strpos ($desc, '(');
			if ($pos !== false) {
				$desc = substr ($desc, 0, $pos);
			}
			$desc = trim ($desc);
		}
		return $desc;
	}

	/**
	 * Return the requirements of a particular scheduling type.  This is
	 * just a default stub, overloaded by specific algorithms.
	 *
	 * @param mixed $num_teams The number of teams to schedule for
	 * @param mixed $type The schedule type
	 * @return mixed An array with the number of fields needed each day
	 *
	 */
	function scheduleRequirements($type, $num_teams) {
		return array();
	}

	function canSchedule($num_fields, $field_counts) {
		foreach ($num_fields as $required) {
			while ($required > 0) {
				if (empty($field_counts)) {
					$this->_controller->Session->setFlash(sprintf(__('There are insufficient %s available to support the requested schedule.', true), Configure::read('sport.fields')), 'default', array('class' => 'info'));
					return false;
				}
				$field_count = array_shift($field_counts);
				$required -= $field_count[0]['count'];
			}
		}

		return true;
	}

	/**
	 * Load everything required for scheduling.
	 */
	function startSchedule($division_id, $exclude_teams, $start_date) {
		$this->games = array();

		$regions = Configure::read('feature.region_preference');
		if ($regions) {
			$field_contain = array('Field' => array('Facility' => 'Region'));
		} else {
			$field_contain = array();
		}
		$this->_controller->Division->contain (array (
			'Day',
			'Team' => array(
				'order' => 'Team.name',
				'conditions' => array('NOT' => array('id' => $exclude_teams)),
			),
			'League',
			'Game' => array(
				'GameSlot' => $field_contain,
			),
			'DivisionGameslotAvailability' => array(
				'GameSlot' => array(
					// This will still return all of the Availability records, but many will have
					// empty GameSlot arrays, so Set::Extract calls won't match and they're ignored
					// TODO: Can a better query improve the efficiency of this?
					'conditions' => array(
						'game_date >=' => $start_date,
						'game_id' => null,
					),
				),
			),
		));
		$this->division = $this->_controller->Division->read(null, $division_id);
		if ($this->division === false) {
			$this->_controller->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'warning'));
			return false;
		}

		// Go through all the games and count the number of home and away games
		// and games within preferred region for each team
		$this->home_games = $this->away_games = $this->preferred_games = array();
		foreach ($this->division['Game'] as $game) {
			if (!array_key_exists ($game['home_team'], $this->home_games)) {
				$this->home_games[$game['home_team']] = 1;
			} else {
				++ $this->home_games[$game['home_team']];
			}

			if (!array_key_exists ($game['away_team'], $this->away_games)) {
				$this->away_games[$game['away_team']] = 1;
			} else {
				++ $this->away_games[$game['away_team']];
			}

			if ($regions) {
				$team = array_pop (Set::extract ("/Team[id={$game['home_team']}]/.", $this->division));
				if ($team['region_preference'] && $team['region_preference'] == $game['GameSlot']['Field']['Facility']['region_id']) {
					if (!array_key_exists ($game['home_team'], $this->preferred_games)) {
						$this->preferred_games[$game['home_team']] = 1;
					} else {
						++ $this->preferred_games[$game['home_team']];
					}
				}

				$team = array_pop (Set::extract ("/Team[id={$game['away_team']}]/.", $this->division));
				if ($team['region_preference'] && $team['region_preference'] == $game['GameSlot']['Field']['Facility']['region_id']) {
					if (!array_key_exists ($game['away_team'], $this->preferred_games)) {
						$this->preferred_games[$game['away_team']] = 1;
					} else {
						++ $this->preferred_games[$game['away_team']];
					}
				}
			}
		}

		return true;
	}

	function finishSchedule($division_id, $publish) {
		if (empty ($this->games)) {
			return false;
		}

		// Add the publish flag and division id to every game
		foreach (array_keys($this->games) as $i) {
			$this->games[$i]['division_id'] = $division_id;
			$this->games[$i]['published'] = $publish;
			if (!array_key_exists ('round', $this->games[$i])) {
				$this->games[$i]['round'] = $this->division['Division']['current_round'];
			}
		}

		// Check that chosen game slots didn't somehow get allocated elsewhere in the meantime
		$slots = Set::extract ('/GameSlot/id', $this->games);
		$this->_controller->Division->Game->GameSlot->contain();
		$taken = $this->_controller->Division->Game->GameSlot->find('all', array('conditions' => array(
				'id' => $slots,
				'game_id !=' => null,
		)));
		if (!empty ($taken)) {
			$this->_controller->Session->setFlash(__('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true), 'default', array('class' => 'warning'));
			return false;
		}

		// saveAll doesn't save GameSlot records here (the hasOne relation
		// indicates to Cake that slots are supposed to be created for games,
		// rather than being created ahead of time and assigned to games).
		// So, we replicate the important bits of saveAll here.
		$transaction = new DatabaseTransaction($this->_controller->Division->Game);

		// for($x as $k => $v) works on a cached version of $x, so any changes
		// to the games made in beforeSave or afterSave will show up in
		// $this->games but not in the game variables as we iterate through.
		// So, iterate over the array keys instead and use that to directly
		// reference the array.
		foreach (array_keys($this->games) as $key) {
			$this->_controller->Division->Game->create();
			if (!$this->beforeSave($key) ||
				!$this->_controller->Division->Game->save($this->games[$key]) ||
				!$this->afterSave($key))
			{
				return false;
			}

			$this->games[$key]['GameSlot']['game_id'] = $this->_controller->Division->Game->id;
			if (!$this->_controller->Division->Game->GameSlot->save($this->games[$key]['GameSlot'])) {
				return false;
			}
		}

		return ($transaction->commit() !== false);
	}

	function beforeSave($game) {
		// Most league types have nothing that keeps games from being saved
		return true;
	}

	function afterSave($game) {
		// Most league types have nothing to do after games are saved
		return true;
	}

	/**
	 * Create a single game in this division
	 */
	function createEmptyGame($date) {
		$num_teams = count($this->division['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		$game_slot_id = $this->selectRandomGameslot($date);
		if ($game_slot_id === false) {
			return false;
		}

		// TODO: 'GameSlot' can't be the first key, or else Model::set uses it as the
		// parameter to getAssociated and the return value isn't null. Report as a bug
		// in CakePHP?
		$this->games[] = array(
			'home_team' => null,
			'away_team' => null,
			'GameSlot' => array(
				'id' => $game_slot_id,
			),
		);

		return true;
	}

	/**
	 * Schedule one set of games, using weighted field assignment
	 *
	 * @param mixed $date The date of the games
	 * @param mixed $teams List of teams, sorted into pairs by matchup
	 * @param mixed $remaining The number of other games still to be scheduled after this set
	 * @return boolean indication of success
	 *
	 */
	function assignFields($date, $teams, $remaining = 0) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = array();

		// Iterate over teams array pairwise and create games with balanced home/away
		for($team_idx = 0; $team_idx < count($teams); $team_idx += 2) {
			$games[] = $this->addTeamsBalanced($teams[$team_idx], $teams[$team_idx + 1]);
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		if (!$this->assignFieldsByPreferences($date, $games, $remaining)) {
			return false;
		}

		return true;
	}

	/**
	 * Add two opponents to a game, attempting to balance the number of home
	 * and away games
	 */
	function addTeamsBalanced($a, $b) {
		$a_ratio = $this->homeAwayRatio($a['id']);
		$b_ratio = $this->homeAwayRatio($b['id']);

		// team with lowest ratio (fewer home games) gets to be home.
		if ($a_ratio < $b_ratio) {
			$home = $a;
			$away = $b;
		} elseif ($a_ratio > $b_ratio) {
			$home = $b;
			$away = $a;
		} else {
			// equal ratios... choose randomly.
			if (rand(0,1) > 0) {
				$home = $a;
				$away = $b;
			} else {
				$home = $b;
				$away = $a;
			}
		}

		if (!array_key_exists ($home['id'], $this->home_games)) {
			$this->home_games[$home['id']] = 0;
		}
		if (!array_key_exists ($away['id'], $this->away_games)) {
			$this->away_games[$away['id']] = 0;
		}

		++ $this->home_games[$home['id']];
		++ $this->away_games[$away['id']];

		return array(
			'home_team' => $home['id'],
			'away_team' => $away['id'],
		);
	}

	function homeAwayRatio($id) {
		if (array_key_exists ($id, $this->home_games)) {
			$home_games = $this->home_games[$id];
		} else {
			$home_games = 0;
		}

		if (array_key_exists ($id, $this->away_games)) {
			$away_games = $this->away_games[$id];
		} else {
			$away_games = 0;
		}

		if ($home_games + $away_games < 1) {
			// Avoid divide-by-zero
			return 0;
		}

		return ($home_games / ($home_games + $away_games));
	}

	/**
	 * Assign field based on home field or region preference.
	 *
	 * It uses the select_weighted_gameslot function, which first looks at home field
	 * designation, then at field region preferences.
	 *
	 * We first sort teams in order of their allocation preference ratio.  Teams
	 * with a low ratio get first crack at a desired location.
	 *
	 * Then, we allocate gameslots to games where the home team has a home field.
	 * This is necessary to prevent another team with a lower ratio from scooping
	 * another team's dedicated home field.
	 *
	 * Following this, we simply loop over all remaining games and call
	 * select_weighted_gameslot(), which takes region preference into account.
	 *
	 */
	function assignFieldsByPreferences($date, $games, $remaining = 0) {
		/*
		 * We sort by ratio of getting their preference, from lowest to
		 * highest, so that teams who received their field preference least
		 * will have a better chance of it.
		 */
		AppModel::_reindexInner($this->division, 'Team', 'id');
		usort($games, array($this, 'comparePreferredFieldRatio'));

		while($game = array_shift($games)) {
			$slot_id = $this->selectWeightedGameslot($game, $date, count($games) + 1 + $remaining);
			if (!$slot_id) {
				return false;
			}
			$game['GameSlot'] = array(
				'id' => $slot_id,
			);

			$this->games[] = $game;
		}

		return true;
	}

	function comparePreferredFieldRatio($a, $b) {
		// Put all those teams with a home field at the top of the list
		$a_home = $this->hasHomeField($a);
		$b_home = $this->hasHomeField($b);
		if ($a_home && !$b_home) {
			return -1;
		} else if (!$a_home && $b_home) {
			return 1;
		}

		$a_ratio = $this->preferredFieldRatio($a);
		$b_ratio = $this->preferredFieldRatio($b);
		if ($a_ratio == $b_ratio) {
			return 0;
		}

		return ($a_ratio > $b_ratio) ? 1 : -1;
	}

	function hasHomeField($game) {
		return (Configure::read('feature.home_field') && $this->division['Team'][$game['home_team']]['home_field']);
	}

	function preferredFieldRatio($game) {
		// If we're not using region preferences, that's like everyone
		// has 100% of their games in a preferred region.
		if (!Configure::read('feature.region_preference')) {
			return 1;
		}

		// We've already dealt with teams that have home fields. If we're
		// calling this function, then either both games being compared
		// involve a team with a home field, or neither does. So, if this
		// game has one, the other must also, in which case we want to look
		// to their opponents to break that tie. This tie-breaker will
		// only matter if multiple teams share a home field, but it doesn't
		// do any harm to include it in other situations.
		if (Configure::read('feature.home_field') && $this->division['Team'][$game['home_team']]['home_field']) {
			$id = $game['away_team'];
		} else {
			$id = $game['home_team'];
		}

		// No preference means they're always happy.  We return over 100% to
		// force them to sort last when ordering by ratio, so that teams with
		// a preference always appear before them.
		if (empty($this->division['Team'][$id]['region_preference'])) {
			return 2;
		}

		if (!array_key_exists('preferred_ratio', $this->division['Team'][$id])) {
			if (!array_key_exists($id, $this->preferred_games)) {
				$this->division['Team'][$id]['preferred_ratio'] = 0;
			} else {
				$this->division['Team'][$id]['preferred_ratio'] = $this->preferred_games[$id] /
					// We've already incremented these counters with the new game
					// before arriving here, so we subtract 1 to get the true count
					($this->home_games[$id] + $this->away_games[$id] - 1);
			}
		}
		return $this->division['Team'][$id]['preferred_ratio'];
	}

	/**
	 * Select a random gameslot
	 *
	 * @param mixed $date The possible dates of the game
	 * @param mixed $remaining The number of games still to be scheduled, including this one
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectRandomGameslot($dates, $remaining = 1) {
		if (!is_array($dates)) {
			$dates = array($dates);
		}

		$slots = array();
		foreach ($dates as $date) {
			if (is_numeric ($date)) {
				$date = date('Y-m-d', $date);
			}
			$slots = array_merge($slots, Set::extract("/DivisionGameslotAvailability/GameSlot[game_date=$date]/id", $this->division));
			if (count($slots) >= $remaining) {
				break;
			}
		}

		if (empty ($slots)) {
			App::import('Helper', 'Html');
			$html = new HtmlHelper();

			$this->_controller->Session->setFlash(sprintf(__('There are insufficient game slots available to complete this schedule. Check the %s for details.', true),
					$html->link(sprintf(__('%s Availability Report', true), __(Configure::read('sport.field_cap'), true)), array('controller' => 'divisions', 'action' => 'slots', 'division' => $this->division['Division']['id'], 'date' => $date))),
					'default', array('class' => 'warning'));
			return false;
		}

		shuffle ($slots);
		$slot_id = $slots[0];
		$this->removeGameslot($slot_id);
		return $slot_id;
	}

	/**
	 * Select an appropriate gameslot for this game.  "appropriate" takes
	 * field quality, home field designation, and field preferences into account.
	 * Gameslot is to be selected from those available for the division in which
	 * this game exists.
	 *
	 * TODO: Take field quality into account when assigning.  Easiest way
	 * to do this would be to order by field quality instead of RAND(),
	 * keeping our best fields in use.
	 *
	 * @param mixed $game Array of game details (e.g. home_team, away_team)
	 * @param mixed $date The date of the game
	 * @param mixed $remaining The number of games still to be scheduled, including this one
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectWeightedGameslot($game, $date, $remaining)
	{
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$slots = array();

		$home = $this->division['Team'][$game['home_team']];
		$away = $this->division['Team'][$game['away_team']];

		$days = Set::extract('/Day/id', $this->division);
		$match_dates = Game::_matchDates($date, $days);

		if (Configure::read('feature.home_field')) {
			// Try to adhere to the home team's home field
			if ($home['home_field']) {
				$slots = $this->matchingSlots("[field_id={$home['home_field']}]", 'id', $match_dates, $remaining);
			}

			// If not available, try the away team's home field
			if (empty ($slots) && $away['home_field']) {
				$slots = $this->matchingSlots("[field_id={$away['home_field']}]", 'id', $match_dates, $remaining);
			}
		}

		// Maybe try region preferences
		if (Configure::read('feature.region_preference')) {
			if (empty ($slots) && $home['region_preference']) {
				$slots = $this->matchingSlots("/Field/Facility[region_id={$home['region_preference']}]", '../..', $match_dates, $remaining);
				$slots = Set::extract('/GameSlot/id', $slots);
			}

			if (empty ($slots) && $away['region_preference']) {
				$slots = $this->matchingSlots("/Field/Facility[region_id={$away['region_preference']}]", '../..', $match_dates, $remaining);
				$slots = Set::extract('/GameSlot/id', $slots);
			}
		}

		// If still nothing can be found, last try is just random
		if (empty ($slots)) {
			return $this->selectRandomGameslot($match_dates, $remaining);
		}

		shuffle ($slots);
		$slot_id = $slots[0];
		$this->removeGameslot($slot_id);
		return $slot_id;
	}

	function matchingSlots($criteria, $path, $dates, $remaining) {
		$matches = array();
		foreach ($dates as $date) {
			$matches = array_merge($matches, Set::extract("/DivisionGameslotAvailability/GameSlot[game_date=$date]$criteria/$path", $this->division));
			if (count($matches) >= $remaining) {
				break;
			}
		}
		return $matches;
	}

	/**
	 * Remove a slot from the list of those available
	 *
	 * @param mixed $slot_id Id of the slot to remove
	 *
	 */
	function removeGameslot($slot_id) {
		foreach ($this->division['DivisionGameslotAvailability'] as $key => $slot) {
			if ($slot['game_slot_id'] == $slot_id) {
				unset ($this->division['DivisionGameslotAvailability'][$key]);
				return;
			}
		}
	}

	/**
	 * Count how many distinct gameslot days are availabe from $date onwards
	 *
	 */
	function countAvailableGameslotDays($date, $slots_per_day) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$dates = array_unique(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>=$date]/game_date", $this->division));
		sort($dates);

		$available = $slots = 0;
		foreach ($dates as $date) {
			$slots += count(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date=$date]", $this->division));
			if ($slots >= $slots_per_day) {
				++$available;
				$slots = 0;
			}
		}
		return $available;
	}

	/**
	 * Return next available day of play after $date, based on gameslot availability
	 *
	 * value returned is a UNIX timestamp for the game day.
	 */
	function nextGameslotDay($date, $skip) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}

		if (!$skip) {
			$dates = array_unique(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>$date]/game_date", $this->division));
			return min($dates);
		}

		$dates = array_unique(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>=$date]/game_date", $this->division));
		sort($dates);
		while ($skip > 0 && !empty($dates)) {
			$date = array_shift($dates);
			$skip -= count(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date=$date]", $this->division));
		}
		return array_shift($dates);
	}
}

?>
