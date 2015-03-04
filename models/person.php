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
				'alternate_work_phone' => 'phone_format',
				'alternate_mobile_phone' => 'phone_format',
			),
		),
	);

	var $validate = array(
		'first_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'First name must not be blank.',
				'last' => true,
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
				'last' => true,
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
				'allowEmpty' => true,
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
		'alternate_first_name' => array(
			'valid' => array(
				'rule' => array('custom', self::NAME_REGEX),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.',
			),
		),
		'alternate_last_name' => array(
			'valid' => array(
				'rule' => array('custom', self::NAME_REGEX),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Names can only include letters, numbers, spaces, commas, periods, apostrophes and hyphens.',
			),
		),
		'alternate_work_phone' => array(
			'phone' => array(
				'rule' => array('phone'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Please supply area code and number.',
			),
		),
		'alternate_work_ext' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Please supply extension, if any.',
			),
		),
		'publish_alternate_work_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
				'required' => false,
			),
		),
		'alternate_mobile_phone' => array(
			'phone' => array(
				'rule' => array('phone'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'Please supply area code and number.',
			),
		),
		'publish_alternate_mobile_phone' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
				'required' => false,
			),
		),
		'alternate_email' => array(
			'email' => array(
				'rule' => array('email'),
				'allowEmpty' => true,
				'required' => false,
				'message' => 'You must supply a valid email address.',
			),
		),
		'publish_alternate_email' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
				'required' => false,
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
				'last' => true,
			),
			'range' => array(
				'rule' => array('indateconfig', 'born'),
				'message' => 'You must provide a valid birthdate.',
			),
		),
		'height' => array(
			'range' => array(
				'rule' => array('range', 35, 85),
				'message' => 'You must enter a valid height.',
			),
		),
		'shirt_size' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.shirt_size'),
				'message' => 'You must select a valid shirt size.',
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
		'contact_for_feedback' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'allowEmpty' => true,
			),
		),
	);

	var $hasMany = array(
		'Skill' => array(
			'className' => 'Skill',
			'foreignKey' => 'person_id',
			'dependent' => true,
		),
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
		'Group' => array(
			'className' => 'Group',
			'joinTable' => 'groups_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'group_id',
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
		'Related' => array(
			'className' => 'Person',
			'joinTable' => 'people_people',
			'with' => 'PeoplePerson',
			'foreignKey' => 'relative_id',
			'associationForeignKey' => 'person_id',
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

	// The _handlePersonSearch and _handleRuleSearch functions need custom counting.
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		// This functionality is mostly copied directly from the standard implementation.
		// If a situation ever arises where we don't want to count distinct person ids
		// in this function, something will need to change to differentiate that situation
		// from searches.
		$parameters = compact('conditions');
		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}
		$parameters['fields'] = 'DISTINCT Person.id';
		return $this->find('count', array_merge($parameters, $extra));
	}

	function beforeValidate() {
		// Save or restore the default validation info, for when we save a few records
		if (!isset($this->defaultValidate)) {
			$this->defaultValidate = $this->validate;
		} else {
			$this->validate = $this->defaultValidate;
		}

		if (array_key_exists('addr_country', $this->data[$this->alias])) {
			if ($this->data[$this->alias]['addr_country'] == 'United States') {
				$this->validate['addr_postalcode']['postal'] = array(
					'rule' => array('postal', null, 'us'),
					'message' => 'You must enter a valid US zip code',
				);
			}
			if ($this->data[$this->alias]['addr_country'] == 'Mexico') {
				$this->validate['addr_postalcode']['postal'] = array(
					'rule' => array('postal', null, 'us'), // same format as US
					'message' => 'You must enter a valid Mexican postal code',
				);
			}
		}

		if (Configure::read('feature.birth_year_only') && !empty($this->validate['birthdate'])) {
			$this->validate['birthdate']['date'] = array(
				'rule' => array('date', 'y', '/^(\\d{4})/'),
				'message' => 'You must provide a valid birthdate.',
			);
		}

		if (Configure::read('feature.units') == 'Metric' && !empty($this->validate['height'])) {
			$this->validate['height']['range']['rule'] = array('range', 90, 215);
		}

		$always_on = array('email');
		foreach (array_keys($this->validate) as $field) {
			$short_field = strtr($field, array('publish_' => '', 'alternate_' => ''));
			if (!in_array($short_field, $always_on) && !Configure::read("profile.$short_field")) {
				unset($this->validate[$field]);
			}
		}

		// Adjust to require any one phone number, if at least one option is enabled
		$first_field = $found = false;
		foreach (array('home_phone', 'work_phone', 'mobile_phone')as $field) {
			if (Configure::read("profile.$field")) {
				if (!$first_field) {
					$first_field = $field;
				}
				if (!empty($this->data[$this->alias][$field])) {
					$found = true;
				}
			}
		}
		if ($first_field && !$found && array_key_exists($first_field, $this->validate)) {
			$this->validate[$first_field]['notempty'] = array(
				'rule' => array('notempty'),
				'message' => 'You must provide at least one phone number.',
			);
			$this->validate[$first_field]['phone']['allowEmpty'] = false;
		}
	}

	function beforeValidateChild() {
		foreach (array('home_phone', 'work_phone', 'work_ext', 'mobile_phone', 'addr_street', 'addr_city', 'addr_prov', 'addr_country', 'addr_postalcode') as $field) {
			unset($this->validate[$field]);
		}
	}

	function beforeValidateNonPlayer() {
		foreach (array('gender', 'birthdate', 'height', 'shirt_size') as $field) {
			unset($this->validate[$field]);
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
		if (!empty($user['last_login'])) {
			$record[$this->alias]['last_login'] = $user['last_login'];
		}
		if (!empty($user['client_ip'])) {
			$record[$this->alias]['client_ip'] = $user['client_ip'];
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
			if (!empty($record[$this->alias]['alternate_email'])) {
				if (!empty ($record[$this->alias]['full_name'])) {
					$record[$this->alias]['alternate_email_formatted'] = "\"{$record[$this->alias]['full_name']} (alternate)\" <{$record[$this->alias]['alternate_email']}>";
				} else {
					$record[$this->alias]['alternate_email_formatted'] = $record[$this->alias]['alternate_email'];
				}
			}
		}

		if (array_key_exists ('alternate_first_name', $record[$this->alias]) && array_key_exists ('alternate_last_name', $record[$this->alias])) {
			$record[$this->alias]['alternate_full_name'] = trim ("{$record[$this->alias]['alternate_first_name']} {$record[$this->alias]['alternate_last_name']}");
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
					'Person.first_name' => $person['Person']['first_name'],
					'Person.last_name' => $person['Person']['last_name'],
				),
			),
		);

		if (!empty($person['Person']['email'])) {
			$conditions['OR'][] = array(
				"$user_model.$email_field" => $person['Person']['email'],
				"$user_model.$email_field !=" => '',
				array("$user_model.$email_field !=" => null),
			);
			$conditions['OR'][] = array(
				'Person.alternate_email' => $person['Person']['email'],
				'Person.alternate_email !=' => '',
				array('Person.alternate_email !=' => null),
			);
		}

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
			$conditions['OR'][] = array(
				'Person.addr_street' => $person['Person']['addr_street'],
				'Person.addr_street !=' => '',
				array('Person.addr_street !=' => null),
			);
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
				'contain' => array('Skill', 'Group'),
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
