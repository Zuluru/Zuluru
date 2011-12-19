<?php
App::Import ('model', 'User');

class Person extends User {
	var $name = 'Person';

	var $belongsTo = array(
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Allstar' => array(
			'className' => 'Allstar',
			'foreignKey' => 'person_id',
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
		'Attendance' => array(
			'className' => 'Attendance',
			'foreignKey' => 'person_id',
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
		'Preregistration' => array(
			'className' => 'Preregistration',
			'foreignKey' => 'person_id',
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
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'person_id',
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
		'Setting' => array(
			'className' => 'Setting',
			'foreignKey' => 'person_id',
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
		'Upload' => array(
			'className' => 'Upload',
			'foreignKey' => 'other_id',
			'dependent' => false,
			'conditions' => array('Upload.type' => 'person'),
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Waiver' => array(
			'className' => 'Waiver',
			'foreignKey' => 'person_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => 'Waiver.expires',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);

	var $hasAndBelongsToMany = array(
		'League' => array(
			'className' => 'League',
			'joinTable' => 'leagues_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'league_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Team' => array(
			'className' => 'Team',
			'joinTable' => 'teams_people',
			'with' => 'TeamsPerson',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'team_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	// Return a person record including all details related to current leagues.
	function readCurrent($id) {
		// Check for invalid users (not logged in, for example)
		if ($id === null) {
			return array();
		}

		// Get the main record.  We don't contain Allstar, Team and League
		// records, because Person hasMany of them and by default this causes
		// many queries to the database.
		$this->recursive = -1;
		$contain = array(
			'Group',
			'Setting',
			'Upload',
		);
		if (Configure::read('feature.registration')) {
			$contain = array_merge ($contain, array(
				'Registration' => array(
					'fields' => array('id', 'created', 'payment'),
					'limit' => 4,
					'order' => 'created DESC',
					'Event' => array(
						'fields' => array('id', 'name'),
					),
				),
				'Preregistration',
			));
		}
		$this->contain($contain);
		$person = $this->read(null, $id);

		// Unfortunate that we have to manually specify the joins, but it seems
		// that it's (currently) the only way to fetch all this data in a
		// single query.
		if (Configure::read('scoring.allstars')) {
			$this->Allstar->recursive = -1;
			$person['Allstar'] = $this->Allstar->find('all', array(
					'conditions' => array(
						'Allstar.person_id' => $id,
						'League.is_open' => true,
					),
					'order' => 'GameSlot.game_date, GameSlot.game_start',
					'fields' => array(
						'Allstar.id',
						'Game.id',
						'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
						'Field.id', 'Field.name', 'Field.num', 'Field.code',
						'HomeTeam.id', 'HomeTeam.name',
						'AwayTeam.id', 'AwayTeam.name',
						'League.id', 'League.name',
					),
					'joins' => array(
						array(
							'table' => "{$this->Allstar->tablePrefix}games",
							'alias' => 'Game',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'Game.id = Allstar.game_id',
						),
							array(
								'table' => "{$this->Allstar->tablePrefix}game_slots",
								'alias' => 'GameSlot',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'GameSlot.game_id = Game.id',
							),
								array(
									// TODO: something more generic than explicitly escaping the table name
									'table' => "`{$this->Allstar->tablePrefix}fields`",
									'alias' => 'Field',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'Field.id = GameSlot.field_id',
								),
							array(
								'table' => "{$this->Allstar->tablePrefix}teams",
								'alias' => 'HomeTeam',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'HomeTeam.id = Game.home_team',
							),
							array(
								'table' => "{$this->Allstar->tablePrefix}teams",
								'alias' => 'AwayTeam',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'AwayTeam.id = Game.away_team',
								),
							array(
								'table' => "{$this->Allstar->tablePrefix}leagues",
								'alias' => 'League',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'League.id = Game.league_id',
							),
					),
			));
		}

		$person['Team'] = $this->Team->readByPlayerId($id);
		$person['League'] = $this->League->readByPlayerId($id);

		return $person;
	}

	function findDuplicates($person) {
		$this->recursive = -1;
		return $this->find('all', array(
				'conditions' => array(
					'id !=' => $person['Person']['id'],
					'OR' => array(
						'email' => $person['Person']['email'],
						array(
							'home_phone' => $person['Person']['home_phone'],
							'home_phone !=' => '',
							array('home_phone !=' => null),
						),
						array(
							'work_phone' => $person['Person']['work_phone'],
							'work_phone !=' => '',
							array('work_phone !=' => null),
						),
						array(
							'mobile_phone' => $person['Person']['mobile_phone'],
							'mobile_phone !=' => '',
							array('mobile_phone !=' => null),
						),
						'addr_street' => $person['Person']['addr_street'],
						array(
							'first_name' => $person['Person']['first_name'],
							'last_name' => $person['Person']['last_name'],
						),
					),
				),
		));
	}

	static function comparePerson($a, $b) {
		if (array_key_exists('Person', $a)) {
			$a = $a['Person'];
			$b = $b['Person'];
		}

		if ($a['gender'] < $b['gender']) {
			return 1;
		} else if ($a['gender'] > $b['gender']) {
			return -1;
		} else if (low($a['last_name']) > low($b['last_name'])) {
			return 1;
		} else if (low($a['last_name']) < low($b['last_name'])) {
			return -1;
		} else if (low($a['first_name']) > low($b['first_name'])) {
			return 1;
		} else {
			return -1;
		}
	}
}
?>
