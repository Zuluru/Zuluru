<?php
/**
 * Rule helper for returning how many teams a user is on in the specified leagues.
 */

class RuleLeagueTeamCountComponent extends RuleComponent
{
	var $query_having = 'Person.id HAVING team_count';

	function parse($config) {
		$config = trim ($config, '"\'');
		$this->config = array_map ('trim', explode (',', $config));
		return true;
	}

	// Count how many teams the user was on in the given leagues.
	// Since we're only interested in non-subs, if the user in
	// question is a sub on the current team, we'll just return 0.
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

	function build_query(&$joins, &$fields) {
		$fields['team_count'] = 'COUNT(Team.id) as team_count';
		$joins['TeamsPerson'] = array(
			'table' => 'teams_people',
			'alias' => 'TeamsPerson',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'TeamsPerson.person_id = Person.id',
		);
		$joins['Team'] = array(
			'table' => 'teams',
			'alias' => 'Team',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Team.id = TeamsPerson.team_id',
		);
		$joins['Division'] = array(
			'table' => 'divisions',
			'alias' => 'Division',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Division.id = Team.division_id',
		);
		return array(
			'Division.league_id' => $this->config,
			'TeamsPerson.position' => Configure::read('playing_roster_positions'),
			'TeamsPerson.status' => ROSTER_APPROVED,
		);
	}

	function desc() {
		return __('have a team count', true);
	}
}

?>
