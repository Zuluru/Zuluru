<?php
/**
 * Rule helper for returning how many teams a user is on in the specified leagues.
 */

class RuleLeagueTeamCountComponent extends RuleComponent
{
	function parse($config) {
		$config = trim ($config, '"\'');
		$this->config = array_map ('trim', explode (',', $config));
		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date. Since we're only
	// interested in non-subs, if the user in question is a sub
	// on the current team, we'll just return 0.
	function evaluate($params, $team) {
		$count = 0;
		$positions = Configure::read('playing_roster_positions');

		$position = Set::extract("/Person[id={$params['Person']['id']}]", $team);
		if (!empty($position) && !array_key_exists($position[0]['Person']['TeamsPerson']['position'], $positions)) {
			return 0;
		}

		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['position'], $positions) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				in_array($team['Division']['League']['id'], $this->config))
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
