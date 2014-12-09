<?php
App::Import ('model', 'User');

/**
 * Class for handling authentication using the Zikula user database.
 */
class UserZikula extends User {
	var $name = 'UserZikula';
	var $useTable = 'nuke_users';
	var $primaryKey = 'pn_uid';

	/**
	 * Column in the table where user names are stored.
	 */
	var $userField = 'pn_uname';

	/**
	 * Column in the table where passwords are stored.
	 */
	var $pwdField = 'pn_pass';

	/**
	 * Column in the table where email addresses are stored.
	 */
	var $emailField = 'pn_email';

	/**
	 * Column in the table where actual names are stored.
	 */
	var $nameField = 'pn_name';

	/**
	 * Column in the table where last login is stored.
	 */
	var $loginField = 'pn_lastlogin';

	/**
	 * Function to use for hashing passwords.
	 */
	var $hashMethod = 'sha256';

	/**
	 * Accounts (add, delete, passwords) are managed by Zikula, not Zuluru.
	 */
	var $manageAccounts = false;
	var $manageName = 'Zikula';
	var $loginComponent = 'Zikula';

	function activated($data) {
		return (array_key_exists($this->name, $data) && $data[$this->name]['pn_activated']);
	}

	function delete_duplicate_user($id) {
		// TODO: Delete nuke_group_membership record too
		$this->delete ($id);
	}

	function merge_duplicate_user($new_id, $old_id) {
		$this->delete_duplicate_user($old_id);
		// TODO: Update nuke_group_membership record too
		$this->updateAll (array($this->primaryKey => $old_id), array($this->primaryKey => $new_id));
	}
}
?>
