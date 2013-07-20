<?php

/**
 * Derived class for implementing functionality for divisions with tournament scheduling.
 */

class LeagueTypeTournamentComponent extends LeagueTypeComponent
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'tournament';

	/**
	 * Remember details about the games already scheduled, so that when
	 * we get to the next round we make sure to advance the time.
	 */
	var $pool_times = array();

	/**
	 * Remember details about the block of games currently being scheduled,
	 * for use when we're scheduling several blocks at once.
	 */
	var $first_team = 0;

	/**
	 * Cached list of game slots that we have available
	 */
	var $slots = null;

	function poolOptions($num_teams, $stage) {
		$types = array();

		if ($stage == 1) {
			// Add options, depending on the number of teams.
			$min_pools = ceil($num_teams / 12);
			$max_pools = floor($num_teams / 2);
			for ($i = $min_pools; $i <= $max_pools; ++ $i) {
				$types["seeded_$i"] = "seeded split into $i pools";
			}

			if ($num_teams >= 6) {
				// Add some snake seeding options for round-robins to lead into re-seeding
				$max_snake_size = 8;
				$min_snake_size = 3;
				$min_pools = max(2, ceil($num_teams / $max_snake_size));
				$max_pools = floor($num_teams / $min_snake_size);

				for ($pools = $min_pools; $pools <= $max_pools; ++ $pools) {
					$teams = floor($num_teams / $pools);
					$remainder = $num_teams - ($teams * $pools);

					if ($remainder == 0) {
						$types["snake_$pools"] = sprintf('snake seeded split into %d pools of %d teams', $pools, $teams);
					} else if ($pools == 2) {
						$types["snake_$pools"] = sprintf('snake seeded split into %d pools of %d and %d teams', $pools, $teams + 1, $teams);
					} else {
						$types["snake_$pools"] = sprintf('snake seeded split into %d pools (%d with %d teams and %d with %d)', $pools, $remainder, $teams + 1, $pools - $remainder, $teams);
					}
				}
			}
		} else {
			// Add options, depending on the number of teams.
			$min_pools = ceil($num_teams / 12);
			$max_pools = floor($num_teams / 2);
			for ($i = $min_pools; $i <= $max_pools; ++ $i) {
				$types["reseed_$i"] = "$i re-seeded power pools";
			}
			$types['crossover'] = 'group of crossover games';
		}

		return $types;
	}

	function scheduleOptions($num_teams, $stage) {
		$types = array(
			'single' => sprintf(__('single blank, unscheduled game (2 teams, one %s)', true), Configure::read('sport.field')),
		);

		if ($num_teams % 2 == 0) {
			$types['blankset'] = "set of blank unscheduled games for all teams in the division ($num_teams teams, " . ($num_teams / 2) . " games)";
		} else {
			$types['blankset_bye'] = "set of blank unscheduled games for all but one team in the division ($num_teams teams, " . (($num_teams - 1) / 2) . " games)";
			$types['blankset_doubleheader'] = "set of blank unscheduled games for all teams in the division, one team will have a double-header ($num_teams teams, " . (($num_teams + 1) / 2) . " games)";
		}

		if ($num_teams >= 3 && $num_teams <= 10) {
			$types['round_robin'] = 'round-robin';
			if ($stage > 1) {
				$types['round_robin_carry_forward'] = 'round-robin with results from prior-stage matchups carried forward';
			}
		}

		// Add more types, depending on the number of teams
		switch ($num_teams) {
			case 2:
				$types['winner_take_all'] = 'single game, winner take all';
				$types['home_and_home'] = '"home and home" series';
				break;

			case 3:
				$types['playin_three'] = 'play-in game for 2nd and 3rd; 1st gets a bye to the finals';
				break;

			case 4:
				$types['semis_consolation'] = 'bracket with semi-finals, finals and 3rd place';
				$types['semis_elimination'] = 'bracket with semi-finals and finals, no 3rd place';
				break;

			case 5:
				$types['semis_consolation_five'] = 'bracket with semi-finals and finals, plus a 5th place play-in';
				$types['semis_minimal_five'] = '1st gets a bye to the finals, 4th and 5th place play-in for the bronze';
				break;

			case 6:
				$types['semis_consolation_six'] = 'bracket with semi-finals and finals, plus 5th and 6th place play-ins';
				$types['semis_double_elimination_six'] = 'bracket with semi-finals and finals, 1st and 2nd place have double-elimination option, everyone gets 3 games';
				$types['semis_complete_six'] = 'bracket with semi-finals and finals, plus 5th and 6th place play-ins, everyone gets 3 games';
				$types['semis_minimal_six'] = 'bracket with semi-finals and finals, 5th and 6th have consolation games, everyone gets 2 games';
				break;

			case 7:
				$types['quarters_consolation_seven'] = 'bracket with quarter-finals, semi-finals, finals, and all placement games, with a bye every round for whoever should be playing the missing 8th seed';
				$types['quarters_round_robin_seven'] = 'bracket with play-in quarter-finals for all but the top seed, semi-finals, finals and 3rd place, and a round-robin for the losers of the quarters';
				break;

			case 8:
				$types['quarters_consolation'] = 'bracket with quarter-finals, semi-finals, finals, and all placement games';
				$types['quarters_bronze'] = 'bracket with quarter-finals, semi-finals, finals and 3rd place, but no consolation bracket';
				$types['quarters_elimination'] = 'bracket with quarter-finals, semi-finals and finals, no placement games';
				break;

			case 9:
				$types['quarters_consolation_nine'] = 'bracket with quarter-finals, semi-finals and finals, plus a 9th place play-in';
				break;

			case 10:
				$types['quarters_consolation_ten'] = 'bracket with quarter-finals, semi-finals and finals, plus 9th and 10th place play-ins';
				$types['presemis_consolation_ten'] = 'bracket with pre-semi-finals, semi-finals and finals, everyone gets 3 games';
				break;

			case 11:
				$types['quarters_consolation_eleven'] = 'bracket with quarter-finals, semi-finals and finals, plus 9th, 10th and 11th place play-ins';
				break;
		}

		return $types;
	}

	function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return array(1);
			case 'blankset':
				return array($num_teams / 2);
			case 'blankset_bye':
				return array(($num_teams - 1) / 2);
			case 'blankset_doubleheader':
				return array(($num_teams + 1) / 2);
			case 'round_robin':
				$games = $num_teams * ($num_teams - 1) / 2;
				$games_per_round = floor($num_teams / 2);
				return array_fill(1, $games / $games_per_round, $games_per_round);
			case 'round_robin_carry_forward':
				// TODO: Assumption here is that each team will already have
				// played exactly one other team in the new pool.
				$games = $num_teams * ($num_teams - 1) / 2 - floor($num_teams / 2);
				$games_per_round = floor($num_teams / 2);
				return array_fill(1, $games / $games_per_round, $games_per_round);
			case 'crossover':
			case 'winner_take_all':
				return array(1);
			case 'home_and_home':
				return array(1, 1);
			case 'playin_three':
				return array(1, 1);
			case 'semis_consolation':
				return array(2, 2);
			case 'semis_elimination':
				return array(2, 1);
			case 'semis_consolation_five':
				return array(1, 2, 2, 2);
			case 'semis_minimal_five':
				return array(2, 2);
			case 'semis_double_elimination_six':
			case 'semis_complete_six':
				return array(3, 3, 3);
			case 'semis_consolation_six':
				return array(2, 2, 3);
			case 'semis_minimal_six':
				return array(3, 3);
			case 'quarters_consolation_seven':
				return array(3, 3, 3);
			case 'quarters_round_robin_seven':
				return array(3, 3, 3, 1);
			case 'quarters_consolation':
				return array(4, 4, 4);
			case 'quarters_bronze':
				return array(4, 4, 2);
			case 'quarters_elimination':
				return array(4, 2, 1);
			case 'quarters_consolation_nine':
				return array(1, 4, 4, 4, 1);
			case 'quarters_consolation_ten':
				return array(2, 5, 4, 4);
			case 'presemis_consolation_ten':
				return array(5, 5, 5);
			case 'quarters_consolation_eleven':
				return array(3, 5, 5, 5);
		}
	}

	function schedulePreview($type, $num_teams, $pool) {
		// Schedules with only a single round don't warrant a preview
		$requirements = $this->scheduleRequirements($type, $num_teams);
		if (count($requirements) < 2) {
			return null;
		}

		// Set up some fake info
		$this->games = array();
		$this->pool_name = null;
		$this->pool = $pool;

		if (!$this->createScheduleBlock($type)) {
			return null;
		}

		$rounds = array();
		foreach ($this->games as $game) {
			if ($game['home_dependency_type'] == 'copy') {
				continue;
			}

			switch ($game['home_dependency_type']) {
				case 'pool':
				case 'seed':
					$alias = Set::extract("/PoolsTeam[id={$game['home_pool_team_id']}]/alias", $pool);
					$home = $alias[0];
					break;
				case 'game_winner':
					$home = "W{$game['home_dependency_id']}";
					break;
				case 'game_loser':
					$home = "L{$game['home_dependency_id']}";
					break;
			}

			switch ($game['away_dependency_type']) {
				case 'pool':
				case 'seed':
					$alias = Set::extract("/PoolsTeam[id={$game['away_pool_team_id']}]/alias", $pool);
					$away = $alias[0];
					break;
				case 'game_winner':
					$away = "W{$game['away_dependency_id']}";
					break;
				case 'game_loser':
					$away = "L{$game['away_dependency_id']}";
					break;
			}

			$rounds[$game['round']][] = "{$home}v{$away}";
		}
		$ret = array();
		foreach ($rounds as $round => $games) {
			$ret[$round] = "Round $round: " . implode(', ', $games);
		}
		return $ret;
	}

	function createSchedule($division_id, $exclude_teams, $data, $pool) {
		if (is_array($data['start_date'])) {
			list($start_date, $x) = explode(' ', min($data['start_date']));
		} else {
			$start_date = $data['start_date'];
		}

		if (!$this->startSchedule($division_id, $exclude_teams, $start_date, $pool) ||
			!$this->createScheduleBlock($data['type']) ||
			!$this->assignFieldsByRound($data['start_date']))
		{
			return false;
		}

		return $this->finishSchedule($division_id, $data['publish']);
	}

	function startSchedule($division_id, $exclude_teams, $start_date, $pool) {
		$this->pool = $pool;
		$this->pool_name = $this->pool['Pool']['name'];
		if (!empty($this->pool_name)) {
			$this->pool_name .= '-';
		}

		$ret = parent::startSchedule($division_id, $exclude_teams, $start_date);
		$prior_teams = Set::extract("/Pool[stage={$this->pool['Pool']['stage']}][name<{$this->pool['Pool']['name']}]/PoolsTeam/alias", $this->division);
		$this->first_team = count($prior_teams);
		return $ret;
	}

	function createScheduleBlock($type) {
		switch($type) {
			case 'single':
				// Create single game
				$ret = $this->createEmptyGame();
				break;
			case 'blankset':
				// Create game for all teams in division
				$ret = $this->createEmptySet();
				break;
			case 'blankset_bye':
				// Create game for all teams in division
				$ret = $this->createEmptySet(-1);
				break;
			case 'blankset_doubleheader':
				// Create game for all teams in division
				$ret = $this->createEmptySet(1);
				break;
			case 'round_robin':
				$ret = $this->createRoundRobin();
				break;
			case 'round_robin_carry_forward':
				$ret = $this->createRoundRobin(true);
				break;
			case 'crossover':
				$ret = $this->createCrossover();
				break;
			case 'winner_take_all':
				$ret = $this->createWinnerTakeAll();
				break;
			case 'home_and_home':
				$ret = $this->createHomeAndHome();
				break;
			case 'playin_three':
				$ret = $this->createPlayinThree();
				break;
			case 'semis_consolation':
				$ret = $this->createSemis(true);
				break;
			case 'semis_elimination':
				$ret = $this->createSemis(false);
				break;
			case 'semis_consolation_five':
				$ret = $this->createSemisFive(true);
				break;
			case 'semis_minimal_five':
				$ret = $this->createSemisFiveMinimal();
				break;
			case 'semis_double_elimination_six':
				$ret = $this->createDoubleEliminationSix(true);
				break;
			case 'semis_complete_six':
				$ret = $this->createCompleteSix(true);
				break;
			case 'semis_consolation_six':
				$ret = $this->createSemisSix(true);
				break;
			case 'semis_minimal_six':
				$ret = $this->createMinimalSix(true);
				break;
			case 'quarters_consolation_seven':
				$ret = $this->createQuartersSeven(true, true);
				break;
			case 'quarters_round_robin_seven':
				$ret = $this->createQuartersRoundRobinSeven(true, true);
				break;
			case 'quarters_consolation':
				$ret = $this->createQuarters(true, true);
				break;
			case 'quarters_bronze':
				$ret = $this->createQuarters(true, false);
				break;
			case 'quarters_elimination':
				$ret = $this->createQuarters(false, false);
				break;
			case 'quarters_consolation_nine':
				$ret = $this->createQuartersNine(true, true);
				break;
			case 'quarters_consolation_ten':
				$ret = $this->createQuartersTen(true, true);
				break;
			case 'presemis_consolation_ten':
				$ret = $this->createPresemisTen(true, true);
				break;
			case 'quarters_consolation_eleven':
				$ret = $this->createQuartersEleven(true, true);
				break;
		}

		return $ret;
	}

	/*
	 * Create an empty set of games for this division
	 */
	function createEmptySet($team_adjustment = 0) {
		$num_teams = count($this->division['Team']) + $team_adjustment;

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		if ($num_teams % 2) {
			$this->_controller->Session->setFlash(__('Must have even number of teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		// Now, create our games.  Don't add any teams, or set a round,
		// or anything, just randomly allocate a gameslot.
		$num_games = $num_teams / 2;
		$success = true;
		for ($i = 0; $i < $num_games; ++$i) {
			$success &= $this->createEmptyGame();
		}

		return $success;
	}

	function createRoundRobin($carry_forward = false) {
		$teams = range(1, count($this->pool['PoolsTeam']));
		$num_teams = count($teams);

		if ($num_teams % 2) {
			$teams[] = -1;
			++ $num_teams;
		}

		// For general algorithm details, see the round robin component
		$success = true;
		$id = 1;

		// If we are carrying forward results, create those games first
		// This cannot work if there was a round robin, then crossover games, then
		// another round robin with results carried forward, because we don't know
		// what teams will win the crossovers and hence don't know what matchups
		// have already happened. TODO: Detect this situation and abort.
		if ($carry_forward) {
			for ($round = 1; $success && ($round < $num_teams); ++ $round) {
				for ($k = 0; $k < ($num_teams / 2); $k++) {
					if ($round % 2) {
						$home = $teams[$k];
						$away = $teams[$num_teams - $k - 1];
					} else {
						$home = $teams[$num_teams - $k - 1];
						$away = $teams[$k];
					}
					if ($home != -1 && $away != -1) {
						if ($this->pool['PoolsTeam'][$home - 1]['dependency_pool_id'] == $this->pool['PoolsTeam'][$away - 1]['dependency_pool_id']) {
							$success &= $this->createTournamentGame ($id++, $round, null, POOL_PLAY_GAME, 'copy', $home, 'copy', $away);
						}
					}
				}
				$teams = $this->rotateAllExceptFirst($teams);
			}
		}

		for ($round = 1; $success && ($round < $num_teams); ++ $round) {
			for ($k = 0; $k < ($num_teams / 2); $k++) {
				if ($round % 2) {
					$home = $teams[$k];
					$away = $teams[$num_teams - $k - 1];
				} else {
					$home = $teams[$num_teams - $k - 1];
					$away = $teams[$k];
				}
				if ($home != -1 && $away != -1) {
					if (!$carry_forward || $this->pool['PoolsTeam'][$home - 1]['dependency_pool_id'] != $this->pool['PoolsTeam'][$away - 1]['dependency_pool_id']) {
						$success &= $this->createTournamentGame ($id++, $round, null, POOL_PLAY_GAME, 'pool', $home, 'pool', $away);
					}
				}
			}
			$teams = $this->rotateAllExceptFirst($teams);
		}

		return $success;
	}

	function rotateAllExceptFirst ($ary) {
		$new_first = array_shift($ary);
		$new_last = array_shift($ary);
		array_push ($ary, $new_last);
		array_unshift ($ary, $new_first);
		return $ary;
	}

	function createCrossover() {
		$success = $this->createTournamentGame (1, 1, true, POOL_PLAY_GAME, 'pool', 1, 'pool', 2);
		return $success;
	}

	function createWinnerTakeAll() {
		// Round 1: 1v2
		$success = $this->createTournamentGame (1, 1, ordinal($this->first_team + 1), BRACKET_GAME, 'pool', 1, 'pool', 2);

		return $success;
	}

	function createHomeAndHome() {
		// Round 1: 1v2
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 2);

		// Round 2: 2v1
		$success &= $this->createTournamentGame (2, 2, '2', BRACKET_GAME, 'pool', 2, 'pool', 1);

		return $success;
	}

	function createPlayinThree() {
		// Round 1: 2v3
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 2: 1 v winner
		$success &= $this->createTournamentGame (2, 2, ordinal($this->first_team + 1), BRACKET_GAME, 'pool', 1, 'game_winner', 1);

		return $success;
	}

	function createSemis($consolation) {
		// Round 1: 1v4, 2v3
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 4);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 2: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (3, 2, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (4, 2, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		return $success;
	}

	function createSemisFive($consolation) {
		// Round 1: 4 vs 5
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 4, 'pool', 5);

		// Round 2: 1 vs Winner 1, 2 vs 3
		$success &= $this->createTournamentGame (2, 2, '2', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (3, 2, '3', BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 3: Winner 2 vs Winner 3 1st/2nd Place, optional Loser 1 vs Loser 3 - Loser 5th Place
		$success &= $this->createTournamentGame (4, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (5, 3, '4', BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);

			// Round 4: Winner 4 vs Loser 2 3rd/4th Place
			$success &= $this->createTournamentGame (6, 4, ordinal($this->first_team + 3), BRACKET_GAME, 'game_winner', 5, 'game_loser', 2);
		}

		return $success;
	}

	function createSemisFiveMinimal() {
		// Round 1: 2v3, 4v5
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 2, 'pool', 3);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 4, 'pool', 5);

		// Round 2: 1st vs winner 1, loser 1 vs winner 2
		$success &= $this->createTournamentGame (3, 2, ordinal($this->first_team + 1), BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (4, 2, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 1, 'game_winner', 2);

		return $success;
	}

	function createCompleteSix($consolation) {
		// Round 1: 1 vs 5, 2 vs 6, 3 vs 4
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 5);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 2, 'pool', 6);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 3, 'pool', 4);

		// Round 2: Winner 1 vs Loser 3, Winner 2 vs Winner 3, Loser 1 vs Loser 2
		$success &= $this->createTournamentGame (4, 2, '4', BRACKET_GAME, 'game_winner', 1, 'game_loser', 3);
		$success &= $this->createTournamentGame (5, 2, '5', BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		$success &= $this->createTournamentGame (6, 2, '6', BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional consolation games
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 5, 'game_winner', 6);
			$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_loser', 4, 'game_loser', 6);
		}

		return $success;
	}

	function createDoubleEliminationSix($consolation) {
		// Round 1: 1 vs 2, 4 vs 5, 3 vs 6
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 2);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: Winner 1 vs Winner 2, Loser 1 vs Winner 3, Loser 2 vs Loser 3
		$success &= $this->createTournamentGame (4, 2, '4', BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		$success &= $this->createTournamentGame (5, 2, '5', BRACKET_GAME, 'game_winner', 3, 'game_loser', 1);
		$success &= $this->createTournamentGame (6, 2, '6', BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional consolation games
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 4, 'game_winner', 6);
			$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}

		return $success;
	}

	function createSemisSix($consolation) {
		// Round 1: 4 vs 5, 3 vs 6
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2
		$success &= $this->createTournamentGame (3, 2, '3', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (4, 2, '4', BRACKET_GAME, 'pool', 2, 'game_winner', 2);

		// Round 3: Winner 3 vs Winner 4 1st/2nd Place, optional Loser 3 vs Loser 4 3rd/4th Place and Loser 1 vs Loser 2 5th/6th Place
		$success &= $this->createTournamentGame (5, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$success &= $this->createTournamentGame (6, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
			$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		return $success;
	}

	function createMinimalSix($consolation) {
		// Round 1: 1 vs 4, 2 vs 3, 5 vs 6
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 4);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 2, 'pool', 3);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 5, 'pool', 6);

		// Round 2: Winner 1 vs Winner 2, optional Loser 2 vs Winner 3, Loser 1 vs Loser 3
		$success &= $this->createTournamentGame (4, 2, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (5, 2, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 2, 'game_winner', 3);
			$success &= $this->createTournamentGame (6, 2, ordinal($this->first_team + 5), BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);
		}

		return $success;
	}

	function createQuartersSeven($bronze, $consolation) {
		// Round 1: 4 vs 5, 2 vs 7, 3 vs 6
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 2, 'pool', 7);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, other winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (4, 2, '4', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (5, 2, '5', BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (6, 2, '6', BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);
		}

		// Round 3: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($bronze) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_loser', 1, 'game_winner', 6);
		}

		return $success;
	}

	function createQuartersRoundRobinSeven($bronze, $consolation) {
		// Round 1: 4 vs 5, 2 vs 7, 3 vs 6
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 2, 'pool', 7);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, Winner 2 vs Winner 3, optional Loser 1 vs Loser 2 - game 1 of round robin for 5th/6th/7th
		$success &= $this->createTournamentGame (4, 2, '4', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (5, 2, '5', BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (6, 2, '6', BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional Loser 4 vs Loser 5 3rd/4th Place, optional Loser 1 vs Loser 3 - game 2 of round robin for 5th/6th/7th
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($bronze) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (9, 3, '7', BRACKET_GAME, 'game_loser', 3, 'game_loser', 1);

			// Round 4: Loser 3 vs Loser 2 - game 3 of round robin for 5th/6th/7th
			$success &= $this->createTournamentGame (10, 4, '8', BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);
		}

		return $success;
	}

	function createQuarters($bronze, $consolation) {
		// Round 1: 1v8, 2v7, etc.
		$success = $this->createTournamentGame (1, 1, '1', BRACKET_GAME, 'pool', 1, 'pool', 8);
		$success &= $this->createTournamentGame (2, 1, '2', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (3, 1, '3', BRACKET_GAME, 'pool', 2, 'pool', 7);
		$success &= $this->createTournamentGame (4, 1, '4', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (5, 2, '5', BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		$success &= $this->createTournamentGame (6, 2, '6', BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$success &= $this->createTournamentGame (7, 2, '7', BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
			$success &= $this->createTournamentGame (8, 2, '8', BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
		}

		// Round 3: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 5, 'game_winner', 6);
		if ($bronze) {
			$success &= $this->createTournamentGame (10, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (11, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_winner', 7, 'game_winner', 8);
			$success &= $this->createTournamentGame (12, 3, ordinal($this->first_team + 7), BRACKET_GAME, 'game_loser', 7, 'game_loser', 8);
		}

		return $success;
	}

	function createQuartersNine($bronze, $consolation) {
		// Round 1: 8v9
		$success = $this->createTournamentGame (1, 1, '01', BRACKET_GAME, 'pool', 8, 'pool', 9);

		// Round 2: 1 vs Winner 1, 2v7, 3v6, 4v5
		$success &= $this->createTournamentGame (2, 2, '02', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (3, 2, '03', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (4, 2, '04', BRACKET_GAME, 'pool', 2, 'pool', 7);
		$success &= $this->createTournamentGame (5, 2, '05', BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (6, 3, '06', BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		$success &= $this->createTournamentGame (7, 3, '07', BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 3, '08', BRACKET_GAME, 'game_loser', 1, 'game_loser', 4);
			$success &= $this->createTournamentGame (9, 3, '09', BRACKET_GAME, 'game_loser', 2, 'game_loser', 5);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (10, 4, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$success &= $this->createTournamentGame (11, 4, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (12, 4, '10', BRACKET_GAME, 'game_winner', 8, 'game_loser', 3);
			$success &= $this->createTournamentGame (13, 4, ordinal($this->first_team + 8), BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);

			// Round 5: optional Winner J vs Winner I - 5th/6th Place
			$success &= $this->createTournamentGame (14, 5, ordinal($this->first_team + 5), BRACKET_GAME, 'game_winner', 12, 'game_winner', 9);
		}

		return $success;
	}

	function createQuartersTen($bronze, $consolation) {
		// Round 1: 8v9, 7v10
		$success = $this->createTournamentGame (1, 1, '01', BRACKET_GAME, 'pool', 8, 'pool', 9);
		$success &= $this->createTournamentGame (2, 1, '02', BRACKET_GAME, 'pool', 7, 'pool', 10);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2, 3v6, 4v5, optional Loser 1 vs Loser 2 - 9th/10th Place
		$success &= $this->createTournamentGame (3, 2, '03', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (4, 2, '04', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (5, 2, '05', BRACKET_GAME, 'pool', 2, 'game_winner', 2);
		$success &= $this->createTournamentGame (6, 2, '06', BRACKET_GAME, 'pool', 3, 'pool', 6);
		if ($consolation) {
			$success &= $this->createTournamentGame (7, 2, '07', BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (8, 3, '08', BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		$success &= $this->createTournamentGame (9, 3, '09', BRACKET_GAME, 'game_winner', 5, 'game_winner', 6);
		if ($consolation) {
			$success &= $this->createTournamentGame (10, 3, '10', BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
			$success &= $this->createTournamentGame (11, 3, '11', BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (12, 4, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
		if ($bronze) {
			$success &= $this->createTournamentGame (13, 4, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (14, 4, ordinal($this->first_team + 5), BRACKET_GAME, 'game_winner', 10, 'game_winner', 11);
			$success &= $this->createTournamentGame (15, 4, ordinal($this->first_team + 7), BRACKET_GAME, 'game_loser', 10, 'game_loser', 11);
		}

		return $success;
	}

	function createPresemisTen($bronze, $consolation) {
		// Round 1: 1v2, 3v6, 4v5, 7v10, 8v9
		$success = $this->createTournamentGame (1, 1, '01', BRACKET_GAME, 'pool', 1, 'pool', 2);
		$success &= $this->createTournamentGame (2, 1, '02', BRACKET_GAME, 'pool', 3, 'pool', 6);
		$success &= $this->createTournamentGame (3, 1, '03', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (4, 1, '04', BRACKET_GAME, 'pool', 7, 'pool', 10);
		$success &= $this->createTournamentGame (5, 1, '05', BRACKET_GAME, 'pool', 8, 'pool', 9);

		// Round 2: Winner 1 vs Winner 3, Loser 1 vs Winner 2, optional Loser 2 vs Winner 4, Loser 3 vs Winner 5, optional Loser 4 vs Loser 5 - 9th/10th Place game 1
		$success &= $this->createTournamentGame (6, 2, '06', BRACKET_GAME, 'game_winner', 1, 'game_winner', 3);
		$success &= $this->createTournamentGame (7, 2, '07', BRACKET_GAME, 'game_loser', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 2, '08', BRACKET_GAME, 'game_loser', 2, 'game_winner', 4);
			$success &= $this->createTournamentGame (9, 2, '09', BRACKET_GAME, 'game_loser', 3, 'game_winner', 5);
			$success &= $this->createTournamentGame (10, 2, '10', BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (11, 3, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$success &= $this->createTournamentGame (12, 3, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (13, 3, ordinal($this->first_team + 5), BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
			// TODO: Consider swapping L9 with W10 and make game 11 into 9th with L9 v L10, to prevent back-to-back rematch
			$success &= $this->createTournamentGame (14, 3, ordinal($this->first_team + 7), BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);
			$success &= $this->createTournamentGame (15, 3, '11', BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}

		return $success;
	}

	function createQuartersEleven($bronze, $consolation) {
		// Round 1: 8v9, 7v10, 6v11
		$success = $this->createTournamentGame (1, 1, '01', BRACKET_GAME, 'pool', 8, 'pool', 9);
		$success &= $this->createTournamentGame (2, 1, '02', BRACKET_GAME, 'pool', 7, 'pool', 10);
		$success &= $this->createTournamentGame (3, 1, '03', BRACKET_GAME, 'pool', 6, 'pool', 11);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2, 3 vs Winner 3, 4v5, optional Loser 1 vs Loser 2 - game 1 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (4, 2, '04', BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (5, 2, '05', BRACKET_GAME, 'pool', 4, 'pool', 5);
		$success &= $this->createTournamentGame (6, 2, '06', BRACKET_GAME, 'pool', 2, 'game_winner', 2);
		$success &= $this->createTournamentGame (7, 2, '07', BRACKET_GAME, 'pool', 3, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 2, '08', BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers, optional Loser 1 vs Loser 3 - game 2 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (9, 3, '09', BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		$success &= $this->createTournamentGame (10, 3, '10', BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($consolation) {
			$success &= $this->createTournamentGame (11, 3, '11', BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
			$success &= $this->createTournamentGame (12, 3, '12', BRACKET_GAME, 'game_loser', 7, 'game_loser', 6);
			$success &= $this->createTournamentGame (13, 3, '13', BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);
		}

		// Round 4: more winners vs winners, optional losers vs losers, optional Loser 3 vs Loser 2 - game 3 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (14, 4, ordinal($this->first_team + 1), BRACKET_GAME, 'game_winner', 9, 'game_winner', 10);
		if ($bronze) {
			$success &= $this->createTournamentGame (15, 4, ordinal($this->first_team + 3), BRACKET_GAME, 'game_loser', 9, 'game_loser', 10);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (16, 4, ordinal($this->first_team + 5), BRACKET_GAME, 'game_winner', 11, 'game_loser', 12);
			$success &= $this->createTournamentGame (17, 4, ordinal($this->first_team + 7), BRACKET_GAME, 'game_loser', 11, 'game_loser', 12);
			$success &= $this->createTournamentGame (18, 4, '14', BRACKET_GAME, 'game_loser', 3, 'game_loser', 2);
		}

		return $success;
	}

	/**
	 * Create a single tournament game
	 */
	function createTournamentGame($id, $round, $name, $type,
		$home_dependency_type, $home_dependency_id, $away_dependency_type, $away_dependency_id)
	{
		if (array_key_exists($id, $this->games)) {
			$this->_controller->Session->setFlash(__('Duplicate game id, check the scheduling algorithm', true), 'default', array('class' => 'error'));
			return false;
		}

		if (substr ($home_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$home_dependency_resolved = false;
			$home_dependency_field = 'dependency';
		} else if ($home_dependency_type == 'pool' || $home_dependency_type == 'copy') {
			$home_dependency_id = $this->pool['PoolsTeam'][$home_dependency_id - 1]['id'];
			$home_dependency_resolved = true;
			$home_dependency_field = 'pool_team';
		} else {
			$this->_controller->Session->setFlash(sprintf(__('Unknown home dependency type "%s"', true), $home_dependency_type), 'default', array('class' => 'error'));
			return false;
		}

		if (substr ($away_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$away_dependency_resolved = false;
			$away_dependency_field = 'dependency';
		} else if ($away_dependency_type == 'pool' || $away_dependency_type == 'copy') {
			$away_dependency_id = $this->pool['PoolsTeam'][$away_dependency_id - 1]['id'];
			$away_dependency_resolved = true;
			$away_dependency_field = 'pool_team';
		} else {
			$this->_controller->Session->setFlash(sprintf(__('Unknown away dependency type "%s"', true), $away_dependency_type), 'default', array('class' => 'error'));
			return false;
		}

		if ($name === true) {
			$name = $this->pool['Pool']['name'];
		} else if (!empty($name)) {
			$name = $this->pool_name . $name;
		}

		$this->games[$id] = array(
			'home_team' => null,
			'away_team' => null,
			'round' => $round,
			'type' => $type,
			'name' => $name,
			'home_dependency_type' => $home_dependency_type,
			"home_{$home_dependency_field}_id" => $home_dependency_id,
			'home_dependency_resolved' => $home_dependency_resolved,
			'away_dependency_type' => $away_dependency_type,
			"away_{$away_dependency_field}_id" => $away_dependency_id,
			'away_dependency_resolved' => $away_dependency_resolved,
		);

		$this->games[$id]['pool_id'] = $this->pool['Pool']['id'];

		return true;
	}

	function canSchedule($num_fields, $field_counts) {
		$this->pool_times = array();
		foreach ($num_fields as $round => $required) {
			while ($required--) {
				if (!$this->canScheduleOne($round, $field_counts)) {
					$this->_controller->Session->setFlash(sprintf(__('There are insufficient %s available to support the requested schedule.', true), Configure::read('sport.fields')), 'default', array('class' => 'info'));
					return false;
				}
			}
		}

		return true;
	}

	function canScheduleOne($round, &$field_counts) {
		if (empty($field_counts)) {
			return false;
		}

		// If this pool has already had games scheduled, but not in this
		// round, ignore any unused slots in the same time as games
		// in the last round of this pool.
		if (!empty($this->pool_times) && empty($this->pool_times[$round])) {
			$max_round = max(array_keys($this->pool_times));
			$slot_list = min(array_diff(array_keys($field_counts), $this->pool_times[$max_round]));
		} else {
			$slot_list = min(array_keys($field_counts));
		}

		-- $field_counts[$slot_list][0]['count'];
		if ($field_counts[$slot_list][0]['count'] == 0) {
			unset($field_counts[$slot_list]);
		}
		if (empty($this->pool_times[$round])) {
			$this->pool_times[$round] = array();
		}
		$this->pool_times[$round][] = $slot_list;
		return true;
	}

	function assignFieldsByRound($start_date) {
		uasort($this->games, array($this, 'sortByRound'));
		if (is_array($start_date)) {
			list ($date, $x) = explode(' ', min($start_date));
			$separate_days = false;
		} else {
			$date = $start_date;
			$rounds = count(array_unique(Set::extract('/round', $this->games)));
			$dates = count(array_unique(Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>=$start_date]/game_date", $this->division)));
			$separate_days = ($this->division['Division']['schedule_type'] != 'tournament') && ($rounds <= $dates);
		}
		$this->pool_times = array();

		// Extract and sort the list of slots that are available
		$this->slots = Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>=$date]", $this->division);

		// If this division has already had games scheduled in earlier
		// stages, get rid of any unused slots up to the end of the last stage.
		$prior_pools = Set::extract("/Pool[stage<{$this->pool['Pool']['stage']}]/id", $this->division);
		if ($separate_days) {
			$initial = '0000-00-00';
		} else {
			$initial = '0000-00-00 00:00:00';
		}
		$last_game = $initial;
		foreach ($prior_pools as $pool) {
			$last_pool_game = max(Set::extract("/Game[pool_id=$pool]/GameSlot/game_date", $this->division));
			if (!$separate_days) {
				$last_pool_game .= ' ' . max(Set::extract("/Game[pool_id=$pool]/GameSlot[game_date=$last_pool_game]/game_start", $this->division));
			}
			$last_game = max($last_game, $last_pool_game);
		}
		if ($last_game != $initial) {
			foreach ($this->slots as $key => $slot) {
				if ($separate_days) {
					$slot_key = $slot['GameSlot']['game_date'];
				} else {
					$slot_key = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
				}
				if ($slot_key <= $last_game) {
					unset ($this->slots[$key]);
				}
			}
		}

		usort($this->slots, array($this, 'sortByDateAndTime'));

		foreach ($this->games as $key => $game) {
			if ($game['home_dependency_type'] != 'copy') {
				if (is_array($start_date)) {
					list ($date, $time) = explode(' ', $start_date[$game['round']]);
					$game_slot_id = $this->selectRoundGameslot($date, $time, $game['round'], false);
				} else {
					// '0' is a non-blank string which Set::extract can compare to, but will always be less than any actual time
					$game_slot_id = $this->selectRoundGameslot($start_date, '0', $game['round'], $separate_days);
				}
				if ($game_slot_id === false) {
					return false;
				}

				$this->games[$key]['GameSlot'] = array(
					'id' => $game_slot_id,
				);
			}
		}

		return true;
	}

	function selectRoundGameslot($date, $time, $round, $separate_days) {
		// If this pool has already had games scheduled, but not in this
		// round, get rid of any unused slots in the same time as games
		// in the last round of this pool. If we have at least as many
		// days as rounds, get rid of everything on the same day.
		if (!empty($this->pool_times) && empty($this->pool_times[$round])) {
			$max_round = max(array_keys($this->pool_times));
			$used = $this->pool_times[$max_round];
			foreach ($this->slots as $key => $slot) {
				if ($separate_days) {
					$slot_key = $slot['GameSlot']['game_date'];
				} else {
					$slot_key = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
				}
				if (in_array($slot_key, $used)) {
					unset ($this->slots[$key]);
				}
			}
		}

		if (empty($this->slots)) {
			if ($time == '0') {
				$message = sprintf (__('Couldn\'t get a slot ID: date %s, round %s', true), $date, $round);
			} else {
				$message = sprintf (__('Couldn\'t get a slot ID: date %s, time %s, round %s', true), $date, $time, $round);
			}
			$this->_controller->Session->setFlash($message, 'default', array('class' => 'warning'));
			return false;
		}

		$possible_slots = Set::extract("/GameSlot[game_date=$date][game_start>=$time]", $this->slots);
		if (empty($possible_slots)) {
			// No slots at the requested time, or later on the same day. Try any later date.
			$possible_slots = Set::extract("/GameSlot[game_date>$date]", $this->slots);
		}
		if (empty($possible_slots)) {
			// No slots on later date either. Take the last available slot instead.
			$possible_slots = array_reverse($this->slots);
		}
		$slot = array_shift($possible_slots);
		$this->removeGameslot($slot['GameSlot']['id']);
		if (empty($this->pool_times[$round])) {
			$this->pool_times[$round] = array();
		}
		if ($separate_days) {
			$this->pool_times[$round][] = $slot['GameSlot']['game_date'];
		} else {
			$this->pool_times[$round][] = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
		}

		return $slot['GameSlot']['id'];
	}

	function removeGameslot($slot_id) {
		parent::removeGameslot($slot_id);
		foreach ($this->slots as $key => $slot) {
			if ($slot['GameSlot']['id'] == $slot_id) {
				unset ($this->slots[$key]);
				return;
			}
		}
	}

	// Make sure that dependencies are resolved before saving
	function beforeSave($key) {
		if (array_key_exists('home_dependency_resolved', $this->games[$key]) &&
			$this->games[$key]['home_dependency_resolved'] === false)
		{
			$this->_controller->Session->setFlash(__('A game dependency was not resolved before saving the game. Check the scheduling algorithm.', true), 'default', array('class' => 'error'));
			return false;
		}
		if (array_key_exists('away_dependency_resolved', $this->games[$key]) &&
			$this->games[$key]['away_dependency_resolved'] === false)
		{
			$this->_controller->Session->setFlash(__('A game dependency was not resolved before saving the game. Check the scheduling algorithm.', true), 'default', array('class' => 'error'));
			return false;
		}

		return true;
	}

	// Replace this game id with the saved game id in any dependencies
	function afterSave($key) {
		foreach ($this->games as $id => $game) {
			if (array_key_exists('home_dependency_resolved', $game) &&
				$game['home_dependency_resolved'] === false &&
				$game['home_dependency_id'] == $key)
			{
				$this->games[$id]['home_dependency_id'] = $this->_controller->Division->Game->id;
				$this->games[$id]['home_dependency_resolved'] = true;
			}
			if (array_key_exists('away_dependency_resolved', $game) &&
				$game['away_dependency_resolved'] === false &&
				$game['away_dependency_id'] == $key)
			{
				$this->games[$id]['away_dependency_id'] = $this->_controller->Division->Game->id;
				$this->games[$id]['away_dependency_resolved'] = true;
			}
		}

		return true;
	}

	function sortByDateAndTime($a, $b) {
		if ($a['GameSlot']['game_date'] > $b['GameSlot']['game_date']) {
			return 1;
		} else if ($a['GameSlot']['game_date'] < $b['GameSlot']['game_date']) {
			return -1;
		}
		if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		} else if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		}
		return 0;
	}

	function sortByRound($a, $b) {
		if ($a['round'] > $b['round']) {
			return 1;
		} else if ($a['round'] < $b['round']) {
			return -1;
		}

		if (strpos($a['name'], '-') !== false) {
			list($x, $a_name) = explode('-', $a['name']);
		} else {
			$a_name = $a['name'];
		}
		if (strpos($b['name'], '-') !== false) {
			list($x, $b_name) = explode('-', $b['name']);
		} else {
			$b_name = $b['name'];
		}

		if ($a_name > $b_name) {
			return 1;
		} else if ($a_name < $b_name) {
			return -1;
		}

		return 0;
	}
}

?>
