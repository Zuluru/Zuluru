<?php

/**
 * Derived class for implementing functionality for the ratings ladder.
 */

class LeagueTypeRatingsLadderComponent extends LeagueTypeComponent
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'ladder';

	function addMenuItems ($league, $is_coordinator = false) {
		if ($this->_controller->is_admin || $is_coordinator) {
			$this->_controller->_addMenuItem ('Adjust ratings', array('controller' => 'leagues', 'action' => 'ratings', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		}
	}

	/**
	 * Sort a ladder league by:
	 * 1: Rating
	 * 2: Spirit
	 * 3: Wins/ties
	 * 4: +/-
	 * 5: Goals for
	 */
	static function compareTeams($a, $b) {
		if ($a['rating'] < $b['rating'])
			return 1;
		if ($a['rating'] > $b['rating'])
			return -1;

		// TODO: Compare spirit scores?

		if (array_key_exists ('results', $a))
		{
			if ($a['results']['pts'] < $b['results']['pts'])
				return 1;
			if ($a['results']['pts'] > $b['results']['pts'])
				return -1;

			if ($a['results']['gf'] - $a['results']['ga'] < $b['results']['gf'] - $b['results']['ga'])
				return 1;
			if ($a['results']['gf'] - $a['results']['ga'] > $b['results']['gf'] - $b['results']['ga'])
				return -1;

			if ($a['results']['gf'] < $b['results']['gf'])
				return 1;
			if ($a['results']['gf'] > $b['results']['gf'])
				return -1;
		}

		return 0;
	}

	function schedulingFields($is_admin, $is_coordinator) {
		if ($is_admin || $is_coordinator) {
			return array(
				'games_before_repeat' => array(
					'label' => 'Games Before Repeat',
					'options' => Configure::read('options.games_before_repeat'),
					'empty' => '---',
					'after' => __('The number of games before two teams can be scheduled to play each other again.', true),
					'required' => true,	// Since this is not in the model validation list, we must force this
				),
			);
		} else {
			return array();
		}
	}

	function schedulingFieldsValidation() {
		return array(
			'games_before_repeat' => array(
				'inlist' => array(
					'rule' => array('inconfig', 'options.games_before_repeat'),
					'message' => 'You must select a valid number of games before repeat.',
				),
			),
		);
	}

	function scheduleOptions($num_teams) {
		$types = array(
			'single' => 'single blank, unscheduled game (2 teams, one field, one day)',
			'oneset_ratings_ladder' => "set of ratings-scheduled games for all teams ($num_teams teams, " . ($num_teams / 2) . " games, one day)"
		);

		return $types;
	}

	function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				$num_fields = 1;
				break;
			case 'oneset_ratings_ladder':
				$num_fields = ($num_teams / 2);
				break;
		}
		return array(1, $num_fields);
	}

	function createSchedule($league_id, $exclude_teams, $type, $start_date, $publish) {
		if (!$this->startSchedule($league_id, $exclude_teams, $start_date))
			return false;

		switch($type) {
			case 'single':
				// Create single game
				$ret = $this->createEmptyGame($start_date);
				break;
			case 'oneset_ratings_ladder':
				// Create game for all teams in league
				$ret = $this->createScheduledSet($start_date);
				break;
		}

		if (!$ret) {
			return false;
		}
		return $this->finishSchedule($league_id, $publish);
	}

	/*
	 * Create a scheduled set of games for this league
	 */
	function createScheduledSet($date) {
		$num_teams = count($this->league['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true));
			return false;
		}

		if ($num_teams % 2) {
			$this->_controller->Session->setFlash(__('Must have even number of teams', true));
			return false;
		}

		// Sort teams so ratings scheduling works properly
		$this->sort($this->league);

		return $this->scheduleOneSet($date, $this->league['Team']);
	}

	/**
	 * Schedule one set of games using the ratings_ladder scheme!
	 */
	function scheduleOneSet($date, $teams) {
		$games_before_repeat = $this->league['League']['games_before_repeat'];
		$min_games_before_repeat = 0;
		$max_retries = $this->league['League']['schedule_attempts'];
		$ret = false;

		$versus_teams = array();
		$gbr_diff = array();
		$seed_closeness = array();
		$ratings_closeness = array();

		for ($j = 0; $j < $max_retries; $j++) {
			set_time_limit(45); // Give this one call 45 seconds to return
			list($ret, $versus_teams_try, $gbr_diff_try, $seed_closeness_try, $ratings_closeness_try) =
					$this->scheduleOneSetTry( $teams, $games_before_repeat, $j%2);

			if ($ret == false) {
				continue;
			}

			// Keep the best schedule by checking how many times we had to decrement
			// the games_before_repeat restriction in order to be able to generate
			// this schedule...

			// The best possible schedule will first have the smallest games before repeat sum,
			// then will have the smallest seed_closeness, and then will have the smallest ratings_closeness
			if (	( count($gbr_diff) == 0  ||  array_sum($gbr_diff) > array_sum($gbr_diff_try) ) ||
					( array_sum($gbr_diff) == array_sum($gbr_diff_try) && array_sum($seed_closeness) > array_sum($seed_closeness_try) ) ||
					( array_sum($gbr_diff) == array_sum($gbr_diff_try) && array_sum($seed_closeness) == array_sum($seed_closeness_try) && array_sum($ratings_closeness) > array_sum($ratings_closeness_try) ) )
			{
				$versus_teams = $versus_teams_try;
				$gbr_diff = $gbr_diff_try;
				$seed_closeness = $seed_closeness_try;
				$ratings_closeness = $ratings_closeness_try;
			}

			if (array_sum($seed_closeness) == sizeof($teams)/2) {
				// that's enough - don't bother getting any more, you have a perfect schedule (ie: 1 vs 2, 3 vs 4, etc).
				break;
			}
		}

		// Now, call assignFields() to actually create the games
		if (!$this->assignFields($date, $versus_teams)) {
			return false;
		}

		// TODO: A better way to do this, it's crap...
		$output = "<div class='schedule'>";
		$output .= "<table align=center>";
		$output .= "<tr><td class='column-heading'>Team 1</td><td class='column-heading'>Team 2</td>";
		$output .= "<td class='column-heading'>Seed Diff<br>(". array_sum($seed_closeness) .")</td><td class='column-heading'>Played each other<br>X games ago...</td></tr>";
		$team_idx = 0;
		for ($i = 0; $i < count($gbr_diff); $i++)  {
			$font = "black";
			$played = $gbr_diff[$i];
			if ($played != 0) {
				$font = "red";
				$played = $games_before_repeat - $gbr_diff[$i] + 1;
			} else {
				$played = "&nbsp;";
			}
			$output .= "<tr>";
			$output .= "<td><font color=$font>" . $versus_teams[$team_idx++]['name'] . "</font></td>";
			$output .= "<td><font color=$font>" . $versus_teams[$team_idx++]['name'] . "</font></td>";
			$output .= "<td><font color=$font>" . $seed_closeness[$i] . "</font></td>";
			$output .= "<td align=center><font color=$font>" . $played . "</font></td>";
			$output .= "</tr>\n";
		}
		$output .= "</table>\n";
		$output .= "</div>\n";
		$this->_controller->Session->setFlash($output);

		return $ret;
	}

	/**
	 * This does the actual work of scheduling a one set rattings_ladder set of games.
	 * However it has some problems where it may not properly schedule all
	 * the games.  If it runs into problems then we use the wrapper
	 * function that calls this one to retry it.
	 * If any problems are found then this function rolls back it's changes.
	 *
	 * The algorithm is as follows...
	 * - start at either top or bottom of ordered ladder
	 * - grab a "group" of teams, starting with a group size of 1 (and increasing to a per-league-defined MAX)
	 * - take the first team in the group, and find a random opponent within the group that meets the GBR criteria
	 * - remove those 2 teams from the ordered ladder and repeat
	 *
	 */
	function scheduleOneSetTry($teams, $games_before_repeat, $down) {
		$ratings_closeness = array();
		$seed_closeness = array();
		$gbr_diff = array();
		$versus_teams = array();

		// TODO: make this maximum a per-league variable, and enforce it in the caller function?
		// maximum standings difference of matched teams:
		$MAX_STANDINGS_DIFF = 8;
		// NOTE: that's not REALLY the max standings diff...
		// it's more like the max grouping of teams to use as possible opponents, and they
		// may be well over 8 seeds apart...

		// current standings diff (starts at 1, counts up to MAX_STANDINGS_DIFF)
		$CURRENT_STANDINGS_DIFF = 1;

		$NUM_TIMES_TO_TRY_CURRENT = 10;

		if ($down) {
			$teams = array_reverse($teams);  // go up instead
		}

		// copy the games before repeat variable
		$gbr = $games_before_repeat;
		// copy the teams array
		$workingteams = $teams;

		// main loop - go through all of the teams
		while(sizeof($workingteams) > 0) {
			// start with the first team (remove from array)
			$current_team = array_shift($workingteams);

			// get the group of teams that are possible opponents
			$possible_opponents = array_slice ($workingteams, 0, $CURRENT_STANDINGS_DIFF);

			// now, loop through the possible opponents and save only the ones who have not been in recent games
			$recent_opponents = $this->getRecentOpponents($current_team['id'], $gbr);
			foreach ($possible_opponents as $key => $po) {
				if (in_array ($po['id'], $recent_opponents)) {
					unset ($possible_opponents[$key]);
				}
			}

			// if at this point there are no possible opponents, then you have to relax one of the restrictions:
			if (sizeof($possible_opponents) == 0 ) {
				if ($NUM_TIMES_TO_TRY_CURRENT > 0) {
					$NUM_TIMES_TO_TRY_CURRENT--;
				} else if ($CURRENT_STANDINGS_DIFF < $MAX_STANDINGS_DIFF) {
					$NUM_TIMES_TO_TRY_CURRENT = 10;
					// try increasing the current standings diff...
					$CURRENT_STANDINGS_DIFF++;
				} else {
					$NUM_TIMES_TO_TRY_CURRENT = 10;
					$CURRENT_STANDINGS_DIFF = 1;
					// try to decrease games before repeat:
					$gbr--;
				}

				// but, if games before repeat goes negative, you're screwed!
				if ($gbr < 0) {
					// TODO: Return error message
					print "<br><b>FAILURE: scheduler cannot find valid opponents for " . $current_team['id'] . "</b></br>";
					return false;
				}

				// now, before starting over, put back some stuff...

				// put back the teams:
				$workingteams = $teams;

				// reset these arrays
				$ratings_closeness = array();
				$seed_closeness = array();
				$gbr_diff = array();
				$versus_teams = array();

				// start over:
				continue;

			} // end if sizeof possible opponents

			// now find them an opponent by randomly choosing one of the remaining possible opponents
			shuffle($possible_opponents);
			$opponent = $possible_opponents[0];

			// remove the opponent from the remaining list of teams
			foreach ($workingteams as $key => $team) {
				if ($team['id'] == $opponent['id']) {
					unset ($workingteams[$key]);
					break;
				}
			}

			// Create the matchup
			$versus_teams[] = $current_team;
			$versus_teams[] = $opponent;
			$gbr_diff[] = $games_before_repeat - $gbr;

			$counter = 0;
			$seed1 = 0;
			$seed2 = 0;
			$rating1 = $current_team['rating'];
			$rating2 = $opponent['rating'];
			foreach ($teams as $t) {
				$counter++;
				if ($t['id'] == $current_team['id']) {
					$seed1 = $counter;
				}
				if ($t['id'] == $opponent['id']) {
					$seed2 = $counter;
				}
				if ($seed1 != 0 && $seed2 != 0) {
					break;
				}
			}
			$seed_closeness[] = abs($seed2-$seed1);
			$ratings_closeness[] = pow($rating1-$rating2, 2);
		} // main loop

		return array(true, $versus_teams, $gbr_diff, $seed_closeness, $ratings_closeness);
	}

	function getRecentOpponents($teamid, $gbr) {
		// Find all of the recent games where this team participated
		$games = array_merge (
			Set::extract ("/Game[home_team=$teamid]/.", $this->league),
			Set::extract ("/Game[away_team=$teamid]/.", $this->league));

		// Extract the last few
		usort ($games, array($this, 'cmpGameDate'));
		$recent_games = array_slice ($games, -$gbr);

		// Pull out the list of teams that participated in these games, less this team
		$recent_opponents = array_flip (array_unique (array_merge (
			Set::extract ("/home_team/.", $recent_games),
			Set::extract ("/away_team/.", $recent_games))));
		unset ($recent_opponents[$teamid]);

		return array_keys ($recent_opponents);
	}

	/**
	 * Compare two games by game date and time
	 */
	function cmpGameDate($a, $b) {
		if ($a['GameSlot']['game_date'] < $b['GameSlot']['game_date']) {
			return -1;
		} else if ($a['GameSlot']['game_date'] > $b['GameSlot']['game_date']) {
			return 1;
		} else if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		} else if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		} else {
			// This should never happen, how can a team have two games on the same day at the same time?
			return 0;
		}
	}
}

?>
