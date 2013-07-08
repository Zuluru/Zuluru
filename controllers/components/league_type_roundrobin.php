<?php

/**
 * Derived class for implementing functionality for round robin.
 */

class LeagueTypeRoundrobinComponent extends LeagueTypeComponent
{
	function compareTeams($a, $b) {
		$ret = $this->compareTeamsTieBreakers($a, $b);
		if ($ret == 0) {
			$ret = parent::compareTeams($a, $b);
		}
		return $ret;
	}

	function schedulingFields($is_admin, $is_coordinator) {
		if ($is_admin || $is_coordinator) {
			return array(
				'current_round' => array(
					'label' => 'Current Round',
					'options' => Configure::read('options.round'),
					'empty' => '---',
					'after' => __('New games will be scheduled in this round by default.', true),
					'required' => true,	// Since this is not in the model validation list, we must force this
				),
			);
		} else {
			return array();
		}
	}

	function schedulingFieldsValidation() {
		return array(
			'current_round' => array(
				'inlist' => array(
					'rule' => array('inconfig', 'options.round'),
					'message' => 'You must select a valid round.',
				),
			),
		);
	}

	function scheduleOptions($num_teams) {
		$types = array(
			'single' => sprintf(__('single blank, unscheduled game (2 teams, one %s)', true), Configure::read('sport.field')),
			'blankset' => "set of blank unscheduled games for all teams in a division ($num_teams teams, " . ($num_teams / 2) . " games, one day)",
			'oneset' => "set of randomly scheduled games for all teams in a division ($num_teams teams, " . ($num_teams / 2) . " games, one day)",
			'fullround' => "full-division round-robin ($num_teams teams, " . (($num_teams - 1) * ($num_teams / 2)) . " games over " .($num_teams - 1) . " weeks)",
			'halfroundstandings' => "half-division round-robin ($num_teams teams, " . ((($num_teams / 2 ) - 1) * ($num_teams / 2)) . " games over " .($num_teams/2 - 1) . " weeks).  2 pools (top, bottom) divided by team standings.",
			'halfroundrating' => "half-division round-robin ($num_teams teams, " . ((($num_teams / 2 ) - 1) * ($num_teams / 2)) . " games over " .($num_teams/2 - 1) . " weeks).  2 pools (top/bottom) divided by rating.",
			'halfroundmix' => "half-division round-robin ($num_teams teams, " . ((($num_teams / 2 ) - 1) * ($num_teams / 2)) . " games over " .($num_teams/2 - 1) . " weeks).  2 even (interleaved) pools divided by team standings.",
		);
		if($num_teams % 4) {
			// Can't do a half-round without an even number of teams in
			// each half.
			unset($types['halfroundstandings']);
			unset($types['halfroundrating']);
			unset($types['halfroundmix']);
		}

		return $types;
	}

	function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return array(1);
			case 'blankset':
			case 'oneset':
				return array($num_teams / 2);
			case 'fullround':
				return array_fill(0, $num_teams - 1, $num_teams / 2);
			case 'halfroundstandings':
			case 'halfroundrating':
			case 'halfroundmix':
				return array_fill(0, ($num_teams / 2) - 1, $num_teams / 2);
		}
	}

	function createSchedule($division_id, $exclude_teams, $data) {
		if (!$this->startSchedule($division_id, $exclude_teams, $data['start_date']))
			return false;

		switch($data['type']) {
			case 'single':
				// Create single game
				$ret = $this->createEmptyGame($data['start_date']);
				break;
			case 'blankset':
				// Create game for all teams in division
				$ret = $this->createEmptySet($data['start_date']);
				break;
			case 'oneset':
				// Create game for all teams in division
				$ret = $this->createScheduledSet($data['start_date']);
				break;
			case 'fullround':
				// Create full roundrobin
				$ret = $this->createFullRoundrobin($data['start_date']);
				break;
			case 'halfroundstandings':
				$ret = $this->createHalfRoundrobin($data['start_date'], 'standings');
				break;
			case 'halfroundrating':
				$ret = $this->createHalfRoundrobin($data['start_date'], 'rating');
				break;
			case 'halfroundmix':
				$ret = $this->createHalfRoundrobin($data['start_date'], 'mix');
				break;
		}

		if (!$ret) {
			return false;
		}
		return $this->finishSchedule($division_id, $data['publish']);
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
		for ($i = 0; $i < $num_games; ++$i) {
			$this->createEmptyGame($date);
		}

		return true;
	}

	/*
	 * Create a scheduled set of games for this division
	 */
	function createScheduledSet($date) {
		$num_teams = count($this->division['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		if ($num_teams % 2) {
			$this->_controller->Session->setFlash(__('Must have even number of teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		// randomize team IDs
		shuffle($this->division['Team']);

		return $this->assignFields($date, $this->division['Team']);
	}

	/*
	 * Create a half round-robin for this division.
	 */
	function createHalfRoundrobin($date, $how_split = 'standings') {
		$num_teams = count($this->division['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		if ($num_teams % 2) {
			$this->_controller->Session->setFlash(__('Must have even number of teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		// Split division teams into two groups
		switch($how_split) {
			case 'rating':
				uasort($this->division['Team'], array($this, 'teams_sort_rating'));
				$top_half = array_slice($this->division['Team'], 0, ($num_teams / 2));
				$bottom_half = array_slice($this->division['Team'], ($num_teams / 2));
				break;

			case 'standings':
				$this->sort($this->division);
				$top_half = array_slice($this->division['Team'], 0, ($num_teams / 2));
				$bottom_half = array_slice($this->division['Team'], ($num_teams / 2));
				break;

			// Sort by standings, then do a "snake" to split into two groups
			// $i will be 1,2,...,n, so $i%4 will be 1,2,3,0,...
			case 'mix':
				$this->sort($this->division);
				$top_half = $bottom_half = array();
				$i = 0;
				foreach ($this->division['Team'] as $team) {
					if (++$i % 4 < 2) {
						$top_half[] = $team;
					} else {
						$bottom_half[] = $team;
					}
				}
				break;
		}

		// Schedule both halves.
		// TODO: We should create the games for each half and combine them before allocating fields,
		// to be better at accomodating home field and regional requests. Otherwise, a team in the
		// "bottom half" might have their best option already allocated to a game in the "top half".
		// Low priority, as it's only an issue when scheduling half round robins when there are
		// home field or regional preferences.
		return ($this->createFullRoundrobin($date, $top_half, 2) &&
				$this->createFullRoundrobin($date, $bottom_half));
	}

	/*
	 * Create a full round-robin for this division.
	 */
	function createFullRoundrobin($date, $teams = null, $repeats = 1) {
		if (is_null($teams)) {
			$teams = $this->division['Team'];
		}

		$num_teams = count($teams);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		if ($num_teams % 2) {
			$this->_controller->Session->setFlash(__('Must have even number of teams', true), 'default', array('class' => 'warning'));
			return false;
		}

		// For n-1 iterations, generate games by pairing up teams
		$iterations_remaining = $num_teams - 1;

		// and so we need n-1 days worth of gameslots
		$day_count = $this->countAvailableGameslotDays($date, $num_teams / 2 * $repeats);

		if ($day_count < $iterations_remaining) {
			$this->_controller->Session->setFlash(sprintf (__('Need %s weeks of gameslots, yet only %s are available. Add more gameslots.', true), $iterations_remaining, $day_count), 'default', array('class' => 'warning'));
			return false;
		}

		while ($iterations_remaining--) {
			// Round-robin algorithm for n teams:
			// a. pair each team k up with its (n - k - 1) partner in the
			// list.  assignFields() takes the array pairwise, so we do
			// it like this.
			$set_teams = array();
			for($k = 0; $k < ($num_teams / 2); $k++) {
				$set_teams[] = $teams[$k];
				$set_teams[] = $teams[($num_teams - $k - 1)];
			}

			// b. schedule them
			if (!$this->assignFields($date, $set_teams, $num_teams / 2 * ($repeats - 1))) {
				$this->_controller->Session->setFlash(sprintf (__('Had to stop with %s sets left to schedule: could not assign %s', true), $iterations_remaining, Configure::read('sport.fields')), 'default', array('class' => 'error'));
				return false;
			}

			// c. keep k=0 element in place, move k=1 element to end, and move
			// k=2 through n elements left one position.
			$teams = $this->rotateAllExceptFirst($teams);

			// Now, move the date forward to next available game date
			$date = $this->nextGameslotDay($date, $num_teams / 2 * ($repeats - 1));
		}

		return true;
	}

	/**
	 * Given an array, keep the first element in place, but rotate the
	 * remaining elements by one.
	 */
	function rotateAllExceptFirst ($ary) {
		$new_first = array_shift($ary);
		$new_last = array_shift($ary);
		array_push ($ary, $new_last);
		array_unshift ($ary, $new_first);
		return $ary;
	}

	function teams_sort_rating ($a, $b) {
		if ($a['rating'] < $b['rating'])
			return 1;
		if ($a['rating'] > $b['rating'])
			return -1;

		// TODO: Leaguerunner version of this uses average player skill level as tie-breaker
		return (mt_rand (0, 1) == 0 ? -1 : 1);
	}
}

?>