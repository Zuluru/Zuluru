<?php
App::Import ('model', 'User');

/**
 * Class for handling authentication using the Drupal user database.
 *
 * If you are using this class, you will need to manually add the following
 * entries to the $config['security'] array in the config/install.php file:
 *	'drupal_root'		=> '/path/to/your/drupal/installation',
 *	'auth_session'		=> 'your.domain.name',
 */
class UserDrupal extends User {
	var $name = 'UserDrupal';
	var $useDbConfig = 'drupal';
	var $primaryKey = 'uid';

	var $hasOne = array(
		// This is duplicated from the base User class, or else it gets blown away
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'user_id',
		),
		'Session' => array(
			'className' => 'UserDrupalSession',
			'foreignKey' => 'uid',
		),
	);

	/**
	 * Column in the table where user names are stored.
	 */
	var $userField = 'name';

	/**
	 * Column in the table where passwords are stored.
	 */
	var $pwdField = 'pass';

	/**
	 * Column in the table where email addresses are stored.
	 */
	var $emailField = 'mail';

	/**
	 * Column in the table where last login is stored.
	 */
	var $loginField = 'login';

	/**
	 * Accounts (add, delete, passwords) are managed by Drupal, not Zuluru.
	 */
	var $manageAccounts = false;
	var $manageName = 'Drupal';
	var $loginComponent = 'Drupal';

	function __construct($id = false, $table = null, $ds = null) {
		global $databases;

		if (!defined('DRUPAL_ROOT'))
		{
			define('DRUPAL_ROOT', Configure::read('security.drupal_root'));
			require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
			drupal_settings_initialize();
		}

		$this->useTable = $databases['default']['default']['prefix'] . 'users';

		parent::__construct($id, $table, $ds);
	}

	// Override the AppModel function for password checking
	function comparepassword($password, $saved) {
		require_once DRUPAL_ROOT . '/includes/password.inc';

		$account = new Object();
		$account->pass = $saved;
		return user_check_password($password, $account);
	}

	function hashPassword($password) {
		require_once DRUPAL_ROOT . '/includes/password.inc';

		return user_hash_password($password);
	}

	function activated($data) {
		return (!empty($data[$this->name]) && $data[$this->name]['status'] != 0);
	}

	function delete_duplicate_user($id) {
		// TODO: Delete users_roles record too
		return $this->delete ($id);
	}

	function merge_duplicate_user($new_id, $old_id) {
		if (!is_numeric($old_id)) {
			return;
		}
		$this->delete_duplicate_user($old_id);
		// TODO: Update users_roles record too
		$this->updateAll (array("{$this->name}.{$this->primaryKey}" => $old_id), array("{$this->name}.{$this->primaryKey}" => $new_id));
	}

	function beforeSave() {
		if (array_key_exists($this->alias, $this->data) && empty($this->data[$this->alias][$this->primaryKey])) {
			// Drupal doesn't use auto increment on the uid column.
			// This hack is adapted from Drupal's methods...
			// It will leave extra records in the sequences table,
			// but Drupal will take care of that for us.
			global $databases;
			if (!$this->query("INSERT INTO {$databases['default']['default']['prefix']}sequences () VALUES ();", false)) {
				return false;
			}
			$id = $this->query('SELECT LAST_INSERT_ID();', false);
			$id = array_shift(array_shift(array_shift($id)));
			$this->data[$this->alias][$this->primaryKey] = $id;
			$this->data[$this->alias]['status'] = 1; // don't require further activation in Drupal
		}

		return parent::beforeSave();
	}
}

// Auxilliary classs only required for UserDrupal containment
class UserDrupalSession extends AppModel {
	var $name = 'UserDrupalSession';
	var $useDbConfig = 'drupal';
	var $primaryKey = 'uid';

	function __construct($id = false, $table = null, $ds = null) {
		global $databases;

		if (!defined('DRUPAL_ROOT'))
		{
			define('DRUPAL_ROOT', Configure::read('security.drupal_root'));
			require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
			drupal_settings_initialize();
		}

		$this->useTable = $databases['default']['default']['prefix'] . 'sessions';

		parent::__construct($id, $table, $ds);
	}
}
?>
