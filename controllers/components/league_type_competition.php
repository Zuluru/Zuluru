<?php

/**
 * Derived class for implementing functionality for leagues based on group competitions rather than head-to-head games.
 */

class LeagueTypeCompetitionComponent extends LeagueTypeComponent
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'competition';

	function newTeam() {
		return array(
			'initial_rating' => 0,
			'rating' => 0,
		);
	}

	/**
	 * Sort a competition division by ratings (lower total is better), then base stuff.
	 * We don't use compareTeamsTieBreakers here, because it looks at things like wins,
	 * which are meaningless here.
	 */
	function compareTeams($a, $b) {
		if ($a['rating'] > $b['rating'])
			return 1;
		if ($a['rating'] < $b['rating'])
			return -1;

		return parent::compareTeams($a, $b);
	}

	function scheduleOptions($num_teams) {
		$types = array(
			'single' => 'single blank, unscheduled game',
			'blankset' => "set of blank unscheduled games for all teams in a division ($num_teams teams, " . $num_teams . " games, one day)",
			'oneset' => "set of randomly scheduled games for all teams in a division ($num_teams teams, " . $num_teams . " games, one day)",
		);

		return $types;
	}

	function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return array(1);
			case 'blankset':
			case 'oneset':
				return array($num_teams);
		}
	}

	function createSchedule($division_id, $exclude_teams, $data) {
		if (!$this->startSchedule($division_id, $exclude_teams, $data['start_date'], $data['double_booking']))
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
		}

		if (!$ret) {
			return false;
		}
		return $this->finishSchedule($division_id, $data['publish'], $data['double_booking']);
	}

	/*
	 * Create an empty set of games for this division
	 */
	function createEmptySet($date) {
		$num_teams = count($this->division['Team']);

		// Now, create our games.  Don't add any teams, or set a round,
		// or anything, just randomly allocate a gameslot.
		for ($i = 0; $i < $num_teams; ++$i) {
			$this->createEmptyGame($date);
		}

		return true;
	}

	/*
	 * Create a scheduled set of games for this division
	 */
	function createScheduledSet($date) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = array();

		// Create a game for each team
		foreach ($this->division['Team'] as $team) {
			$games[] = array('home_team' => $team['id']);
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		if (!$this->assignFieldsByPreferences($date, $games)) {
			return false;
		}

		return true;
	}
}

?>