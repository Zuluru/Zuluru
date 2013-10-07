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
