<?php
/**
 * Rule helper for returning how many teams a user is on.
 */

class RuleTeamCountComponent extends RuleComponent
{
	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	function evaluate($params) {
		$date = strtotime ($this->config);
		$count = 0;
		$positions = Configure::read('playing_roster_positions');
		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['position'], $positions) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				strtotime ($team['League']['open']) <= $date &&
				$date <= strtotime ($team['League']['close']))
			{
				++ $count;
			}
		}
		return $count;
	}

	function desc() {
		return __('team count', true);
	}
}

?>
