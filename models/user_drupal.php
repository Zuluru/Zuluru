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
		return (array_key_exists($this->name, $data) && $data[$this->name]['status'] != 0);
	}

	function delete_duplicate_user($id) {
		// TODO: Delete users_roles record too
		$this->delete ($id);
	}

	function merge_duplicate_user($new_id, $old_id) {
		$this->delete_duplicate_user($old_id);
		// TODO: Update users_roles record too
		$this->updateAll (array("{$this->name}.{$this->primaryKey}" => $old_id), array("{$this->name}.{$this->primaryKey}" => $new_id));
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
