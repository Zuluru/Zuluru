<?php
/**
 * Rule helper for returning how many teams a user is on.
 */

class RuleTeamCountComponent extends RuleComponent
{
	var $query_having = 'Person.id HAVING team_count';

	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	function evaluate($affiliate, $params) {
		$date = strtotime ($this->config);
		$count = 0;
		$roles = Configure::read('playing_roster_roles');
		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['role'], $roles) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				$team['Division']['League']['affiliate_id'] == $affiliate &&
				strtotime ($team['Division']['open']) <= $date &&
				$date <= strtotime ($team['Division']['close']))
			{
				++ $count;
			}
		}
		return $count;
	}

	function build_query($affiliate, &$joins, &$fields) {
		$date = date('Y-m-d', strtotime ($this->config));
		$fields['team_count'] = 'COUNT(Team.id) as team_count';
		$joins['TeamsPerson'] = array(
			'table' => 'teams_people',
			'alias' => 'TeamsPerson',
			'type' => 'INNER',
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
		$query = array(
			'Division.open <=' => $date,
			'Division.close >=' => $date,
			'TeamsPerson.role' => Configure::read('playing_roster_roles'),
			'TeamsPerson.status' => ROSTER_APPROVED,
		);

		if (Configure::read('feature.affiliate')) {
			$joins['League'] = array(
				'table' => 'leagues',
				'alias' => 'League',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'League.id = Division.league_id',
			);
			$query['League.affiliate_id'] = $affiliate;
		}

		return $query;
	}

	function desc() {
		return __('have a team count', true);
	}
}

?>
