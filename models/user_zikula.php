<?php
/**
 * Class for handling authentication using the Zikula user database.
 */
class UserZikula extends User {
	var $name = 'UserZikula';
	var $useTable = 'nuke_users';
	var $primaryKey = 'pn_uid';

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
	var $userField = 'pn_uname';

	/**
	 * Column in the table where passwords are stored.
	 */
	var $pwdField = 'pn_pass';

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

/**
 * Merge Zikula and Zuluru user records, if it's not already done.
 *
 * @param array $user Zikula user record
 * @return array Zikula user record merged with Zuluru user data
 * @access public
 */
	function merge_user_record($data) {
		$this->User->contain();
		$user = $this->User->read(null, $data[$this->alias][$this->primaryKey]);
		if (!$user) {
			return $this->create_user_record($data, array(
				'first_name'=> 'pn_name',
				'user_name'	=> 'pn_uname',
				'email'		=> 'pn_email',
			));
		}

		$field_map = array(
			'user_name'	=> 'pn_uname',
			'email'		=> 'pn_email',
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
