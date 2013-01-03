<?php
/**
 * Class for handling authentication using the Joomla user database.
 */
class UserJoomla extends User {
	var $name = 'UserJoomla';
	var $useTable = 'j_users';
	var $primaryKey = 'id';

	// We have to undo the belongsTo from the base User class, since it's not the
	// nuke_users table that has the relation, but the people table, handled with
	// the hasOne below;
	var $belongsTo = array();

	var $hasOne = array(
		'User' => array(
			'className' => 'Person',
			'foreignKey' => 'id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	 * Column in the table where user names are stored.
	 */
	var $userField = 'username';

	/**
	 * Column in the table where passwords are stored.
	 */
	var $pwdField = 'password';

	/**
	 * Function to use for hashing passwords.
	 */
	var $hashMethod = 'md5';

	/**
	 * Accounts (add, delete, passwords) are managed by Joomla, not Zuluru.
	 */
	var $manageAccounts = false;
	var $manageName = 'Joomla';
	var $loginComponent = 'Joomla';

	// TODO: Get the table prefix from Joomla configuration and update $this->useTable

/**
 * Merge Joomla and Zuluru user records, if it's not already done.
 *
 * @param array $user Joomla user record
 * @return array Joomla user record merged with Zuluru user data
 * @access public
 */
	function merge_user_record($data) {
		$this->User->contain();
		$user = $this->User->read(null, $data[$this->alias][$this->primaryKey]);
		if (!$user) {
			return $this->create_user_record($data, array(
				'first_name'=> 'name',
				'user_name'	=> 'username',
				'email'		=> 'email',
			));
		}

		$field_map = array(
			'user_name'	=> 'username',
			'email'		=> 'email',
		);
		foreach ($field_map as $new => $old) {
			if ($data[$this->alias][$old] != $user['User'][$new]) {
				$this->User->saveField ($new, $data[$this->alias][$old]);
			}
		}

		// We don't want this data hanging around in $User->data to mess up later saves
		$this->User->data = null;

		return true;
	}

	function delete_duplicate_user($id) {
		// TODO: Delete j_user_usergroup_map record too
		$this->delete ($id);
	}

	function merge_duplicate_user($new_id, $old_id) {
		$this->delete_duplicate_user($old_id);
		// TODO: Update j_user_usergroup_map record too
		$this->updateAll (array("{$this->name}.{$this->primaryKey}" => $old_id), array("{$this->name}.{$this->primaryKey}" => $new_id));
	}
}
?>
