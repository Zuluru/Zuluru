<?php
/**
 * Rule helper for returning how many teams a user is on in the specified leagues.
 */

class RuleLeagueTeamCountComponent extends RuleComponent
{
	var $query_having = 'Person.id HAVING team_count';

	function parse($config) {
		$config = str_replace(array('"', "'"), '', low($config));
		$this->config = array(
			'leagues' => array_map ('trim', explode (',', $config)),
		);
		$sub_key = array_search('include_subs', $this->config['leagues']);
		if ($sub_key !== false) {
			$this->config['roles'] = Configure::read('extended_playing_roster_roles');
			unset($this->config['leagues'][$sub_key]);
		} else {
			$this->config['roles'] = Configure::read('playing_roster_roles');
		}
		return true;
	}

	// Count how many teams the user was on in the given leagues.
	// If we're only interested in non-subs, if the user in
	// question is a sub on the current team, we'll just return 0.
	function evaluate($affiliate, $params, $team) {
		$count = 0;

		$role = Set::extract("/Person[id={$params['Person']['id']}]", $team);
		if (!empty($role) && !array_key_exists($role[0]['Person']['TeamsPerson']['role'], $this->config['roles'])) {
			return 0;
		}

		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['role'], $this->config['roles']) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				in_array($team['Division']['League']['id'], $this->config['leagues']))
			{
				++ $count;
			}
		}
		return $count;
	}

	function build_query($affiliate, &$joins, &$fields) {
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
			'Division.league_id' => $this->config['leagues'],
			'TeamsPerson.role' => $this->config['roles'],
			'TeamsPerson.status' => ROSTER_APPROVED,
		);
	}

	function desc() {
		return __('have a team count', true);
	}
}

?>
