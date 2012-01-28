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
		'track_attendance' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'attendance_reminder' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
				'message' => 'Please enter a number',
				'last' => true,
			),
			'range' => array(
				'rule' => array('range', -2, 6),
				'message' => 'Attendance reminders can be sent a maximum of five days in advance',
			),
		),
		'attendance_summary' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
				'message' => 'Please enter a number',
				'last' => true,
			),
			'range' => array(
				'rule' => array('range', -2, 6),
				'message' => 'Attendance summaries can be sent a maximum of five days in advance',
			),
		),
		'attendance_notification' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
				'message' => 'Please enter a number',
				'last' => true,
			),
			'range' => array(
				'rule' => array('range', -2, 15),
				'message' => 'Attendance notifications can be sent starting a maximum of 14 days in advance',
			),
		),
	);

	var $belongsTo = array(
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Field' => array(
			'className' => 'Field',
			'foreignKey' => 'home_field',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Region' => array(
			'className' => 'Region',
			'foreignKey' => 'region_preference',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $hasMany = array(
		'Attendance' => array(
			'className' => 'Attendance',
			'foreignKey' => 'team_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
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
		),
		'TeamEvent' => array(
			'className' => 'TeamEvent',
			'foreignKey' => 'team_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
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
		),
		'Franchise' => array(
			'className' => 'Franchise',
			'joinTable' => 'franchises_teams',
			'with' => 'FranchisesTeam',
			'foreignKey' => 'team_id',
			'associationForeignKey' => 'franchise_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => 'Franchise.name',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function beforeValidate() {
		$division_id = $team_id = null;
		if (array_key_exists ('Team', $this->data)) {
			$team = $this->data['Team'];
		} else {
			$team = $this->data;
		}

		if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
			$Team = ClassRegistry::init('Team');
			$division_id = $Team->field ('division_id', array('id' => $team_id));
		}
		if (array_key_exists ('division_id', $team)) {
			$division_id = $team['division_id'];
		}

		$this->validate['name']['unique'] = array(
			'rule' => array('team_unique', $team_id, $division_id),
			'message' => 'There is already a team by that name in this league.',
		);
	}

	function readByPlayerId($id, $open = true) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		if ($open) {
			$conditions = array(
				'Division.schedule_type !=' => 'none',
				'OR' => array(
					'Division.is_open' => true,
					'Division.open > CURDATE()',
				),
			);
		} else {
			$conditions = array(
				'Division.schedule_type !=' => 'none',
			);
		}
		$conditions['TeamsPerson.person_id'] = $id;

		$teams = $this->find('all', array(
				'conditions' => $conditions,
				'contain' => array(
					'Division' => array(
						'League',
						'Day',
					),
				),
				'fields' => array(
					'Team.*',
					'TeamsPerson.person_id', 'TeamsPerson.team_id', 'TeamsPerson.position', 'TeamsPerson.status',
				),
				'joins' => array(
					array(
						'table' => "{$this->tablePrefix}teams_people",
						'alias' => 'TeamsPerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'TeamsPerson.team_id = Team.id',
					),
				),
		));
		usort ($teams, array('League', 'compareLeagueAndDivision'));

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
