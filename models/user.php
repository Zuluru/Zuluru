<?php
/**
 * Base class for Zuluru authentication. Other variations should extend this
 * and set userField, pwdField and hashMethod as appropriate.  In general, the
 * hashPasswords function will not need to be overloaded.
 */
class User extends AppModel {
	var $name = 'User';
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
		'user_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'User name must not be blank.',
			),
			'isunique' => array(
				'rule' => array('isUnique'),
				'message' => 'That user name is already taken',
			),
		),
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
		'passold' => array(
			'match' => array(
				'rule' => array('matchpassword'),
				'message' => 'Old password is not correct',
			),
		),
		'passwd' => array(
			'between' => array(
				'rule' => array('between', 6, 50),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Password must be between 6 and 50 characters long'
			),
			'mustnotmatch' => array(
				'rule' => array('mustnotmatch','passwd','user_name'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'You cannot use your user name as your password.'
			),
		),
		'confirm_passwd' => array(
			'mustmatch' => array(
				'rule' => array('mustmatch','passwd','confirm_passwd'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Passwords must match.'
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'You must supply a valid email address.',
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

	var $hasAndBelongsToMany = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'joinTable' => 'affiliates_people',
			'foreignKey' => 'person_id',
			'associationForeignKey' => 'affiliate_id',
			'unique' => true,
		),
	);

	/**
	 * Column in the table where user names are stored.
	 */
	var $userField = 'user_name';

	/**
	 * Column in the table where passwords are stored.
	 */
	var $pwdField = 'password';

	/**
	 * Function to use for hashing passwords. This must match the type given
	 * in the hash call in the install controller.
	 */
	// TODO: Use md5 for converted Leaguerunner databases.
	var $hashMethod = 'sha256';

	/**
	 * Accounts (add, delete, passwords) are managed by Zuluru.
	 */
	var $manageAccounts = true;
	var $manageName = 'Zuluru';
	var $loginComponent = null;

	function beforeValidate() {
		if (array_key_exists('addr_country', $this->data[$this->alias]) && $this->data[$this->alias]['addr_country'] == 'United States') {
			$this->validate['addr_postalcode']['postal'] = array(
				'rule' => array('postal', null, 'us'),
				'message' => 'You must enter a valid US zip code',
			);
		}

		foreach (array_keys($this->validate) as $field) {
			if ($field != $this->userField && !Configure::read("profile.$field")) {
				unset($this->validate[$field]);
			}
		}
	}

	function beforeSave() {
		if (array_key_exists ('User', $this->data) && array_key_exists ('passwd', $this->data['User'])) {
			$this->data['User']['password'] = $this->data['User']['passwd'];
			$this->data = $this->hashPasswords ($this->data);
		}
		return true;
	}

	function _afterFind ($record) {
		if (array_key_exists ('first_name', $record[$this->alias]) && array_key_exists ('last_name', $record[$this->alias])) {
			$record[$this->alias]['full_name'] = trim ("{$record[$this->alias]['first_name']} {$record[$this->alias]['last_name']}");
			if (array_key_exists ('email', $record[$this->alias]) && !empty($record[$this->alias]['email'])) {
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
 * Create a simple user record. This will be called in the case where
 * a third-party authentication system has logged someone in, but they
 * don't yet have a Zuluru profile.
 */
	function create_user_record($data, $field_map) {
		$status = (Configure::read('feature.auto_approve') ? 'active' : 'new');
		$save = array(
			'id' => $data[$this->alias][$this->primaryKey],
			'group_id' => 1,	// TODO: Assumed this is the Player group
			'status' => $status,
		);
		foreach ($field_map as $new => $old) {
			$save[$new] = trim($data[$this->alias][$old]);
		}
		if (!empty($save['first_name']) && empty($save['last_name']) && strpos($save['first_name'], ' ') !== false) {
			list($save['first_name'], $save['last_name']) =
					explode(' ', $save['first_name'], 2);
		}

		// We know this record isn't going to have full validation
		foreach (array_keys($this->User->validate) as $field) {
			if (!array_key_exists($field, $save)) {
				unset($this->User->validate[$field]);
			}
		}

		$this->User->create();
		return $this->User->save($save);
	}

/**
 * Hash any passwords found in $data
 *
 * @param array $data Set of data to look for passwords
 * @return array Data with passwords hashed
 * @access public
 */
	function hashPasswords($data) {
		Security::setHash($this->hashMethod);

		if (is_array($data) && isset($data[$this->alias])) {
			if (isset($data[$this->alias][$this->userField]) && isset($data[$this->alias][$this->pwdField])) {
				// Existing user databases didn't hash passwords using the salt
				$data[$this->alias][$this->pwdField] = Security::hash($data[$this->alias][$this->pwdField], null, Configure::read ('security.salted_hash'));
			}
		}
		return $data;
	}
}
?>
