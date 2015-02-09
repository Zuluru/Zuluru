<?php
/**
 * Rule helper for returning how many teams a user is on.
 */

class RuleTeamCountComponent extends RuleComponent
{
	var $query_having = 'Person.id HAVING team_count';

	function parse($config) {
		if (strpos($config, ',') !== false) {
			$this->config = array('params' => array_map('trim', explode(',', $config)));
		} else {
			$this->config = array('params' => array(trim($config)));
		}

		$sub_key = array_search('include_subs', $this->config['params']);
		if ($sub_key !== false) {
			$this->config['roles'] = Configure::read('extended_playing_roster_roles');
			unset($this->config['params'][$sub_key]);
		} else {
			$this->config['roles'] = Configure::read('playing_roster_roles');
		}
		$this->config['params'] = trim (implode(',', $this->config['params']), '"\'');

		if ($this->config['params'][0] == '<') {
			$to = substr($this->config['params'], 1);
			$to_time = strtotime($to);
			if ($to_time === false) {
				$this->parse_error = sprintf(__('Invalid date: %s', true), $to);
				return false;
			}
			$to = date('Y-m-d', $from_time - DAY);
			$this->config['params'] = array('0000-00-00', $to);
		} else if ($this->config['params'][0] == '>') {
			$from = substr($this->config['params'], 1);
			$from_time = strtotime($from);
			if ($from_time === false) {
				$this->parse_error = sprintf(__('Invalid date: %s', true), $from);
				return false;
			}
			$from = date('Y-m-d', $from_time + DAY);
			$this->config['params'] = array($from, '9999-12-31');
		} else if (strpos($this->config['params'], ',') !== false) {
			$this->config['params'] = explode(',', $this->config['params']);
			if (strtotime($this->config['params'][0]) === false) {
				$this->parse_error = sprintf(__('Invalid date: %s', true), $this->config['params'][0]);
				return false;
			}
			if (strtotime($this->config['params'][1]) === false) {
				$this->parse_error = sprintf(__('Invalid date: %s', true), $this->config['params'][1]);
				return false;
			}
		} else {
			if (strtotime($this->config['params']) === false) {
				$this->parse_error = sprintf(__('Invalid date: %s', true), $this->config['params']);
				return false;
			}
		}

		return true;
	}

	// Count how many teams the user was on that played in leagues
	// that were open on the configured date
	function evaluate($affiliate, $params) {
		if (empty($params['Team'])) {
			return 0;
		}
		if (is_array($this->config['params'])) {
			$date_from = strtotime ($this->config['params'][0]);
			$date_to = strtotime ($this->config['params'][1]);
		} else {
			$date_from = $date_to = strtotime ($this->config['params']);
		}
		$count = 0;
		foreach ($params['Team'] as $team) {
			if (in_array($team['TeamsPerson']['role'], $this->config['roles']) &&
				$team['TeamsPerson']['status'] == ROSTER_APPROVED &&
				$team['Division']['League']['affiliate_id'] == $affiliate &&
				strtotime ($team['Division']['open']) <= $date_to &&
				$date_from <= strtotime ($team['Division']['close']))
			{
				++ $count;
			}
		}
		return $count;
	}

	function build_query($affiliate, &$joins, &$fields) {
		if (is_array($this->config['params'])) {
			$date_from = date('Y-m-d', strtotime ($this->config['params'][0]));
			$date_to = date('Y-m-d', strtotime ($this->config['params'][1]));
		} else {
			$date_from = $date_to = date('Y-m-d', strtotime ($this->config['params']));
		}
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
			'Division.open <=' => $date_to,
			'Division.close >=' => $date_from,
			'TeamsPerson.role' => $this->config['roles'],
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
