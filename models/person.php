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
		),
		'Attendance' => array(
			'className' => 'Attendance',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
		'Preregistration' => array(
			'className' => 'Preregistration',
			'foreignKey' => 'person_id',
			'dependent' => false,
		),
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'person_id',
			'dependent' => false,
		),
		'Setting' => array(
			'className' => 'Setting',
			'foreignKey' => 'person_id',
			'dependent' => false,
		),
		'Upload' => array(
			'className' => 'Upload',
			'foreignKey' => 'person_id',
			'dependent' => false,
		),
		'Note' => array(
			'className' => 'Note',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
		'Subscription' => array(
			'className' => 'Subscription',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
		'Stat' => array(
			'className' => 'Stat',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
		'TaskSlot' => array(
			'className' => 'TaskSlot',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
	);

	var $hasAndBelongsToMany = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'joinTable' => 'affiliates_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'affiliate_id',
			'unique' => true,
		),
		'Badge' => array(
			'className' => 'Badge',
			'joinTable' => 'badges_people',
			'with' => 'BadgesPerson',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'badge_id',
			'unique' => false,
			'order' => 'Badge.id',
		),
		'Division' => array(
			'className' => 'Division',
			'joinTable' => 'divisions_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'division_id',
			'unique' => true,
		),
		'Franchise' => array(
			'className' => 'Franchise',
			'joinTable' => 'franchises_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'franchise_id',
			'unique' => true,
		),
		'Team' => array(
			'className' => 'Team',
			'joinTable' => 'teams_people',
			'with' => 'TeamsPerson',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'team_id',
			'unique' => true,
		),
		'Waiver' => array(
			'className' => 'Waiver',
			'joinTable' => 'waivers_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'waiver_id',
			'unique' => true,
		),
	);

	// Return a person record including all details related to current divisions.
	function readCurrent($id, $my_id = null, $badge_obj = null) {
		// Check for invalid users (not logged in, for example)
		if ($id === null) {
			return array();
		}

		// Get the main record.  We don't contain Allstar, Team and Division
		// records, because Person hasMany of them and by default this causes
		// many queries to the database.
		$contain = array(
			'Group',
			'Setting',
			'Waiver' => array(
					'conditions' => array('WaiversPerson.valid_from <= CURDATE()', 'WaiversPerson.valid_until >= CURDATE()'),
			),
		);

		// May need to include various types of uploads
		if (Configure::read('feature.photos') && !Configure::read('feature.documents')) {
			$contain['Upload'] = array('conditions' => array('type_id' => null));
		} else if (!Configure::read('feature.photos') && Configure::read('feature.documents')) {
			$contain['Upload'] = array('UploadType', 'conditions' => array('type_id !=' => null));
		} else if (Configure::read('feature.photos') && Configure::read('feature.documents')) {
			$contain['Upload'] = array('UploadType');
		}

		if (Configure::read('feature.annotations') && $my_id !== null) {
			$contain['Note'] = array('conditions' => array('created_person_id' => $my_id));
		}

		if (isset($badge_obj)) {
			$contain['Badge'] = array('conditions' => array(
				'BadgesPerson.approved' => true,
				'Badge.visibility' => $badge_obj->getVisibility(),
			));
		}

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
				'Preregistration' => 'Event',
			));
		}

		if (Configure::read('feature.tasks')) {
			$contain['TaskSlot'] = array(
				'Task' => array('Category', 'Person'),
				'order' => array('TaskSlot.task_date', 'TaskSlot.task_start'),
			);
		}

		$this->contain($contain);
		$person = $this->read(null, $id);
		if (!$person) {
			return array();
		}

		if (isset($badge_obj)) {
			$badge_obj->prepForDisplay($person);
		}

		// Unfortunate that we have to manually specify the joins, but it seems
		// that it's (currently) the only way to fetch all this data in a
		// single query.
		if (Configure::read('scoring.allstars')) {
			$this->Allstar->contain();
			$person['Allstar'] = $this->Allstar->find('all', array(
					'conditions' => array(
						'Allstar.person_id' => $id,
						'Division.is_open' => true,
					),
					'order' => 'GameSlot.game_date, GameSlot.game_start',
					'fields' => array(
						'Allstar.id',
						'Game.id',
						'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
						'Facility.id', 'Facility.name', 'Facility.code', 'Field.num',
						'HomeTeam.id', 'HomeTeam.name',
						'AwayTeam.id', 'AwayTeam.name',
						'League.id', 'League.name',
						'Division.id', 'Division.name',
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
										'table' => "`{$this->Allstar->tablePrefix}facilities`",
										'alias' => 'Facility',
										'type' => 'LEFT',
										'foreignKey' => false,
										'conditions' => 'Facility.id = Field.facility_id',
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
								'table' => "{$this->Allstar->tablePrefix}divisions",
								'alias' => 'Division',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'Division.id = Game.division_id',
							),
							array(
								'table' => "{$this->Allstar->tablePrefix}leagues",
								'alias' => 'League',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'League.id = Division.league_id',
							),
					),
			));
		}

		$person['Team'] = $this->Team->readByPlayerId($id);
		$person['Division'] = $this->Division->readByPlayerId($id);
		$person['Affiliate'] = $this->Affiliate->readByPlayerId($id);

		return $person;
	}

	function findDuplicates($person) {
		if (array_key_exists('AffiliatePerson', $person)) {
			$affiliate = $person['AffiliatePerson']['affiliate_id'];
		} else {
			$affiliate = Set::extract('/Affiliate/id', $person);
		}

		$conditions = array(
			'Person.id !=' => $person['Person']['id'],
			'AffiliatePerson.affiliate_id' => $affiliate,
			'OR' => array(
				'Person.email' => $person['Person']['email'],
				array(
					'Person.first_name' => $person['Person']['first_name'],
					'Person.last_name' => $person['Person']['last_name'],
				),
			),
		);

		if (Configure::read('profile.home_phone')) {
			$conditions['OR'][] = array(
				'Person.home_phone' => $person['Person']['home_phone'],
				'Person.home_phone !=' => '',
				array('Person.home_phone !=' => null),
			);
		}
		if (Configure::read('profile.work_phone')) {
			$conditions['OR'][] = array(
				'Person.work_phone' => $person['Person']['work_phone'],
				'Person.work_phone !=' => '',
				array('Person.work_phone !=' => null),
			);
		}
		if (Configure::read('profile.mobile_phone')) {
			$conditions['OR'][] = array(
				'Person.mobile_phone' => $person['Person']['mobile_phone'],
				'Person.mobile_phone !=' => '',
				array('Person.mobile_phone !=' => null),
			);
		}
		if (Configure::read('profile.addr_street')) {
			$conditions['OR']['Person.addr_street'] = $person['Person']['addr_street'];
		}

		return $this->find('all', array(
				'joins' => array(
					array(
						'table' => "{$this->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
				),
				'conditions' => $conditions,
				'contain' => array(),
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
