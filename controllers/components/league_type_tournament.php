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
	 * Remember details about the round most recently scheduled, so that when
	 * we get to the next round we make sure to advance the time.
	 */
	var $last_round = 0;
	var $last_date = null;
	var $last_time = null;

	/**
	 * Remember details about the block of games currently being scheduled,
	 * for use when we're scheduling several blocks at once.
	 */
	var $start_date = null;
	var $block_name = '';
	var $first_team = 0;
	var $first_game = 0;

	/**
	 * Cached list of game slots that we have available
	 */
	var $slots = null;

	function scheduleOptions($num_teams) {
		$types = array(
			'single' => 'single blank, unscheduled game (2 teams, one field)',
			'blankset' => "set of blank unscheduled games for all teams in the division ($num_teams teams, " . ($num_teams / 2) . " games)",
			);

		// Add more types, depending on the number of teams
		switch ($num_teams) {
			case 2:
				$types['home_and_home'] = '"home and home" series';
				break;

			case 3:
				// Round-robin?
				break;

			case 4:
				$types['semis_consolation'] = 'semi-finals, finals and 3rd place';
				$types['semis_elimination'] = 'semi-finals and finals, no 3rd place';
				// Round-robin?
				break;

			case 5:
				$types['semis_consolation_five'] = 'semi-finals and finals, plus a 5th place play-in';
				break;

			case 6:
				$types['semis_consolation_six'] = 'semi-finals and finals, plus 5th and 6th place play-ins';
				$types['semis_complete_six'] = 'semi-finals and finals, plus 5th and 6th place play-ins, everyone gets 3 games';
				$types['semis_minimal_six'] = 'semi-finals and finals, 5th and 6th have consolation games, everyone gets 2 games';
				// Two 3-team round-robins plus finals?
				// Two 3-team round-robins plus quarters?
				break;

			case 7:
				$types['quarters_consolation_seven'] = 'quarter-finals, semi-finals, finals, and all placement games, with a first-round bye for the top seed';
				break;

			case 8:
				$types['quarters_consolation'] = 'quarter-finals, semi-finals, finals, and all placement games';
				$types['quarters_bronze'] = 'quarter-finals, semi-finals, finals and 3rd place, but no consolation bracket';
				$types['quarters_elimination'] = 'quarter-finals, semi-finals and finals, no placement games';
				$types['brackets_of_4'] = 'seeded split into 2 brackets of 4 teams each';
				break;

			case 9:
				$types['quarters_consolation_nine'] = 'quarter-finals, semi-finals and finals, plus a 9th place play-in';
				// Three 3-team round-robins plus quarters?
				break;

			case 10:
				$types['quarters_consolation_ten'] = 'quarter-finals, semi-finals and finals, plus 9th and 10th place play-ins';
				$types['presemis_consolation_ten'] = 'pre-semi-finals, semi-finals and finals, everyone gets 3 games';
				// Two 5-team round-robins plus quarters?
				// 3+3+4-team round-robins plus quarters?
				break;

			case 11:
				$types['quarters_consolation_eleven'] = 'quarter-finals, semi-finals and finals, plus 9th, 10th and 11th place play-ins';
				// 3+4+4-team round-robins plus quarters?
				break;

			// For anything with 12+ teams, offer 4 and 8 team bracket options
			default:
				$singular = 'seeded split into %d bracket of %d teams';
				$plural = 'seeded split into %d brackets of %d teams each';
				foreach (array(4,8) as $size) {
					list($x,$r) = $this->splitBrackets($num_teams, $size);
					$desc = sprintf($x == 1 ? $singular : $plural, $x, $size);
					if ($r) {
						$desc .= ", plus a bracket of $r teams";
					}
					$types["brackets_of_{$size}"] = $desc;
				}
				break;
		}

		return $types;
	}

	function splitBrackets($num_teams, $size) {
		$x = floor($num_teams / $size);
		$r = $num_teams % $size;
		if ($r == 1) {
			$r += $size;
			-- $x;
		}
		return array($x, $r);
	}

	function scheduleRequirements($type, $num_teams, $overflow_type = null) {
		switch($type) {
			case 'single':
				return array(1);
			case 'blankset':
				return array($num_teams / 2);
			case 'home_and_home':
				return array(1, 1);
			case 'semis_consolation':
				return array(2, 2);
			case 'semis_elimination':
				return array(2, 1);
			case 'semis_consolation_five':
				return array(1, 2, 2, 2);
			case 'semis_complete_six':
				return array(3, 3, 3);
			case 'semis_consolation_six':
				return array(2, 3, 2);
			case 'semis_minimal_six':
				return array(3, 3);
			case 'quarters_consolation_seven':
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
			case 'brackets_of_4':
				list ($x, $r) = $this->splitBrackets($num_teams, 4);
				$req = array_fill (1, $x, array(2, 2));
				if (!empty ($overflow_type)) {
					$req[] = $this->scheduleRequirements($overflow_type, 0);
				}
				return $req;
			case 'brackets_of_8':
				list ($x, $r) = $this->splitBrackets($num_teams, 8);
				$req = array_fill (1, $x, array(4, 4, 4));
				if (!empty ($overflow_type)) {
					$req[] = $this->scheduleRequirements($overflow_type, 0);
				}
				return $req;
		}
	}

	function createSchedule($division_id, $exclude_teams, $type, $start_date, $publish, $overflow_type, $names) {
		if (!$this->startSchedule($division_id, $exclude_teams, $start_date) ||
			!$this->createScheduleBlock($division_id, $exclude_teams, $type, $start_date, $publish, $overflow_type, $names) ||
			!$this->assignFieldsByRound())
		{
			return false;
		}
		return $this->finishSchedule($division_id, $publish);
	}

	function createScheduleBlock($division_id, $exclude_teams, $type, $start_date, $publish, $overflow_type, $names, $pool = 1, $first_team = 0) {
		$this->startBlock($start_date, $names, $pool, $first_team);

		switch($type) {
			case 'single':
				// Create single game
				$ret = $this->createEmptyGame($start_date);
				break;
			case 'blankset':
				// Create game for all teams in division
				$ret = $this->createEmptySet($start_date);
				break;
			case 'home_and_home':
				$ret = $this->createHomeAndHome();
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
			case 'brackets_of_4':
				$num_teams = count($this->division['Team']);
				list($x,$r) = $this->splitBrackets($num_teams, 4);
				$ret = true;
				for ($i = 0; $i < $x; ++$i) {
					$this->startBlock($start_date, $names[$i + 1], $i + 1, $i * 4);
					$ret &= $this->createSemis(true);
				}
				// Also handle the overflow type, if any
				if ($overflow_type) {
					$ret &= $this->createScheduleBlock($division_id, $exclude_teams, $overflow_type, $start_date, $publish, null, $names[$i + 1], $i + 1, $i * 4);
				}
				break;
			case 'brackets_of_8':
				$num_teams = count($this->division['Team']);
				list($x,$r) = $this->splitBrackets($num_teams, 8);
				$ret = true;
				for ($i = 0; $i < $x; ++$i) {
					$this->startBlock($start_date, $names[$i + 1], $i + 1, $i * 8);
					$ret = $this->createQuarters(true, true);
				}
				// Also handle the overflow type, if any
				if ($overflow_type) {
					$ret &= $this->createScheduleBlock($division_id, $exclude_teams, $overflow_type, $start_date, $publish, null, $names[$i + 1], $i + 1, $i * 8);
				}
				break;
		}

		return $ret;
	}

	/*
	 * Create an empty set of games for this division
	 */
	function createEmptySet($date) {
		$num_teams = count($this->division['Team']);

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
			$success &= $this->createEmptyGame($date);
		}

		return $success;
	}

	function createHomeAndHome() {
		// Round 1: 1v2
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 2);

		// Round 2: 2v1
		$success &= $this->createTournamentGame (2, 2, 'B', 'seed', 2, 'seed', 1);

		return $success;
	}

	function createSemis($consolation) {
		// Round 1: 1v4, 2v3
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 4);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 2, 'seed', 3);

		// Round 2: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (3, 2, ordinal($this->first_team + 1), 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (4, 2, ordinal($this->first_team + 3), 'game_loser', 1, 'game_loser', 2);
		}

		return $success;
	}

	function createSemisFive($consolation) {
		// Round 1: 4 vs 5
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 4, 'seed', 5);

		// Round 2: 1 vs Winner A, 2 vs 3
		$success &= $this->createTournamentGame (2, 2, 'B', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (3, 2, 'C', 'seed', 2, 'seed', 3);

		// Round 3: Winner B vs Winner C 1st/2nd Place, optional Loser A vs Loser C - Loser 5th Place
		$success &= $this->createTournamentGame (4, 3, ordinal($this->first_team + 1), 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (5, 3, 'D', 'game_loser', 1, 'game_loser', 3);

			// Round 4: Winner D vs Loser B 3rd/4th Place
			$success &= $this->createTournamentGame (6, 4, ordinal($this->first_team + 3), 'game_winner', 5, 'game_loser', 2);
		}

		return $success;
	}

	function createCompleteSix($consolation) {
		// Round 1: 1 vs 2, 4 vs 5, 3 vs 6
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 2);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 3, 'seed', 6);

		// Round 2: Winner A vs Winner B, Loser A vs Winner C, Loser B vs Loser C
		$success &= $this->createTournamentGame (4, 2, 'D', 'game_winner', 1, 'game_winner', 2);
		$success &= $this->createTournamentGame (5, 2, 'E', 'game_winner', 3, 'game_loser', 1);
		$success &= $this->createTournamentGame (6, 2, 'F', 'game_loser', 2, 'game_loser', 3);

		// Round 3: Winner D vs Winner E 1st/2nd Place, optional consolation games
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), 'game_loser', 5, 'game_winner', 6);
			$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 5), 'game_loser', 4, 'game_loser', 6);
		}

		return $success;
	}

	function createSemisSix($consolation) {
		// Round 1: 4 vs 6, 3 vs 5
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 4, 'seed', 6);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 3, 'seed', 5);

		// Round 2: 1 vs Winner A, 2 vs Winner B, optional Loser A vs Loser B 5th/6th Place
		$success &= $this->createTournamentGame (3, 2, 'C', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (4, 2, 'D', 'seed', 2, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (5, 2, ordinal($this->first_team + 5), 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: Winner C vs Winner D 1st/2nd Place, optional Loser C vs Loser D 3rd/4th Place
		$success &= $this->createTournamentGame (6, 3, ordinal($this->first_team + 1), 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 3), 'game_loser', 3, 'game_loser', 4);
		}

		return $success;
	}

	function createMinimalSix($consolation) {
		// Round 1: 1 vs 4, 2 vs 3, 5 vs 6
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 4);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 2, 'seed', 3);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 5, 'seed', 6);

		// Round 2: Winner A vs Winner B, optional Loser B vs Winner C, Loser A vs Loser C
		$success &= $this->createTournamentGame (4, 2, ordinal($this->first_team + 1), 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (5, 2, ordinal($this->first_team + 3), 'game_loser', 2, 'game_winner', 3);
			$success &= $this->createTournamentGame (6, 2, ordinal($this->first_team + 5), 'game_loser', 1, 'game_loser', 3);
		}

		return $success;
	}

	function createQuartersSeven($bronze, $consolation) {
		// Round 1: 4 vs 5, 2 vs 7, 3 vs 6
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 2, 'seed', 7);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 3, 'seed', 6);

		// Round 2: 1 vs Winner A, Winner B vs Winner C, optional Loser A vs Loser B - game 1 of round robin for 5th/6th/7th
		$success &= $this->createTournamentGame (4, 2, 'D', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (5, 2, 'E', 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (6, 2, 'F', 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: Winner D vs Winner E 1st/2nd Place, optional Loser D vs Loser E 3rd/4th Place, optional Loser A vs Loser C - game 2 of round robin for 5th/6th/7th
		$success &= $this->createTournamentGame (7, 3, ordinal($this->first_team + 1), 'game_winner', 4, 'game_winner', 5);
		if ($bronze) {
			$success &= $this->createTournamentGame (8, 3, ordinal($this->first_team + 3), 'game_loser', 4, 'game_loser', 5);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (9, 3, 'G', 'game_loser', 3, 'game_loser', 1);

			// Round 4: Loser C vs Loser B - game 3 of round robin for 5th/6th/7th
			$success &= $this->createTournamentGame (10, 4, 'H', 'game_loser', 2, 'game_loser', 3);
		}

		return $success;
	}

	function createQuarters($bronze, $consolation) {
		// Round 1: 1v8, 2v7, etc.
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 8);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 2, 'seed', 7);
		$success &= $this->createTournamentGame (4, 1, 'D', 'seed', 3, 'seed', 6);

		// Round 2: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (5, 2, 'E', 'game_winner', 1, 'game_winner', 2);
		$success &= $this->createTournamentGame (6, 2, 'F', 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$success &= $this->createTournamentGame (7, 2, 'G', 'game_loser', 1, 'game_loser', 2);
			$success &= $this->createTournamentGame (8, 2, 'H', 'game_loser', 3, 'game_loser', 4);
		}

		// Round 3: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (9, 3, ordinal($this->first_team + 1), 'game_winner', 5, 'game_winner', 6);
		if ($bronze) {
			$success &= $this->createTournamentGame (10, 3, ordinal($this->first_team + 3), 'game_loser', 5, 'game_loser', 6);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (11, 3, ordinal($this->first_team + 5), 'game_winner', 7, 'game_winner', 8);
			$success &= $this->createTournamentGame (12, 3, ordinal($this->first_team + 7), 'game_loser', 7, 'game_loser', 8);
		}

		return $success;
	}

	function createQuartersNine($bronze, $consolation) {
		// Round 1: 8v9
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 8, 'seed', 9);

		// Round 2: 1 vs Winner A, 2v7, 3v6, 4v5
		$success &= $this->createTournamentGame (2, 2, 'B', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (3, 2, 'C', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (4, 2, 'D', 'seed', 2, 'seed', 7);
		$success &= $this->createTournamentGame (5, 2, 'E', 'seed', 3, 'seed', 6);

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (6, 3, 'F', 'game_winner', 2, 'game_winner', 3);
		$success &= $this->createTournamentGame (7, 3, 'G', 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 3, 'H', 'game_loser', 1, 'game_loser', 4);
			$success &= $this->createTournamentGame (9, 3, 'I', 'game_loser', 2, 'game_loser', 5);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (10, 4, ordinal($this->first_team + 1), 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$success &= $this->createTournamentGame (11, 4, ordinal($this->first_team + 3), 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (12, 4, 'J', 'game_winner', 8, 'game_loser', 3);
			$success &= $this->createTournamentGame (13, 4, ordinal($this->first_team + 8), 'game_loser', 8, 'game_loser', 9);

			// Round 5: optional Winner J vs Winner I - 5th/6th Place
			$success &= $this->createTournamentGame (14, 5, ordinal($this->first_team + 5), 'game_winner', 12, 'game_winner', 9);
		}

		return $success;
	}

	function createQuartersTen($bronze, $consolation) {
		// Round 1: 8v9, 7v10
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 8, 'seed', 9);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 7, 'seed', 10);

		// Round 2: 1 vs Winner A, 2 vs Winner B, 3v6, 4v5, optional Loser A vs Loser B - 9th/10th Place
		$success &= $this->createTournamentGame (3, 2, 'C', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (4, 2, 'D', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (5, 2, 'E', 'seed', 2, 'game_winner', 2);
		$success &= $this->createTournamentGame (6, 2, 'F', 'seed', 3, 'seed', 6);
		if ($consolation) {
			$success &= $this->createTournamentGame (7, 2, 'G', 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (8, 3, 'H', 'game_winner', 3, 'game_winner', 4);
		$success &= $this->createTournamentGame (9, 3, 'I', 'game_winner', 5, 'game_winner', 6);
		if ($consolation) {
			$success &= $this->createTournamentGame (10, 3, 'J', 'game_loser', 3, 'game_loser', 4);
			$success &= $this->createTournamentGame (11, 3, 'K', 'game_loser', 5, 'game_loser', 6);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (12, 4, ordinal($this->first_team + 1), 'game_winner', 8, 'game_winner', 9);
		if ($bronze) {
			$success &= $this->createTournamentGame (13, 4, ordinal($this->first_team + 3), 'game_loser', 8, 'game_loser', 9);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (14, 4, ordinal($this->first_team + 5), 'game_winner', 10, 'game_winner', 11);
			$success &= $this->createTournamentGame (15, 4, ordinal($this->first_team + 7), 'game_loser', 10, 'game_loser', 11);
		}

		return $success;
	}

	function createPresemisTen($bronze, $consolation) {
		// Round 1: 1v2, 3v6, 4v5, 7v10, 8v9
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 1, 'seed', 2);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 3, 'seed', 6);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (4, 1, 'D', 'seed', 7, 'seed', 10);
		$success &= $this->createTournamentGame (5, 1, 'E', 'seed', 8, 'seed', 9);

		// Round 2: winner A vs winner C, loser A vs winner B, optional loser B vs winner D, loser C vs winner E, optional Loser D vs Loser E - 9th/10th Place game 1
		$success &= $this->createTournamentGame (6, 2, 'F', 'game_winner', 1, 'game_winner', 3);
		$success &= $this->createTournamentGame (7, 2, 'G', 'game_loser', 1, 'game_winner', 2);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 2, 'H', 'game_loser', 2, 'game_winner', 4);
			$success &= $this->createTournamentGame (9, 2, 'I', 'game_loser', 3, 'game_winner', 5);
			$success &= $this->createTournamentGame (10, 2, 'J', 'game_loser', 4, 'game_loser', 5);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$success &= $this->createTournamentGame (11, 3, ordinal($this->first_team + 1), 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$success &= $this->createTournamentGame (12, 3, ordinal($this->first_team + 3), 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (13, 3, ordinal($this->first_team + 5), 'game_winner', 8, 'game_winner', 9);
			$success &= $this->createTournamentGame (14, 3, ordinal($this->first_team + 7), 'game_loser', 8, 'game_loser', 9);
			$success &= $this->createTournamentGame (15, 3, 'K', 'game_loser', 4, 'game_loser', 5);
		}

		return $success;
	}

	function createQuartersEleven($bronze, $consolation) {
		// Round 1: 8v9, 7v10, 6v11
		$success = $this->createTournamentGame (1, 1, 'A', 'seed', 8, 'seed', 9);
		$success &= $this->createTournamentGame (2, 1, 'B', 'seed', 7, 'seed', 10);
		$success &= $this->createTournamentGame (3, 1, 'C', 'seed', 6, 'seed', 11);

		// Round 2: 1 vs Winner A, 2 vs Winner B, 3 vs Winner C, 4v5, optional Loser A vs Loser B - game 1 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (4, 2, 'D', 'seed', 1, 'game_winner', 1);
		$success &= $this->createTournamentGame (5, 2, 'E', 'seed', 4, 'seed', 5);
		$success &= $this->createTournamentGame (6, 2, 'F', 'seed', 2, 'game_winner', 2);
		$success &= $this->createTournamentGame (7, 2, 'G', 'seed', 3, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame (8, 2, 'H', 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers, optional Loser A vs Loser C - game 2 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (9, 3, 'I', 'game_winner', 4, 'game_winner', 5);
		$success &= $this->createTournamentGame (10, 3, 'J', 'game_winner', 6, 'game_winner', 7);
		if ($consolation) {
			$success &= $this->createTournamentGame (11, 3, 'K', 'game_loser', 4, 'game_loser', 5);
			$success &= $this->createTournamentGame (12, 3, 'L', 'game_loser', 7, 'game_loser', 6);
			$success &= $this->createTournamentGame (13, 3, 'M', 'game_loser', 1, 'game_loser', 3);
		}

		// Round 4: more winners vs winners, optional losers vs losers, optional Loser C vs Loser B - game 3 of round robin for 9th/10th/11th Place
		$success &= $this->createTournamentGame (14, 4, ordinal($this->first_team + 1), 'game_winner', 9, 'game_winner', 10);
		if ($bronze) {
			$success &= $this->createTournamentGame (15, 4, ordinal($this->first_team + 3), 'game_loser', 9, 'game_loser', 10);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame (16, 4, ordinal($this->first_team + 5), 'game_winner', 11, 'game_loser', 12);
			$success &= $this->createTournamentGame (17, 4, ordinal($this->first_team + 7), 'game_loser', 11, 'game_loser', 12);
			$success &= $this->createTournamentGame (18, 4, 'N', 'game_loser', 3, 'game_loser', 2);
		}

		return $success;
	}

	function startBlock($start_date, $block_name, $pool, $first_team) {
		$this->start_date = $start_date;
		if (is_string($block_name)) {
			$this->block_name = $block_name;
		} else {
			$this->block_name = '';
		}
		$this->pool = $pool;
		$this->first_team = $first_team;
		if (!empty($this->games)) {
			$this->first_game = max(array_keys($this->games));
		}
	}

	/**
	 * Create a single tournament game
	 */
	function createTournamentGame($id, $round, $name,
		$home_dependency_type, $home_dependency_id, $away_dependency_type, $away_dependency_id)
	{
		// Increment so that the first game in any block has a unique id
		$id += $this->first_game;

		$block_name = $this->block_name;
		if (!empty($block_name)) {
			$block_name .= '-';
		}

		if (array_key_exists($id, $this->games)) {
			$this->_controller->Session->setFlash(__('Duplicate game id, check the scheduling algorithm', true), 'default', array('class' => 'error'));
			return false;
		}

		if (substr ($home_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$home_dependency_resolved = false;
			$home_dependency_id += $this->first_game;
		} else {
			$home_dependency_resolved = true;
			$home_dependency_id += $this->first_team;
		}

		if (substr ($away_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$away_dependency_resolved = false;
			$away_dependency_id += $this->first_game;
		} else {
			$away_dependency_resolved = true;
			$away_dependency_id += $this->first_team;
		}

		$this->games[$id] = array(
			'home_team' => null,
			'away_team' => null,
			'round' => $round,
			'tournament' => true,
			'tournament_pool' => $this->pool,
			'name' => $block_name . $name,
			'home_dependency_type' => $home_dependency_type,
			'home_dependency_id' => $home_dependency_id,
			'home_dependency_resolved' => $home_dependency_resolved,
			'away_dependency_type' => $away_dependency_type,
			'away_dependency_id' => $away_dependency_id,
			'away_dependency_resolved' => $away_dependency_resolved,
		);

		return true;
	}

	function assignFieldsByRound() {
		uasort($this->games, array($this, 'sortByRound'));

		foreach ($this->games as $key => $game) {
			$game_slot_id = $this->selectRoundGameslot($this->start_date, $game['round']);
			if ($game_slot_id === false) {
				return false;
			}

			$this->games[$key]['GameSlot'] = array(
				'id' => $game_slot_id,
			);
		}

		return true;
	}

	function selectRoundGameslot($date, $round) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}

		// Extract and sort the list of slots that are available
		if ($this->slots === null) {
			$this->slots = Set::extract("/DivisionGameslotAvailability/GameSlot[game_date>=$date]", $this->division);
			usort($this->slots, array($this, 'sortByDateAndTime'));
		}

		// If we've gone to the next round, get rid of any unused slots in
		// the same time as the last round
		if ($round != $this->last_round) {
			$this->last_round = $round;
			foreach ($this->slots as $key => $slot) {
				if ($slot['GameSlot']['game_date'] === $this->last_date && $slot['GameSlot']['game_start'] === $this->last_time) {
					unset ($this->slots[$key]);
				}
			}
		}

		if (empty ($this->slots)) {
			$message = sprintf (__('Couldn\'t get a slot ID: date %s, round %s', true), $date, $round);
			if (!empty($this->block_name)) {
				$message .= sprintf (__(', block %s', true), $this->block_name);
			}
			$this->_controller->Session->setFlash($message, 'default', array('class' => 'warning'));
			return false;
		}

		$slot = array_shift($this->slots);
		$this->removeGameslot($slot['GameSlot']['id']);
		$this->last_date = $slot['GameSlot']['game_date'];
		$this->last_time = $slot['GameSlot']['game_start'];

		return $slot['GameSlot']['id'];
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
		if ($a['name'] > $b['name']) {
			return 1;
		} else if ($a['name'] < $b['name']) {
			return -1;
		}
		return 0;
	}
}

?>
