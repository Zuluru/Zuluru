<?php
/**
 * Derived class for implementing functionality for interfacing with phpBB3.
 * This is intended for use only when you have phpBB3 integrated with some
 * other third-party software which is in turn handling user records.
 */

class UserPhpbb3Component extends UserComponent
{
	function onEdit($record, $old_record) {
		$auth_model = Configure::read('security.auth_model');
		$username_field = $this->_controller->Auth->authenticate->userField;
		$email_field = $this->_controller->Auth->authenticate->emailField;

		// phpBB3 files need these
		global $phpbb_root_path, $phpEx;
		$phpbb_root_path = Configure::read('phpbb3.root_path');
		$phpEx = 'php';
		include($phpbb_root_path . 'config.php');

		$bb3_class = "{$table_prefix}users";
		$bb3_model = ClassRegistry::init($bb3_class);
		$bb3_user = $bb3_model->find('first', array(
				'conditions' => array('username' => $old_record[$auth_model][$username_field])
		));

		// We only care about username and email address changes
		if (empty($bb3_user) || ($bb3_user[$bb3_class]['username'] == $record[$auth_model][$username_field] && $bb3_user[$bb3_class]['user_email'] == $record[$auth_model][$email_field])) {
			return;
		}

		// Includ a couple of things needed for function definitions
		define('IN_PHPBB', true);
		include($phpbb_root_path . 'includes/functions.php');
		include($phpbb_root_path . 'includes/utf/utf_tools.php');

		$clean = utf8_clean_string($record[$auth_model][$username_field]);
		$hash = phpbb_email_hash($record[$auth_model][$email_field]);
		$bb3_model->updateAll(
				array(
					'username' => "'{$record[$auth_model][$username_field]}'",
					'username_clean' => "'$clean'",
					'user_email' => "'{$record[$auth_model][$email_field]}'",
					'user_email_hash' => "'$hash'",
				),
				array('user_id' => $bb3_user[$bb3_class]['user_id'])
		);
	}
}

?>