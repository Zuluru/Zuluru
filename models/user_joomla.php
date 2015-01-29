<?php
App::Import ('model', 'User');

/**
 * Class for handling authentication using the Joomla user database.
 *
 * If you are using this class, you will need to manually add the following
 * entries to the $config['security'] array in the config/install.php file:
 *	'jpath_base'		=> '/path/to/your/joomla/installation',
 */
class UserJoomla extends User {
	var $name = 'UserJoomla';
	var $useDbConfig = 'joomla';
	var $primaryKey = 'id';

	/**
	 * Column in the table where user names are stored.
	 */
	var $userField = 'username';

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
	var $nameField = 'name';

	/**
	 * Column in the table where last login is stored.
	 */
	var $loginField = 'lastvisitDate';

	/**
	 * Accounts (add, delete, passwords) are managed by Joomla, not Zuluru.
	 */
	var $manageAccounts = false;
	var $manageName = 'Joomla';
	var $loginComponent = 'Joomla';

	function __construct($id = false, $table = null, $ds = null) {
		// TODO: Something like this so that it works outside of the Joomla module.
		// This won't be sufficient, though. The CakePHP Auth component assumes that
		// it will be handed a hashed password that it can use, along with the user
		// name, to look up the user, but Joomla can't actually hash the password
		// without the unique salt value saved for each user. To get around this,
		// we'll need a customized Auth component which overrides the login (or
		// identify) function to call a modular function for the comparison.
		//if (!defined('JPATH_BASE'))
		//{
		//	define('JPATH_BASE', Configure::read('security.jpath_base'));
		//	require_once JPATH_BASE . '/configuration.php';
		//}

		$config = new JConfig;
		$this->useTable = $config->dbprefix . 'users';

		parent::__construct($id, $table, $ds);
	}

	// Override the AppModel function for password checking
	function comparepassword($password, $saved) {
		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/joomla/user/helper.php';

		if (strpos(':', $saved) !== false) {
			list($hash, $salt) = explode(':', $saved);
			$crypt = crypt($password, $hash);
			return ("$crypt:$salt" == $saved);
		} else {
			return JUserHelper::verifyPassword($password, $saved);
		}
	}

	function hashPassword($password) {
		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/joomla/user/helper.php';

		$salt = JUserHelper::genRandomPassword(32);
		$crypt = JUserHelper::getCryptedPassword($password, $salt);
		return "$crypt:$salt";
	}

	function activated($data) {
		return (array_key_exists($this->name, $data) && empty($data[$this->name]['activation']));
	}

	function delete_duplicate_user($id) {
		// TODO: Delete j_user_usergroup_map record too
		return $this->delete ($id);
	}

	function merge_duplicate_user($new_id, $old_id) {
		$this->delete_duplicate_user($old_id);
		// TODO: Update j_user_usergroup_map record too
		$this->updateAll (array("{$this->name}.{$this->primaryKey}" => $old_id), array("{$this->name}.{$this->primaryKey}" => $new_id));
	}
}
?>
