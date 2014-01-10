<?php
/**
 * Base class for Zuluru authentication. Other variations should extend this
 * and set userField, pwdField and hashMethod as appropriate.  In general, the
 * hashPasswords function will not need to be overloaded.
 */
class User extends AppModel {
	var $name = 'User';
	var $useTable = 'users';
	var $displayField = 'user_name';
	var $actsAs=array(
		'Trim',
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
	);

	var $hasOne = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'user_id',
		)
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
	 * Column in the table where email addresses are stored.
	 */
	var $emailField = 'email';

	/**
	 * Column in the table where actual names are stored.
	 */
	var $nameField = null;

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
		// Move Zuluru user field validation to the fields required by third-party databases
		if ($this->name != 'User') {
			foreach (array(
				'id' => $this->primaryKey,
				'user_name' => $this->userField,
				'email' => $this->emailField,
			) as $alias => $field)
			{
				if (array_key_exists ($alias, $this->validate)) {
					$this->validate[$field] = $this->validate[$alias];
					unset($this->validate[$alias]);
				}
			}
		}
	}

	function beforeSave() {
		if (array_key_exists ($this->alias, $this->data) && array_key_exists ('passwd', $this->data[$this->alias])) {
			$this->data[$this->alias][$this->pwdField] = $this->data[$this->alias]['passwd'];
			$this->data = $this->hashPasswords ($this->data);
		}

		return true;
	}

	function _afterFind($record) {
		// Make third-party user records look more like a Zuluru user record
		if ($this->name != 'User') {
			foreach (array(
				'id' => $this->primaryKey,
				'user_name' => $this->userField,
				'password' => $this->pwdField,
				'email' => $this->emailField,
			) as $alias => $field)
			{
				if (array_key_exists ($field, $record[$this->alias])) {
					$record[$this->alias][$alias] = $record[$this->alias][$field];
				}
			}
		}

		return $record;
	}

	/**
	 * Hash any passwords found in $data
	 *
	 * @param array $data Set of data to look for passwords
	 * @return array Data with passwords hashed
	 * @access public
	 */
	function hashPasswords($data) {
		if (is_array($data) && isset($data[$this->alias])) {
			if (isset($data[$this->alias][$this->pwdField])) {
if (empty($data[$this->alias]['remember_me']) && (!isset($data[$this->alias]['passwd']) || !isset($data[$this->alias]['confirm_passwd']))) {
	pr($data);
	trigger_error('unexpected hashing!', E_USER_ERROR);
}

				$data[$this->alias][$this->pwdField] = $this->hashPassword($data[$this->alias][$this->pwdField]);
			}
		}
		return $data;
	}

	/**
	 * Default method to hash a password, using CakePHP functions.
	 * Third-party user models may override this with specialized functionality.
	 *
	 * @param mixed $password The password to be hashed
	 * @return mixed The hashed password
	 *
	 */
	function hashPassword($password) {
		Security::setHash($this->hashMethod);
		return Security::hash($password, null, Configure::read ('security.salted_hash'));
	}

	/**
	 * Do a system-specific test of whether the account has been activated.
	 */
	function activated($data) {
		// Stand-alone Zuluru has no activation mechanism
		return true;
	}
}
?>
