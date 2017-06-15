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
	 * Add any league-type-specific fields for new teams.
	 *
	 */
	function newTeam() {
		return array();
	}

	/**
	 * Sort the provided teams according to division-specific criteria.
	 *
	 * @param mixed $division Division to sort (teams are in ['Team'] key)
	 *
	 */
	function sort(&$teams, &$division, $league, $games, $spirit_obj = null, $include_tournament = true) {
		if (!empty($games)) {
			$this->presort ($teams, $division, $league, $games, $spirit_obj);
		}
		$unseeded = Set::extract('/Team[seed=0]', array('Team' => $teams));
		if (empty($unseeded)) {
			usort ($teams, array($this, 'compareSeed'));
		} else if ($division['schedule_type'] == 'tournament' || $include_tournament) {
			usort ($teams, array($this, 'compareTeamsTournament'));
		} else {
			usort ($teams, array($this, 'compareTeams'));
			if (!empty($games)) {
				$this->detectAndResolveTies($teams, 'compareTeams');
			}
		}
	}

	/**
	 * Do any calculations that will make the comparisons more efficient, such
	 * as determining wins, losses, spirit, etc.
	 * 
	 * @param mixed $division Division to perform calculations on
	 *
	 */
	function presort(&$teams, &$division, $league, $games, $spirit_obj) {
		// Different read methods create arrays in different formats.
		// This puts them all in the same format. At the same time,
		// we split them into various groupings.
		$bracket_games = array();
		foreach ($games as $game) {
			if (array_key_exists ('Game', $game)) {
				$game = array_merge($game['Game'], $game);
				unset($game['Game']);
			}

			switch ($game['type']) {
				case SEASON_GAME:
					$division['Season']['Game'][] = $game;
					break;

				case POOL_PLAY_GAME:
					$division['Pools'][$game['HomePoolTeam']['Pool']['stage']][$game['pool_id']]['Game'][] = $game;
					break;

				case BRACKET_GAME:
					$bracket_games[] = $game;
					break;
			}
		}

		// Process each group of games to generate interim results
		if (!empty($division['Season'])) {
			$division['Season']['Results'] = $this->roundRobinResults($division, $league, $division['Season']['Game'], $spirit_obj);
		}

		if (!empty($division['Pools'])) {
			ksort($division['Pools']);
			foreach ($division['Pools'] as $stage_num => $stage) {
				foreach ($stage as $pool_num => $pool) {
					$division['Pools'][$stage_num][$pool_num]['Results'] = $this->roundRobinResults($division, $league, $pool['Game'], $spirit_obj);
				}
			}
		}

		if (!empty($bracket_games)) {
			$division['Bracket']['Results'] = $this->bracketResults($bracket_games, $spirit_obj);

			AppModel::_reindexOuter($bracket_games, 'Game', 'id');
			ksort($bracket_games);

			while (!empty($bracket_games)) {
				$bracket = Game::_extractBracket($bracket_games);
				ksort($bracket);
				// For the class names to format this correctly, we need the rounds in
				// this bracket to be numbered from 0, regardless of what their real
				// round number is.
				$bracket = array_values($bracket);

				// Find the bracket's pool id
				$pool_id = null;
				foreach ($bracket[0] as $game) {
					if (!empty($game['pool_id'])) {
						$pool_id = $game['pool_id'];
						break;
					}
				}
				$division['Bracket']['Game'][] = compact('pool_id', 'bracket');
			}
		}

		// Put the results into the top team records for easy access.
		// Also, put teams into arrays for each grouping and sort them.
		foreach ($teams as $key => $team) {
			if (!empty($division['Season']['Results'][$team['id']])) {
				$teams[$key]['Season'] = $division['Season']['Results'][$team['id']];
				$division['Season']['Team'][] = $teams[$key];
			}
			if (!empty($division['Pools'])) {
				foreach ($division['Pools'] as $stage_num => $stage) {
					foreach ($stage as $pool_num => $pool) {
						if (!empty($pool['Results'][$team['id']])) {
							$x = $teams[$key];
							unset($x['Season']);
							unset($x['Pools']);
							$x += $pool['Results'][$team['id']];
							$division['Pools'][$stage_num][$pool_num]['Team'][] = $x;

							$teams[$key]['Pools'][$stage_num][$pool_num] = $pool['Results'][$team['id']];
						}
					}
				}
			}
			if (!empty($division['Bracket']['Results'][$team['id']])) {
				$teams[$key]['Bracket'] = $division['Bracket']['Results'][$team['id']];

				$x = $teams[$key];
				unset($x['Season']);
				unset($x['Pools']);
				$division['Bracket']['Team'][] = $x;
			}
		}

		$this->division_for_sort = $division;
		$this->league_for_sort = $league;

		if (!empty($division['Season']['Team'])) {
			usort ($division['Season']['Team'], array($this, 'compareTeams'));
		}
		if (!empty($division['Pools'])) {
			foreach ($division['Pools'] as $stage_num => $stage) {
				foreach ($stage as $pool_num => $pool) {
					if (!empty($division['Pools'][$stage_num][$pool_num]['Team'])) {
						usort($division['Pools'][$stage_num][$pool_num]['Team'], array($this, 'compareTeamsResults'));
						$this->detectAndResolveTies($division['Pools'][$stage_num][$pool_num]['Team'], 'compareTeamsResults');
					}
				}
			}
		}
		if (!empty($division['Bracket']['Team'])) {
			usort ($division['Bracket']['Team'], array($this, 'compareTeamsTournament'));
		}

		$this->division_for_sort = $division;
	}

	function roundRobinResults($division, $league, $games, $spirit_obj) {
		$results = array();

		foreach ($games as $game) {
			// Season games use the round indicator for which of possible multiple passes
			// through the entire round robin we are in. Tournament games use it for which
			// game in the single round robin we are in, which is needed when scheduling
			// but not when analysing results.
			if ($game['type'] == SEASON_GAME) {
				$round = $game['round'];
			} else {
				$round = 1;
			}

			if (!in_array($game['status'], Configure::read('unplayed_status'))) {
				if (Game::_is_finalized($game)) {
					$this->addGameResult ($division, $league, $results, $game['home_team'], $game['away_team'],
							$round, $game['home_score'], $game['away_score'], $game['home_carbon_flip'],
							Game::_get_spirit_entry ($game, $game['home_team'], $spirit_obj), $spirit_obj,
							$game['status'] == 'home_default', $game['status'] == 'normal');
					$this->addGameResult ($division, $league, $results, $game['away_team'], $game['home_team'],
							$round, $game['away_score'], $game['home_score'], 2 - $game['home_carbon_flip'],
							Game::_get_spirit_entry ($game, $game['away_team'], $spirit_obj), $spirit_obj,
							$game['status'] == 'away_default', $game['status'] == 'normal');
				} else {
					$this->addUnplayedGame ($division, $results, $game['home_team'], $game['away_team'], $round);
					$this->addUnplayedGame ($division, $results, $game['away_team'], $game['home_team'], $round);
				}
			}
		}

		return $results;
	}

	function addGameResult($division, $league, &$results, $team, $opp, $round, $score_for, $score_against, $cf_for, $spirit_for, $spirit_obj, $default, $played) {
		if (!isset($this->sport_obj)) {
			$this->sport_obj = $this->_controller->_getComponent ('Sport', $league['sport'], $this->_controller);
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
			$results[$team] = array('id' => $team, 'W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0, 'spirit_games' => 0,
									'CFW' => 0, 'CFL' => 0, 'CFT' => 0, 'cf_pts' => 0, 'cf_games' => 0,
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
		if ($played) {
			++ $results[$team]['cf_games'];
			$results[$team]['cf_pts'] += $cf_for;
			if ($cf_for == 2) {
				++ $results[$team]['CFW'];
			} else if ($cf_for == 1) {
				++ $results[$team]['CFT'];
			} else {
				++ $results[$team]['CFL'];
			}
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
				++ $results[$team]['spirit_games'];
				if (!$league['numeric_sotg']) {
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

	function addUnplayedGame($division, &$results, $team, $opp, $round) {
		// Make sure the team record exists in the results
		if (! array_key_exists ($team, $results)) {
			$results[$team] = array('id' => $team, 'W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0, 'spirit_games' => 0,
									'CFW' => 0, 'CFL' => 0, 'CFT' => 0, 'cf_pts' => 0, 'cf_games' => 0,
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
	}

	function bracketResults($games, $spirit_obj) {
		$results = array();

		foreach ($games as $game) {
			// Make sure the team records exist in the results
			if (! array_key_exists ($game['home_team'], $results)) {
				$results[$game['home_team']] = array('pool' => $game['pool_id'], 'results' => array(), 'final' => null);
			}
			if (! array_key_exists ($game['away_team'], $results)) {
				$results[$game['away_team']] = array('pool' => $game['pool_id'], 'results' => array(), 'final' => null);
			}

			// Check if this was a placement game
			$final_win = $final_lose = null;
			$suffix = substr($game['name'], -2);
			if (in_array($suffix, array('st', 'nd', 'rd', 'th'))) {
				$name = substr($game['name'], 0, -2);
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
			if ($game['home_score'] > $game['away_score']) {
				$results[$game['home_team']]['results'][$game['round']] = 1;
				$results[$game['home_team']]['final'] = $final_win;

				$results[$game['away_team']]['results'][$game['round']] = -1;
				$results[$game['away_team']]['final'] = $final_lose;
			} else if ($game['home_score'] < $game['away_score']) {
				$results[$game['home_team']]['results'][$game['round']] = -1;
				$results[$game['home_team']]['final'] = $final_lose;

				$results[$game['away_team']]['results'][$game['round']] = 1;
				$results[$game['away_team']]['final'] = $final_win;
			} else {
				$results[$game['home_team']]['results'][$game['round']] = $results[$game['away_team']]['results'][$game['round']] = 0;
				$results[$game['home_team']]['final'] = $results[$game['away_team']]['final'] = $final_win;
			}
		}

		return $results;
	}

	function compareSeed($a, $b) {
		if ($a['seed'] < $b['seed'])
			return -1;
		if ($a['seed'] > $b['seed'])
			return 1;
		return 0;
	}

	/**
	 * By default, we sort by any seeding information we may have, and then by name as a last resort.
	 */

	function compareTeams($a, $b) {
		if ($a['initial_seed'] < $b['initial_seed'])
			return -1;
		if ($a['initial_seed'] > $b['initial_seed'])
			return 1;

		return (strtolower ($a['name']) > strtolower ($b['name']));
	}

	/**
	 * Various league types might have tournaments.
	 */
	function compareTeamsTournament($a, $b) {
		// If both teams have bracket results, we may be able to use that
		if (!empty($a['Bracket']) && !empty($b['Bracket'])) {
			// If both teams have final placements, we use that
			if ($a['Bracket']['final'] !== null && $b['Bracket']['final'] !== null) {
				if ($a['Bracket']['final'] > $b['Bracket']['final']) {
					return 1;
				} else if ($a['Bracket']['final'] < $b['Bracket']['final']) {
					return -1;
				}
			}

			// Go through each round in the bracket and compare the two teams' results in that round
			$rounds = array_unique(array_merge(array_keys($a['Bracket']['results']), array_keys($b['Bracket']['results'])));
			sort($rounds);
			foreach ($rounds as $round) {
				// If the first team had a bye in this round and the second team lost,
				// put the first team ahead
				if (!array_key_exists($round, $a['Bracket']['results']) && $b['Bracket']['results'][$round] < 0) {
					return -1;
				}

				// If the second team had a bye in this round and the first team lost,
				// put the second team ahead
				if (!array_key_exists($round, $a['Bracket']['results']) && $b['Bracket']['results'][$round] < 0) {
					return 1;
				}

				// If both teams played in this round and had different results,
				// use that result to determine who is ahead
				if (array_key_exists($round, $a['Bracket']['results']) && array_key_exists($round, $b['Bracket']['results']) &&
					$a['Bracket']['results'][$round] != $b['Bracket']['results'][$round])
				{
					return ($a['Bracket']['results'][$round] > $b['Bracket']['results'][$round] ? -1 : 1);
				}
			}
		}

		// If both teams have pool results, we may be able to use that
		if (!empty($a['Pools']) && !empty($b['Pools'])) {
			$max_stage = max(array_merge(array_keys($a['Pools']), array_keys($b['Pools'])));
			for ($stage = $max_stage; $stage > 0; -- $stage) {
				// If teams are not in the same pool, we use that
				$a_pool = current(array_keys($a['Pools'][$stage]));
				$b_pool = current(array_keys($b['Pools'][$stage]));
				if ($a_pool < $b_pool) {
					return -1;
				} else if ($a_pool > $b_pool) {
					return 1;
				}

				$ret = $this->compareTeamsResults($a['Pools'][$stage][$a_pool], $b['Pools'][$stage][$b_pool]);
				if ($ret != 0) {
					return $ret;
				}
			}
		}

		return $this->compareTeams($a, $b);
	}

	/**
	 * Sort based on configured list of tie-breakers
	 */
	function compareTeamsTieBreakers($a, $b) {
		// Teams with no season results are ranked lower than those with
		if (!array_key_exists ('Season', $a)) {
			return 1;
		} else if (!array_key_exists ('Season', $b)) {
			return -1;
		}

		$round = $this->division_for_sort['current_round'];
		if ($round != 1) {
			if (array_key_exists($round, $a['Season']['rounds'])) {
				$a_results = $a['Season']['rounds'][$round];
				$a_results['id'] = $a['id'];
			} else {
				$a_results = array('id' => $a['id'], 'W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'gf' => 0, 'ga' => 0, 'vs' => array(), 'vspm' => array());
			}
			if (array_key_exists($round, $b['Season']['rounds'])) {
				$b_results = $b['Season']['rounds'][$round];
				$b_results['id'] = $b['id'];
			} else {
				$b_results = array('id' => $b['id'], 'W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'gf' => 0, 'ga' => 0, 'vs' => array(), 'vspm' => array());
			}
		} else {
			$a_results = $a['Season'];
			$b_results = $b['Season'];
		}

		return $this->compareTeamsResults($a_results, $b_results);
	}

	/**
	 * Sort based on round-robin results
	 */
	function compareTeamsResults($a, $b) {
		if ($a['pts'] < $b['pts'])
			return 1;
		if ($a['pts'] > $b['pts'])
			return -1;

		if ($a['W'] < $b['W'])
			return 1;
		if ($a['W'] > $b['W'])
			return -1;

		$order = Configure::read("tie_breakers.{$this->league_for_sort['tie_breaker']}");
		foreach ($order as $option) {
			switch ($option) {
				case 'hth':
					if (array_key_exists ($b['id'], $a['vs'])) {
						// if b is in a's results, a must also exist in b's results, no point checking that
						if ($a['vs'][$b['id']] < $b['vs'][$a['id']])
							return 1;
						if ($a['vs'][$b['id']] > $b['vs'][$a['id']])
							return -1;
					}
					break;

				case 'hthpm':
					if (array_key_exists ($b['id'], $a['vspm'])) {
						// if b is in a's results, a must also exist in b's results, no point checking that
						if ($a['vspm'][$b['id']] < $b['vspm'][$a['id']])
							return 1;
						if ($a['vspm'][$b['id']] > $b['vspm'][$a['id']])
							return -1;
					}
					break;

				case 'pm':
					if ($a['gf'] - $a['ga'] < $b['gf'] - $b['ga'])
						return 1;
					if ($a['gf'] - $a['ga'] > $b['gf'] - $b['ga'])
						return -1;
					break;

				case 'gf':
					if ($a['gf'] < $b['gf'])
						return 1;
					if ($a['gf'] > $b['gf'])
						return -1;
					break;

				case 'loss':
					if ($a['L'] > $b['L'])
						return 1;
					if ($a['L'] < $b['L'])
						return -1;
					break;

				case 'spirit':
					if (!empty($a['spirit_games']) && !empty($b['spirit_games'])) {
						if ($a['spirit'] / $a['spirit_games'] < $b['spirit'] / $b['spirit_games'])
							return 1;
						if ($a['spirit'] / $a['spirit_games'] > $b['spirit'] / $b['spirit_games'])
							return -1;
					}
					break;

				case 'cf':
					if (!empty($a['cf_games']) && !empty($b['cf_games'])) {
						if ($a['cf_pts'] / $a['cf_games'] < $b['cf_pts'] / $b['cf_games'])
							return 1;
						if ($a['cf_pts'] / $a['cf_games'] > $b['cf_pts'] / $b['cf_games'])
							return -1;
					}
					break;
			}
		}

		return 0;
	}

	/**
	 * Sort based on round-robin results, when teams are coming from different pools,
	 * possibly with unequal numbers of teams. Putting a 5-0 team ahead of a 4-0 team
	 * isn't fair!
	 */
	function compareTeamsResultsCrossPool($a, $b) {
		if ($a['L'] > $b['L'])
			return 1;
		if ($a['L'] < $b['L'])
			return -1;

		if (($a['gf'] - $a['ga']) / $a['games'] < ($b['gf'] - $b['ga']) / $b['games'])
			return 1;
		if (($a['gf'] - $a['ga']) / $a['games'] > ($b['gf'] - $b['ga']) / $b['games'])
			return -1;

		if ($a['gf'] / $a['games'] < $b['gf'] / $b['games'])
			return 1;
		if ($a['gf'] / $a['games'] > $b['gf'] / $b['games'])
			return -1;

		if ($a['spirit_games'] && $b['spirit_games']) {
			if ($a['spirit'] / $a['spirit_games'] < $b['spirit'] / $b['spirit_games'])
				return 1;
			if ($a['spirit'] / $a['spirit_games'] > $b['spirit'] / $b['spirit_games'])
				return -1;
		}

		// For lack of a better idea, we'll use initial seed as the final tie breaker
		if ($a['initial_seed'] < $b['initial_seed'])
			return -1;
		if ($a['initial_seed'] > $b['initial_seed'])
			return 1;

		return 0;
	}

	/**
	 * Go through a list of teams with game results, detect any three (or more)
	 * way ties, and resolve them.
	 * 
	 * @param mixed $teams Sorted list of teams, with zero-based array indices
	 */
	function detectAndResolveTies(&$teams, $comparison) {
		for ($i = 0; $i < count($teams) - 1; ++ $i) {
			$tied = array();
			for ($j = $i + 1; $j < count($teams); ++ $j) {
				if ($this->$comparison($teams[$i], $teams[$j]) == 1) {
					// Found two teams that are not in the expected order.
					// They must be tied with at least one other.
					$tied[] = $i;
					$tied[] = $j;
					for ($k = $i + 1; $k < count($teams); ++ $k) {
						if ($j != $k && $this->$comparison($teams[$j], $teams[$k]) == 1) {
							$tied[] = $k;
							// We don't need to look for teams tied with the ones we've already found
							$i = max($j, $k) + 1;
						}
					}
					$this->resolveTies($teams, $tied);
					$j = max($j, $i + 1);
				}
			}
		}
	}

	function resolveTies(&$teams, $tied) {
		$compare = array_fill_keys($tied, array(
				'hthpm' => 0,
				'pm' => 0,
		));
		$round = $this->division_for_sort['current_round'];
		foreach ($tied as $i) {
			if (!empty($teams[$i]['Season']['rounds'])) {
				foreach ($tied as $j) {
					if ($i != $j) {
						$compare[$i]['hthpm'] += $teams[$i]['Season']['rounds'][$round]['vspm'][$teams[$j]['id']];
					}
				}
				$compare[$i]['pm'] = $teams[$i]['Season']['rounds'][$round]['gf'] - $teams[$i]['Season']['rounds'][$round]['ga'];
				$compare[$i]['initial_seed'] = $teams[$i]['initial_seed'];
			} else {
				// A huge seed will place a team with no results in last place
				$compare[$i]['initial_seed'] = 10000;
			}
		}
		uasort($compare, array($this, 'compareHTH'));

		// Start the revised list with all teams that were ahead of the tied teams
		$new_teams = array_slice($teams, 0, min($tied));

		// When rounds are not complete, we can have multi-way ties where one team is clearly better
		// then the others, but got lumped into the middle based on overall +/- comparison with a
		// team that they haven't played. We need to deal with these leftovers.
		$leftovers = array_diff(range(min($tied), max($tied)), $tied);
		if (!empty($leftovers)) {
			$sorted = array_keys($compare);
			$best = $teams[array_shift($sorted)];
			$worst = $teams[array_pop($sorted)];
			foreach ($leftovers as $key => $team) {
				// Is the leftover team better than the best team among those tied?
				if ($this->compareTeamsResults($teams[$team]['Season'], $best['Season']) < 1) {
					$new_teams[] = $teams[$team];
					unset($leftovers[$key]);
				}
			}
		}

		// Put the teams into the same order as this new comparison demands
		foreach (array_keys($compare) as $key) {
			$new_teams[] = $teams[$key];
		}

		// Any more leftover teams to deal with?
		foreach ($leftovers as $team) {
			$new_teams[] = $teams[$team];
		}

		// Finish up with all the teams there were behind all the tied teams
		$new_teams += array_slice($teams, max($tied) + 1, null, true);

		$teams = $new_teams;
	}

	function compareHTH($a, $b) {
		// First multi-way tie breaker is head-to-head plus minus in games between these teams
		if ($a['hthpm'] > $b['hthpm'])
			return -1;
		if ($a['hthpm'] < $b['hthpm'])
			return 1;

		// Second multi-way tie breaker is overall plus minus
		if ($a['pm'] > $b['pm'])
			return -1;
		if ($a['pm'] < $b['pm'])
			return 1;

		// For lack of a better idea, we'll use initial seed as the final tie breaker
		if ($a['initial_seed'] < $b['initial_seed'])
			return -1;
		if ($a['initial_seed'] > $b['initial_seed'])
			return 1;
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
	 * @param mixed $num_teams The number of teams to include in the description
	 * @param mixed $stage The stage of the tournament we're scheduling
	 * @return mixed The description
	 *
	 */
	function scheduleDescription($type, $num_teams, $stage) {
		if ($type == 'crossover') {
			return __('crossover game', true);
		} 
		$types = $this->scheduleOptions($num_teams, $stage);
		$desc = $types[$type];
		return $desc;
	}

	/**
	 * Get a preview of the games to be created. Most schedule types will have no preview.
	 *
	 * @param mixed $type The scheduling type to return the description of
	 * @param mixed $num_teams The number of teams to include in the description
	 * @param mixed $stage The stage of the tournament we're scheduling
	 * @return mixed The preview
	 *
	 */
	function schedulePreview($type, $num_teams) {
		return null;
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
	function startSchedule($division_id, $exclude_teams, $start_date, $double_booking = false) {
		$this->games = array();
		$this->double_booking = $double_booking;

		$conditions = array(
			'game_date >=' => $start_date,
		);
		if (!$double_booking) {
			$conditions['assigned'] = false;
		}

		// NOTE: If changes are made to this containment, they may also be required
		// in SchedulesController::reschedule.
		$this->_controller->Division->contain (array (
			'Day',
			'Team' => array(
				'Facility',
				'order' => 'Team.name',
				'conditions' => array('NOT' => array('id' => $exclude_teams)),
			),
			'League',
			'Pool' => array(
				'order' => 'Pool.id',
				'PoolsTeam' => array(
					'order' => 'PoolsTeam.id',
				),
			),
			'Game' => array(
				'conditions' => array(
					'NOT' => array('Game.status' => Configure::read('unplayed_status')),
				),
				'GameSlot',
			),
			'DivisionGameslotAvailability' => array(
				'GameSlot' => array(
					// This will still return all of the Availability records, but many will have
					// empty GameSlot arrays, so Set::Extract calls won't match and they're ignored
					// TODO: Can a better query improve the efficiency of this?
					'conditions' => $conditions,
					'Field' => 'Facility',
				),
			),
		));
		$this->division = $this->_controller->Division->read(null, $division_id);
		if ($this->division === false) {
			$this->_controller->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'warning'));
			return false;
		}

		if ($double_booking) {
			$this->saved_slots = $this->division['DivisionGameslotAvailability'];
		}

		// Go through all the games and count the number of home and away games
		// and games with preferences for each team
		$this->home_games = $this->away_games = $this->field_rank_sum = array();
		foreach ($this->division['Game'] as $game) {
			if (!array_key_exists ($game['home_team'], $this->home_games)) {
				$this->home_games[$game['home_team']] = 1;
			} else {
				++ $this->home_games[$game['home_team']];
			}

			if (!empty($game['away_team'])) {
				if (!array_key_exists ($game['away_team'], $this->away_games)) {
					$this->away_games[$game['away_team']] = 1;
				} else {
					++ $this->away_games[$game['away_team']];
				}
			}

			if (!empty($game['home_field_rank'])) {
				if (!array_key_exists ($game['home_team'], $this->field_rank_sum)) {
					$this->field_rank_sum[$game['home_team']] = 0;
				}
				$this->field_rank_sum[$game['home_team']] += 1 / $game['home_field_rank'];
			}

			if (!empty($game['away_team']) && !empty($game['away_field_rank'])) {
				if (!array_key_exists ($game['away_team'], $this->field_rank_sum)) {
					$this->field_rank_sum[$game['away_team']] = 0;
				}
				$this->field_rank_sum[$game['away_team']] += 1 / $game['away_field_rank'];
			}
		}

		return true;
	}

	function finishSchedule($division_id, $publish, $double_booking) {
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
		if (!$double_booking) {
			$slots = Set::extract ('/game_slot_id', $this->games);
			$this->_controller->Division->Game->GameSlot->contain();
			$taken = $this->_controller->Division->Game->GameSlot->find('all', array('conditions' => array(
					'id' => $slots,
					'assigned' => true,
			)));
			if (!empty ($taken)) {
				$this->_controller->Session->setFlash(__('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true), 'default', array('class' => 'warning'));
				return false;
			}
		}

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
		}

		return ($transaction->commit() !== false);
	}

	function beforeSave($key) {
		// Most league types have nothing that keeps games from being saved
		return true;
	}

	function afterSave($gkey) {
		// Most league types have nothing to do after games are saved
		return true;
	}

	/**
	 * Create a single game in this division
	 */
	function createEmptyGame($date = null) {
		$num_teams = count($this->division['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		$game = array(
			'home_team' => null,
			'away_team' => null,
		);

		if ($date) {
			$game_slot = $this->selectRandomGameslot($date);
			if ($game_slot === false) {
				return false;
			}

			$game['game_slot_id'] = $game_slot['id'];
		}

		$this->games[] = $game;
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
	 * It uses the selectWeightedGameslot function, which first looks at home field
	 * designation, then at field region preferences.
	 *
	 * We first sort games in order of the home team's allocation preference ratio.
	 * Teams with a low ratio get first crack at a desired location. Games where the
	 * home team has a home field are first in the list, to prevent another team with
	 * a lower ratio from scooping another team's dedicated home field.
	 *
	 * Once sorted, we simply loop over all games and call selectWeightedGameslot(),
	 * which takes region preference into account.
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
			$slot = $this->selectWeightedGameslot($game, $date, count($games) + 1 + $remaining);
			if (!$slot) {
				return false;
			}
			$game['game_slot_id'] = $slot['id'];

			$home = $this->division['Team'][$game['home_team']];
			$away = (!empty($game['away_team']) ? $this->division['Team'][$game['away_team']] : null);
			$this->_controller->Division->Game->updateFieldRanking($game, $slot['Field'], $home, $away);

			$this->games[] = $game;
		}

		return true;
	}

	function comparePreferredFieldRatio($a, $b) {
		// Put all those games where one team has a home field at the top of the list
		$a_home = $this->hasHomeField($a);
		$b_home = $this->hasHomeField($b);
		if ($a_home && !$b_home)
			return -1;
		if (!$a_home && $b_home)
			return 1;

		$a_ratio = $this->preferredFieldRatio($a);
		$b_ratio = $this->preferredFieldRatio($b);
		if ($a_ratio == $b_ratio)
			return 0;

		return ($a_ratio > $b_ratio) ? 1 : -1;
	}

	function hasHomeField($game) {
		return (Configure::read('feature.home_field') && ($this->division['Team'][$game['home_team']]['home_field'] || $this->division['Team'][$game['away_team']]['home_field']));
	}

	function preferredFieldRatio($game) {
		// If we're not using team preferences, that's like everyone
		// has 100% of their games in a preferred region.
		if (!Configure::read('feature.region_preference') && !Configure::read('feature.facility_preference')) {
			return 1;
		}

		// We've already dealt with games where a team has a home field. If
		// we're calling this function, then either both games being compared
		// involve a team with a home field, or neither does. So, if this
		// game has one, the other must also, in which case we want to look
		// to their opponents to break that tie. This tie-breaker will
		// only matter if multiple teams share a home field, but it doesn't
		// do any harm to include it in other situations.
		if (Configure::read('feature.home_field') && $this->division['Team'][$game['home_team']]['home_field']) {
			$id = $game['away_team'];
		} else {
			// We get here if home fields are not allowed, neither team
			// has a home field, or only the away team does. In any case,
			// it's the home team that we want to drive the preference.
			$id = $game['home_team'];
		}

		// No preference means they're always happy.  We return over 100% to
		// force them to sort last when ordering by ratio, so that teams with
		// a preference always appear before them.
		if ((!Configure::read('feature.region_preference') || empty($this->division['Team'][$id]['region_preference'])) &&
			(!Configure::read('feature.facility_preference') || empty($this->division['Team'][$id]['Facility'])))
		{
			return 2;
		}

		if (!array_key_exists('preferred_ratio', $this->division['Team'][$id])) {
			if (!array_key_exists($id, $this->field_rank_sum)) {
				$this->division['Team'][$id]['preferred_ratio'] = 0;
			} else {
				$this->division['Team'][$id]['preferred_ratio'] = $this->field_rank_sum[$id] /
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
	 * @param mixed $dates The possible dates of the game
	 * @param mixed $remaining The number of games still to be scheduled, including this one
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectRandomGameslot($dates, $remaining = 1, $recursive = false) {
		if (!is_array($dates)) {
			$dates = array($dates);
		}

		$slots = array();
		foreach ($dates as $date) {
			if (is_numeric ($date)) {
				$date = date('Y-m-d', $date);
			}
			$slots = array_merge($slots, Set::extract("/GameSlot[game_date=$date]/.", $this->division['DivisionGameslotAvailability']));
			if (count($slots) >= $remaining) {
				break;
			}
		}

		if (empty ($slots)) {
			// If double-booking is allowed, we can reset the list of slots and start again
			if (!$recursive && $this->double_booking) {
				$this->division['DivisionGameslotAvailability'] = $this->saved_slots;
				return $this->selectRandomGameslot($dates, $remaining, true);
			}

			App::import('Helper', 'Html');
			$html = new HtmlHelper();

			$this->_controller->Session->setFlash(sprintf(__('There are insufficient game slots available to complete this schedule. Check the %s for details.', true),
					$html->link(sprintf(__('%s Availability Report', true), __(Configure::read('sport.field_cap'), true)), array('controller' => 'divisions', 'action' => 'slots', 'division' => $this->division['Division']['id'], 'date' => $date))),
					'default', array('class' => 'warning'));
			return false;
		}

		shuffle ($slots);
		$slot = $slots[0];
		$this->removeGameslot($slot['id']);
		return $slot;
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
		if (!empty($game['away_team'])) {
			$away = $this->division['Team'][$game['away_team']];
		}

		$days = Set::extract('/Day/id', $this->division);
		$match_dates = Game::_matchDates($date, $days);

		if (Configure::read('feature.home_field')) {
			// Try to adhere to the home team's home field
			if ($home['home_field']) {
				$slots = $this->matchingSlots("[field_id={$home['home_field']}]", '..', $match_dates, $remaining);
			}

			// If not available, try the away team's home field
			if (empty ($slots) && isset($away) && $away['home_field']) {
				$slots = $this->matchingSlots("[field_id={$away['home_field']}]", '..', $match_dates, $remaining);
			}
		}

		// Maybe try facility preferences
		if (empty($slots) && Configure::read('feature.facility_preference')) {
			foreach ($home['Facility'] as $facility) {
				$slots = $this->matchingSlots("/Field[facility_id={$facility['id']}]", '..', $match_dates, $remaining);
				if (!empty($slots)) {
					break;
				}
			}

			if (empty ($slots) && isset($away) && !empty($away['Facility'])) {
				foreach ($away['Facility'] as $facility) {
					$slots = $this->matchingSlots("/Field[facility_id={$facility['id']}]", '..', $match_dates, $remaining);
					if (!empty($slots)) {
						break;
					}
				}
			}
		}

		// Maybe try region preferences
		if (empty($slots) && Configure::read('feature.region_preference')) {
			if ($home['region_preference']) {
				$slots = $this->matchingSlots("/Field/Facility[region_id={$home['region_preference']}]", '../..', $match_dates, $remaining);
			}

			if (empty ($slots) && isset($away) && $away['region_preference']) {
				$slots = $this->matchingSlots("/Field/Facility[region_id={$away['region_preference']}]", '../..', $match_dates, $remaining);
			}
		}

		// If still nothing can be found, last try is just random
		if (empty ($slots)) {
			return $this->selectRandomGameslot($match_dates, $remaining);
		}

		shuffle ($slots);
		$slot = $slots[0]['GameSlot'];
		$this->removeGameslot($slot['id']);
		return $slot;
	}

	function matchingSlots($criteria, $path, $dates, $remaining) {
		$matches = array();
		foreach ($dates as $date) {
			$matches = array_merge($matches, Set::extract("/GameSlot[game_date=$date]$criteria/$path", $this->division['DivisionGameslotAvailability']));
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
		$dates = array_unique(Set::extract("/GameSlot[game_date>=$date]/game_date", $this->division['DivisionGameslotAvailability']));
		sort($dates);

		$available = $slots = 0;
		foreach ($dates as $date) {
			$slots += count(Set::extract("/GameSlot[game_date=$date]", $this->division['DivisionGameslotAvailability']));
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
			// Leagues that operate on multiple nights of the week may have more
			// game slots available later in the same week, but we don't want to
			// use them.
			$days = Set::extract('/Day/id', $this->division);
			$match_dates = Game::_matchDates($date, $days);
			$last_date = max($match_dates);

			$dates = array_unique(Set::extract("/GameSlot[game_date>$last_date]/game_date", $this->division['DivisionGameslotAvailability']));

			// Tournaments, on the other hand, will not want to do this. We detect
			// the difference by whether or not there are any more dates available.
			if (empty($dates)) {
				$dates = array_unique(Set::extract("/GameSlot[game_date>$date]/game_date", $this->division['DivisionGameslotAvailability']));
			}

			if (empty($dates)) {
				return false;
			}
			return min($dates);
		}

		$dates = array_unique(Set::extract("/GameSlot[game_date>=$date]/game_date", $this->division['DivisionGameslotAvailability']));
		sort($dates);
		while ($skip > 0 && !empty($dates)) {
			$date = array_shift($dates);
			$skip -= count(Set::extract("/GameSlot[game_date=$date]", $this->division['DivisionGameslotAvailability']));
		}
		return array_shift($dates);
	}
}

?>
