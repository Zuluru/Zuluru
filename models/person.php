<?php
class Person extends AppModel {
	var $name = 'Person';
	var $useTable = 'people';
	var $displayField = 'full_name';
	var $actsAs=array(
		'Trim',
		'Formatter' => array(
			'fields' => array(
				'first_name' => 'proper_case_format',
				'last_name' => 'proper_case_format',
				'addr_street' => 'proper_case_format',
				'addr_city' => 'proper_case_format',
				'addr_postalcode' => 'postal_format',
				'home_phone' => 'phone_format',
				'work_phone' => 'phone_format',
				'mobile_phone' => 'phone_format',
			),
		),
	);

	var $validate = array(
		'first_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'First name must not be blank.',
			),
			'valid' => array(
				'rule' => array('custom', self::NAME_REGEX),
				'message' => 'Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.',
			),
		),
		'last_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Last name must not be blank.',
			),
			'valid' => array(
				'rule' => array('custom', self::NAME_REGEX),
				'message' => 'Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.',
			),
		),
		'publish_email' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'home_phone' => array(
			'phone' => array(
				'rule' => array('phone'),
				'message' => 'Please supply area code and number.',
			),
		),
		'publish_home_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'work_phone' => array(
			'phone' => array(
				'rule' => array('phone'),
				'allowEmpty' => true,
				'message' => 'Please supply area code and number.',
			),
		),
		'work_ext' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
				'message' => 'Please supply extension, if any.',
			),
		),
		'publish_work_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'mobile_phone' => array(
			'phone' => array(
				'rule' => array('phone'),
				'allowEmpty' => true,
				'message' => 'Please supply area code and number.',
			),
		),
		'publish_mobile_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'addr_street' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'You must supply a valid street address.',
			),
		),
		'addr_city' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'You must supply a city.',
			),
		),
		'addr_prov' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Province', 'name'),
				'message' => 'You must select a province or state.',
			),
		),
		'addr_country' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Country', 'name'),
				'message' => 'You must select a country.',
			),
		),
		'addr_postalcode' => array(
			'postal' => array(
				'rule' => array('postal', null, 'ca'),
				// TODO: validate this by province
				'message' => 'You must enter a valid Canadian postal code',
			),
		),
		'gender' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.gender'),
				'message' => 'You must select a gender.',
			),
		),
		'birthdate' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid birthdate.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'born'),
				'message' => 'You must provide a valid birthdate.',
			),
		),
		'height' => array(
			'range' => array(
				'rule' => array('range', 48, 84),
				'message' => 'You must enter a valid height.',
			),
		),
		'skill_level' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.skill'),
				'message' => 'You must select a skill level between 1 and 10.',
			),
		),
		'year_started' => array(
			'range' => array(
				'rule' => array('indateconfig', 'started'),
				'message' => 'Year started must be after 1986. For the number of people who started playing before then, I don\'t think it matters if you\'re listed as having played 17 years or 20, you\'re still old.',
			),
// TODO You can't have started playing when you were 0 years old! Please correct your birthdate, or your starting year
		),
		'shirt_size' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.shirt_size'),
				'message' => 'You must select a valid shirt size.',
			),
		),
		'group_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Group', 'id'),
				'message' => 'You must select a valid account type.',
			),
		),
		'status' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.record_status'),
				'allowEmpty' => true,
				'message' => 'You must select a valid status.',
			),
		),
		'has_dog' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'willing_to_volunteer' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
		'contact_for_feedback' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
	);

	var $belongsTo = array(
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'group_id',
		),
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
		'Credit' => array(
			'className' => 'Credit',
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
		'Relative' => array(
			'className' => 'Person',
			'joinTable' => 'people_people',
			'with' => 'PeoplePerson',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'relative_id',
			'unique' => false,
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

	function __construct($id = false, $table = null, $ds = null) {
		// Which user model to use depends on system configuration
		$user_model = Configure::read('security.auth_model');
		$this->belongsTo[$user_model] = array(
			'className' => $user_model,
			'foreignKey' => 'user_id',
		);
		$this->$user_model = ClassRegistry::init($user_model);

		// Parent constructor comes last, as it adds missing fields to the belongsTo array
		parent::__construct($id, $table, $ds);
	}

	function beforeValidate() {
		if (array_key_exists('addr_country', $this->data[$this->alias]) && $this->data[$this->alias]['addr_country'] == 'United States') {
			$this->validate['addr_postalcode']['postal'] = array(
				'rule' => array('postal', null, 'us'),
				'message' => 'You must enter a valid US zip code',
			);
		}

		if (Configure::read('feature.birth_year_only')) {
			$this->validate['birthdate']['date'] = array(
				'rule' => array('date', 'y', '/^(\\d{4})/'),
				'message' => 'You must provide a valid birthdate.',
			);
		}

		foreach (array_keys($this->validate) as $field) {
			if (!Configure::read("profile.$field")) {
				unset($this->validate[$field]);
			}
		}
	}

	function _afterFind ($record) {
		$user_model = Configure::read('security.auth_model');

		if (!empty($record[$this->alias][$user_model])) {
			$user = $record[$this->alias][$user_model];
		} else if (!empty($record[$user_model])) {
			$user = $record[$user_model];
		} else {
			$user = array();
		}

		if (array_key_exists('email', $user)) {
			// We want the email column copied if it exists, even if it's blank
			$record[$this->alias]['email'] = $user['email'];
		}
		if (!empty($user['user_name'])) {
			$record[$this->alias]['user_name'] = $user['user_name'];
		}
		if (!empty($user['password'])) {
			$record[$this->alias]['password'] = $user['password'];
		}

		if (array_key_exists ('first_name', $record[$this->alias]) && array_key_exists ('last_name', $record[$this->alias])) {
			$record[$this->alias]['full_name'] = trim ("{$record[$this->alias]['first_name']} {$record[$this->alias]['last_name']}");
			if (!empty($record[$this->alias]['email'])) {
				if (!empty ($record[$this->alias]['full_name'])) {
					$record[$this->alias]['email_formatted'] = "\"{$record[$this->alias]['full_name']}\" <{$record[$this->alias]['email']}>";
				} else {
					$record[$this->alias]['email_formatted'] = $record[$this->alias]['email'];
				}
			}
		}

		return $record;
	}

	/**
	 * Create a simple person record. This will be called in the case where
	 * a third-party authentication system has logged someone in, but they
	 * don't yet have a Zuluru profile.
	 */
	function create_person_record($data, $name_field) {
		$status = (Configure::read('feature.auto_approve') ? 'active' : 'new');
		$save = array(
			'user_id' => $data[$this->primaryKey],
			'group_id' => 1,	// TODO: Assumed this is the Player group
			'status' => $status,
			'gender' => '',
		);
		if (!empty($name_field)) {
			$save['first_name'] = trim($data[$name_field]);
		}
		if (!empty($save['first_name']) && strpos($save['first_name'], ' ') !== false) {
			list($save['first_name'], $save['last_name']) =
					explode(' ', $save['first_name'], 2);
		}

		// We know this record isn't going to have full validation
		foreach (array_keys($this->validate) as $field) {
			if (!array_key_exists($field, $save)) {
				unset($this->validate[$field]);
			}
		}
		unset($this->validate['gender']);

		$this->create();
		return $this->save($save);
	}

	function findDuplicates($person) {
		if (array_key_exists('AffiliatePerson', $person)) {
			$affiliate = $person['AffiliatePerson']['affiliate_id'];
		} else if (!empty($person['Affiliate'][0]['Affiliate'])) {
			$affiliate = Set::extract('/Affiliate/Affiliate/id', $person);
		} else {
			$affiliate = Set::extract('/Affiliate/id', $person);
		}

		$user_model = Configure::read('security.auth_model');
		$id_field = $this->$user_model->primaryKey;
		$email_field = $this->$user_model->emailField;
		$conditions = array(
			'Person.id !=' => $person['Person']['id'],
			'AffiliatePerson.affiliate_id' => $affiliate,
			'OR' => array(
				array(
					"$user_model.$email_field" => $person['Person']['email'],
					"$user_model.$email_field !=" => '',
					array("$user_model.$email_field !=" => null),
				),
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

		$config = new DATABASE_CONFIG;
		$prefix = $this->$user_model->tablePrefix;
		if ($this->$user_model->useDbConfig != 'default') {
			$config_name = $this->$user_model->useDbConfig;
			$config = $config->$config_name;
			$prefix = "{$config['database']}.$prefix";
		}

		return $this->find('all', array(
				'fields' => array('Person.*', "$user_model.*"),
				'joins' => array(
					array(
						'table' => "{$this->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
					array(
						'table' => "$prefix{$this->$user_model->useTable}",
						'alias' => $user_model,
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => "$user_model.$id_field = Person.user_id",
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
