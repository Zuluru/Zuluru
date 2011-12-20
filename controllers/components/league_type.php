<?php
/**
 * Base class for league-specific functionality.  This class defines default
 * no-op functions for all operations that leagues might need to do, as well
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
	 * @param mixed $league Array containing the league data
	 * @param mixed $is_coordinator Indication of whether the user is a coordinator of this league
	 *
	 */
	function addMenuItems($league, $is_coordinator = false)	{
	}

	/**
	 * Sort the provided teams according to league-specific criteria.
	 * This default function is usually going to be good enough, but we put it
	 * here instead of having other code call usort directly, just in case.
	 *
	 * @param mixed $league League to sort (teams are in ['Team'] key)
	 *
	 */
	function sort(&$league, $include_tournament = true) {
		$this->presort ($league);
		if ($include_tournament) {
			usort ($league['Team'], array($this, 'compareTeamsTournament'));
		} else {
			usort ($league['Team'], array($this, 'compareTeams'));
		}
	}

	/**
	 * Do any calculations that will make the comparisons more efficient, such
	 * as determining wins, losses, spirit, etc.
	 * 
	 * @param mixed $league League to perform calculations on
	 *
	 */
	function presort(&$league) {
		if (array_key_exists ('Game', $league)) {
			$season = $tournament = array();
			foreach ($league['Game'] as $game) {
				// Different read methods create arrays in different formats
				if (array_key_exists ('Game', $game)) {
					$result = $game['Game'];
				} else {
					$result = $game;
				}

				if ($result['tournament']) {
					$this->addTournamentResult ($tournament, $result['home_team'], $result['away_team'],
						$result['round'], $result['home_score'], $result['away_score']);
				} else {
					if (Game::_is_finalized($game)) {
						$this->addGameResult ($season, $result['home_team'], $result['away_team'],
								$result['round'], $result['home_score'], $result['away_score'],
								Game::_get_spirit_entry ($game, $result['home_team']),
								$result['status'] == 'home_default');
						$this->addGameResult ($season, $result['away_team'], $result['home_team'],
								$result['round'], $result['away_score'], $result['home_score'],
								Game::_get_spirit_entry ($game, $result['away_team']),
								$result['status'] == 'away_default');
					}
				}
			}

			foreach ($league['Team'] as $key => $team) {
				if (array_key_exists ($team['id'], $season)) {
					$league['Team'][$key]['results'] = $season[$team['id']];
				} else {
					$league['Team'][$key]['results'] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0,
							'gf' => 0, 'ga' => 0, 'str' => 0, 'str_type' => '', 'spirit' => 0,
							'rounds' => array(), 'vs' => array(), 'vspm' => array());
				}

				if (array_key_exists ($team['id'], $tournament)) {
					$league['Team'][$key]['tournament'] = $tournament[$team['id']];
				} else {
					$league['Team'][$key]['tournament'] = array();
				}
			}
		}
	}

	function addGameResult (&$results, $team, $opp, $round, $score_for, $score_against, $spirit_for, $default) {
		// What type of result was this?
		if ($score_for > $score_against) {
			$type = 'W';
			// TODO: points for wins, losses and ties configurable?
			$points = 2;
		} else if ($score_for < $score_against) {
			$type = 'L';
			$points = 0;
		} else {
			$type = 'T';
			$points = 1;
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
			$results[$team]['rounds'][$round] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'gf' => 0, 'ga' => 0);
		}

		// Make sure a record exists for the opponent in the vs arrays
		if (! array_key_exists ($opp, $results[$team]['vs'])) {
			$results[$team]['vs'][$opp] = 0;
			$results[$team]['vspm'][$opp] = 0;
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
		$results[$team]['gf'] += $score_for;
		$results[$team]['rounds'][$round]['gf'] += $score_for;
		$results[$team]['ga'] += $score_against;
		$results[$team]['rounds'][$round]['ga'] += $score_against;
		// TODO: drop high and low spirit?
		if (is_array ($spirit_for) && array_key_exists ('entered_sotg', $spirit_for)) {
			$results[$team]['spirit'] += $spirit_for['entered_sotg'];
		}
		$results[$team]['vs'][$opp] += $points;
		$results[$team]['vspm'][$opp] += $score_for - $score_against;

		// Add to the current streak, or reset it
		if ($type == $results[$team]['str_type']) {
			++ $results[$team]['str'];
		} else {
			$results[$team]['str_type'] = $type;
			$results[$team]['str'] = 1;
		}
	}

	function addTournamentResult (&$results, $team, $opp, $round, $score_for, $score_against) {
		// Make sure the team records exist in the results
		if (! array_key_exists ($team, $results)) {
			$results[$team] = array();
		}
		if (! array_key_exists ($opp, $results)) {
			$results[$opp] = array();
		}

		// What type of result was this?
		if ($score_for > $score_against) {
			$results[$team][$round] = 1;
			$results[$opp][$round] = -1;
		} else if ($score_for < $score_against) {
			$results[$team][$round] = -1;
			$results[$opp][$round] = 1;
		} else {
			$results[$team][$round] = $results[$opp][$round] = 0;
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

		// Go through each tournament round and compare the two teams' results in that round
		$rounds = array_unique(array_merge(array_keys($a['tournament']), array_keys($b['tournament'])));
		sort($rounds);
		foreach ($rounds as $round) {
			// If the first team had a bye in this round and the second team lost,
			// put the first team ahead
			if (!array_key_exists($round, $a['tournament']) && $b['tournament'][$round] < 0) {
				return -1;
			}

			// If the second team had a bye in this round and the first team lost,
			// put the second team ahead
			if (!array_key_exists($round, $a['tournament']) && $b['tournament'][$round] < 0) {
				return 1;
			}

			// If both teams played in this round and had different results,
			// use that result to determine who is ahead
			if (array_key_exists($round, $a['tournament']) && array_key_exists($round, $b['tournament']) &&
				$a['tournament'][$round] != $b['tournament'][$round])
			{
				return ($a['tournament'][$round] > $b['tournament'][$round] ? -1 : 1);
			}
		}

		return $this->compareTeams($a, $b);
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
	 * Returns the list of options for scheduling games in this type of league.
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

	/**
	 * Load everything required for scheduling.
	 */
	function startSchedule($league_id, $exclude_teams, $start_date) {
		$this->games = array();

		$regions = Configure::read('feature.region_preference');
		if ($regions) {
			$field_contain = array('Field' => 'ParentField');
		} else {
			$field_contain = array();
		}
		$this->_controller->League->contain (array (
			'Team' => array(
				'order' => 'Team.name',
				'conditions' => array('NOT' => array('id' => $exclude_teams)),
			),
			'Game' => array(
				'GameSlot' => $field_contain,
			),
			'LeagueGameslotAvailability' => array(
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
		$this->league = $this->_controller->League->read(null, $league_id);
		if ($this->league === false) {
			$this->_controller->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'warning'));
			return false;
		}

		// Go through all the games and count the number of home and away games
		// and games within preferred region for each team
		$this->home_games = $this->away_games = $this->preferred_games = array();
		foreach ($this->league['Game'] as $game) {
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
				$team = array_pop (Set::extract ("/Team[id={$game['home_team']}]/.", $this->league));
				if ($team['region_preference'] && $team['region_preference'] == $game['GameSlot']['Field']['region_id']) {
					if (!array_key_exists ($game['home_team'], $this->preferred_games)) {
						$this->preferred_games[$game['home_team']] = 1;
					} else {
						++ $this->preferred_games[$game['home_team']];
					}
				}

				$team = array_pop (Set::extract ("/Team[id={$game['away_team']}]/.", $this->league));
				if ($team['region_preference'] && $team['region_preference'] == $game['GameSlot']['Field']['region_id']) {
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

	function finishSchedule($league_id, $publish) {
		if (empty ($this->games)) {
			return false;
		}

		// Add the publish flag and league id to every game
		foreach (array_keys($this->games) as $i) {
			$this->games[$i]['league_id'] = $league_id;
			$this->games[$i]['published'] = $publish;
			if (!array_key_exists ('round', $this->games[$i])) {
				$this->games[$i]['round'] = $this->league['League']['current_round'];
			}
		}

		// Check that chosen game slots didn't somehow get allocated elsewhere in the meantime
		$slots = Set::extract ('/GameSlot/id', $this->games);
		$this->_controller->League->Game->GameSlot->recursive = -1;
		$taken = $this->_controller->League->Game->GameSlot->find('all', array('conditions' => array(
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
		$transaction = new DatabaseTransaction($this->_controller->League->Game);

		// for($x as $k => $v) works on a cached version of $x, so any changes
		// to the games made in beforeSave or afterSave will show up in
		// $this->games but not in the game variables as we iterate through.
		// So, iterate over the array keys instead and use that to directly
		// reference the array.
		foreach (array_keys($this->games) as $key) {
			$this->_controller->League->Game->create();
			if (!$this->beforeSave($key) ||
				!$this->_controller->League->Game->save($this->games[$key]) ||
				!$this->afterSave($key))
			{
				return false;
			}

			$this->games[$key]['GameSlot']['game_id'] = $this->_controller->League->Game->id;
			if (!$this->_controller->League->Game->GameSlot->save($this->games[$key]['GameSlot'])) {
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
	 * Create a single game in this league
	 */
	function createEmptyGame($date) {
		$num_teams = count($this->league['Team']);

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
	 * @return boolean indication of success
	 *
	 */
	function assignFields($date, $teams) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = array();

		// Iterate over teams array pairwise and create games with balanced home/away
		for($team_idx = 0; $team_idx < count($teams); $team_idx += 2) {
			$games[] = $this->addTeamsBalanced($teams[$team_idx], $teams[$team_idx + 1]);
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		if (!$this->assignFieldsByPreferences($date, $games)) {
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
	function assignFieldsByPreferences($date, $games) {
		/*
		 * We sort by ratio of getting their preference, from lowest to
		 * highest, so that teams who received their field preference least
		 * will have a better chance of it.
		 */
		AppModel::_reindexInner($this->league, 'Team', 'id');
		usort($games, array($this, 'comparePreferredFieldRatio'));

		while($game = array_shift($games)) {
			$slot_id = $this->selectWeightedGameslot($game, $date);
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
		return ($this->league['Team'][$game['home_team']]['home_field'] ||
			$this->league['Team'][$game['home_team']]['home_field']);
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
		if ($this->league['Team'][$game['home_team']]['home_field']) {
			$id = $game['away_team'];
		} else {
			$id = $game['home_team'];
		}

		// No preference means they're always happy.  We return over 100% to
		// force them to sort last when ordering by ratio, so that teams with
		// a preference always appear before them.
		if (empty($this->league['Team'][$id]['region_preference'])) {
			return 2;
		}

		if (!array_key_exists('preferred_ratio', $this->league['Team'][$id])) {
			if (!array_key_exists($id, $this->preferred_games)) {
				$this->league['Team'][$id]['preferred_ratio'] = 0;
			} else {
				$this->league['Team'][$id]['preferred_ratio'] = $this->preferred_games[$id] /
					// We've already incremented these counters with the new game
					// before arriving here, so we subtract 1 to get the true count
					($this->home_games[$id] + $this->away_games[$id] - 1);
			}
		}
		return $this->league['Team'][$id]['preferred_ratio'];
	}
	
	/**
	 * Select a random gameslot
	 *
	 * @param mixed $date The date of the game
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectRandomGameslot($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/id", $this->league);
		if (empty ($slots)) {
			$this->_controller->Session->setFlash(__('Couldn\'t get a slot ID', true), 'default', array('class' => 'warning'));
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
	 * Gameslot is to be selected from those available for the league in which
	 * this game exists.
	 *
	 * TODO: Take field quality into account when assigning.  Easiest way
	 * to do this would be to order by field quality instead of RAND(),
	 * keeping our best fields in use.
	 *
	 * @param mixed $game Array of game details (e.g. home_team, away_team)
	 * @param mixed $date The date of the game
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectWeightedGameslot($game, $date)
	{
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$slots = array();

		$home = $this->league['Team'][$game['home_team']];
		$away = $this->league['Team'][$game['away_team']];

		// Try to adhere to the home team's home field
		if ($home['home_field']) {
			$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date][field_id={$home['home_field']}]/id", $this->league);
		}

		// If not available, try the away team's home field
		if (empty ($slots) && $away['home_field']) {
			$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date][field_id={$away['home_field']}]/id", $this->league);
		}

		// Maybe try region preferences
		if (Configure::read('feature.region_preference')) {
			if (empty ($slots) && $home['region_preference']) {
				// TODO: Test this once fields are fixed
				$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/Field[region_id={$home['region_preference']}]/..", $this->league);
				$slots = Set::extract('/GameSlot/id', $slots);
			}

			if (empty ($slots) && $away['region_preference']) {
				// TODO: Test this once fields are fixed
				$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/Field[region_id={$away['region_preference']}]/..", $this->league);
				$slots = Set::extract('/GameSlot/id', $slots);
			}
		}

		// If still nothing can be found, last try is just random
		if (empty ($slots)) {
			return $this->selectRandomGameslot($date);
		}

		shuffle ($slots);
		$slot_id = $slots[0];
		$this->removeGameslot($slot_id);
		return $slot_id;
	}

	/**
	 * Remove a slot from the list of those available
	 *
	 * @param mixed $slot_id Id of the slot to remove
	 *
	 */
	function removeGameslot($slot_id) {
		foreach ($this->league['LeagueGameslotAvailability'] as $key => $slot) {
			if ($slot['game_slot_id'] == $slot_id) {
				unset ($this->league['LeagueGameslotAvailability'][$key]);
			}
		}
	}

	/**
	 * Count how many distinct gameslot days are availabe from $date onwards
	 *
	 */
	function countAvailableGameslotDays($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$dates = array_unique (Set::extract("/LeagueGameslotAvailability/GameSlot[game_date>=$date]/game_date", $this->league));
		return count($dates);
	}

	/**
	 * Return next available day of play after $date, based on gameslot availability
	 *
	 * value returned is a UNIX timestamp for the game day.
	 */
	function nextGameslotDay($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$dates = array_unique (Set::extract("/LeagueGameslotAvailability/GameSlot[game_date>$date]/game_date", $this->league));
		return min($dates);
	}

	/**
	 * Calculate the ELO change for the result provided.
	 *
	 * This uses a modified Elo system, similar to the one used for
	 * international soccer (http://www.eloratings.net) with several
	 * modifications:
	 * 	- all games are equally weighted
	 * 	- score differential bonus adjusted for Ultimate patterns (ie: a 3
	 * 	  point win in soccer is a much bigger deal than in Ultimate)
	 * 	- no bonus given for home-field advantage
	 */
	function calculateRatingsChange($home_score, $away_score, $expected_win) {
		$weight_constant = 40;	// All games weighted equally
		$score_weight = 1;		// Games start with a weight of 1

		$game_value = 1;		// Game value is always 1 or 0.5 as we're calculating the elo change for the winning team

		// Find winning/losing scores.  In the case of a tie,
		// the home team is considered the winner for purposes of
		// rating calculation.  This has nothing to do with the
		// tiebreakers used for standings purposes as in tie cases,
		// the $elo_change will work out the same regardless of which team is
		// considered the 'winner'
		if( $home_score == $away_score) {
			// For a tie, we assume the home team wins, but give the game a
			// value of 0.5
			$game_value = 0.5;
		}

		// Calculate score differential bonus.
		// If the difference is greater than 1/3 the winning score, the bonus
		// added is the ratio of score difference over winning score.
		$score_diff = abs($home_score - $away_score);
		$score_max  = max($home_score, $away_score);
		if( $score_max && ( ($score_diff / $score_max) > (1/3) )) {
			$score_weight += $score_diff / $score_max;
		}

		$elo_change = $weight_constant * $score_weight * ($game_value - $expected_win);
		return ceil($elo_change);
	}

}

?>
