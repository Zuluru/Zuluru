<?php
class Team extends AppModel {
	var $name = 'Team';
	var $displayField = 'name';
	var $order = 'Team.name';

	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Team name must not be blank.',
			),
		),
		'website' => array(
			'url' => array(
				'rule' => array('url'),
				'allowEmpty' => true,
				'message' => 'Enter a valid URL, or leave blank.',
			),
		),
		'shirt_colour' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Shirt colour must not be blank.',
			),
		),
		'open_roster' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
	);

	var $belongsTo = array(
		'League' => array(
			'className' => 'League',
			'foreignKey' => 'league_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Incident' => array(
			'className' => 'Incident',
			'foreignKey' => 'team_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'teams_people',
			'with' => 'TeamsPerson',
			'foreignKey' => 'team_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => 'Person.gender DESC, Person.last_name, Person.first_name',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function beforeValidate() {
		$league_id = $team_id = null;
		if (array_key_exists ('Team', $this->data)) {
			$team = $this->data['Team'];
		} else {
			$team = $this->data;
		}

		if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
			$Team = ClassRegistry::init('Team');
			$league_id = $Team->field ('league_id', array('id' => $team_id));
		}
		if (array_key_exists ('league_id', $team)) {
			$league_id = $team['league_id'];
		}

		$this->validate['name']['unique'] = array(
			'rule' => array('notinquery', 'Team', 'name', array(
				'league_id' => $league_id,
				'id !=' => $team_id,
			)),
			'message' => 'There is already a team by that name in this league.',
		);
	}

	function readByPlayerId($id, $roster_limits = false, $open = true, $order = null) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$conditions = array(
			'TeamsPerson.person_id' => $id,
		);
		if ($open) {
			$conditions['OR'] = array(
				'League.is_open' => true,
				'League.open > CURDATE()',
			);
		} else {
			$conditions['League.schedule_type !='] = 'none';
		}
		if ($roster_limits) {
			$conditions['TeamsPerson.position'] = $roster_limits;
		}
		if ($order === null) {
			$order = array('LeaguesDay.day_id', 'League.open');
		}

		$this->recursive = -1;
		$teams = $this->find('all', array(
				'conditions' => $conditions,
				// By grouping, we get only one record per team, regardless
				// of how many days the league may operate on. Without this,
				// a league that runs on two nights would generate two records
				// here. Nothing that uses this function needs the full list
				// of nights, so it's okay.
				'group' => 'Team.id',
				'order' => $order,
				'fields' => array(
					'Team.*',
					'TeamsPerson.person_id', 'TeamsPerson.team_id', 'TeamsPerson.position', 'TeamsPerson.status',
					'League.id', 'League.name', 'League.roster_deadline', 'League.open', 'League.close',
				),
				'joins' => array(
					array(
						'table' => "{$this->tablePrefix}teams_people",
						'alias' => 'TeamsPerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'TeamsPerson.team_id = Team.id',
					),
					array(
						'table' => "{$this->tablePrefix}leagues",
						'alias' => 'League',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'League.id = Team.league_id',
					),
						array(
							'table' => "{$this->tablePrefix}leagues_days",
							'alias' => 'LeaguesDay',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'LeaguesDay.league_id = League.id',
						),
				),
		));

		return $teams;
	}

	static function consolidateRoster(&$team) {
		if (array_key_exists ('Person', $team)) {
			$roster_count = $skill_total = 0;
			foreach ($team['Person'] as $person) {
				if (in_array ($person['TeamsPerson']['position'], Configure::read('playing_roster_positions')) &&
					$person['TeamsPerson']['status'] == ROSTER_APPROVED)
				{
					++$roster_count;
					$skill_total += $person['skill_level'];
				}
			}
			if ($roster_count) {
				$average_skill = sprintf ('%.2f', round ($skill_total / $roster_count, 2));
			} else {
				$average_skill = 'N/A';
			}
			$team = array_merge ($team, compact(array('roster_count', 'roster_invited', 'skill_total', 'skill_invited', 'average_skill')));
		}
	}

	static function compareRoster($a, $b) {
		static $rosterMap = null;
		if ($rosterMap == null) {
			$rosterMap = array_flip(array_keys(Configure::read('options.roster_position')));
		}

		// Sort eligible from non-eligible
		if (array_key_exists('can_add', $a) && array_key_exists('can_add', $b)) {
			if ($a['can_add'] === true && $b['can_add'] !== true) {
				return -1;
			} else if ($a['can_add'] !== true && $b['can_add'] === true) {
				return 1;
			}
		}

		if ($a['TeamsPerson']['status'] == ROSTER_APPROVED && $b['TeamsPerson']['status'] != ROSTER_APPROVED) {
			return -1;
		} else if ($a['TeamsPerson']['status'] != ROSTER_APPROVED && $b['TeamsPerson']['status'] == ROSTER_APPROVED) {
			return 1;
		} else if ($rosterMap[$a['TeamsPerson']['position']] > $rosterMap[$b['TeamsPerson']['position']]) {
			return 1;
		} else if ($rosterMap[$a['TeamsPerson']['position']] < $rosterMap[$b['TeamsPerson']['position']]) {
			return -1;
		} else if ($a['gender'] < $b['gender']) {
			return 1;
		} else if ($a['gender'] > $b['gender']) {
			return -1;
		} else if ($a['last_name'] > $b['last_name']) {
			return 1;
		} else if ($a['last_name'] < $b['last_name']) {
			return -1;
		} else if ($a['first_name'] > $b['first_name']) {
			return 1;
		} else {
			return -1;
		}
	}
}
?>
