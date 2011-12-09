<?php
/**
 * Rule helper for returning how many teams a user is on in the specified leagues.
 */

class RuleTeamCountComponent extends RuleComponent
{
	function parse($config) {
		$config = trim ($config, '"\'');
		$this->config = array_map ('trim', explode (',', $config));
		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	function evaluate($params) {
		$count = 0;
		$positions = Configure::read('playing_roster_positions');
		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['position'], $positions) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				in_array($team['League']['id'], $this->config))
			{
				++ $count;
			}
		}
		return $count;
	}

	function desc() {
		return __('have a team count', true);
	}
}

?>
