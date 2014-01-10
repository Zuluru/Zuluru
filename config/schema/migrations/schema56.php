<?php
class Zuluru56Schema extends CakeSchema {
	var $name = 'Zuluru56';

	function after($event = array()) {
		switch ($event['update']) {
			case 'people':
				$commands = array(
					'people' => 'UPDATE `people` SET `user_id` = `id`;',
				);
				break;

			case 'users':
				$commands = array(
					'users' => 'INSERT INTO `users` (`id`, `user_name`, `password`, `email`, `session_cookie`, `last_login`, `client_ip`) SELECT `id`, `user_name`, `password`, `email`, `session_cookie`, `last_login`, `client_ip` FROM `people` ORDER BY `id`;',
				);
				break;

			default:
				return true;
		}
		return $this->_execute($commands);
	}

	function _execute($commands) {
		if (!isset($this->db)) {
			$this->db =& ConnectionManager::getDataSource($this->connection);
			if (!$this->db->isConnected()) {
				// TODO: How to report errors from here?
				//$this->Session->setFlash(__('Could not connect to database.', true), 'default', array('class' => 'error'));
				return false;
			}
		}
		$results = array();
		foreach ($commands as $table => $sql) {
			if (!$this->db->execute($sql)) {
				//$error = $table . ': '  . $db->lastError();
				return false;
			}
			$results[$table] = __('updated.', true);
		}
		return $results;
	}

	// Interim version that adds the new columns but doesn't remove the old ones
	var $people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'user_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'publish_email' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'home_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_home_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'work_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'work_ext' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
		'publish_work_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'mobile_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_mobile_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'addr_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_prov' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_country' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_postalcode' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 7),
		'gender' => array('type' => 'string', 'null' => false, 'default' => 'Male', 'length' => 6),
		'birthdate' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'height' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 6),
		'skill_level' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'year_started' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'shirt_size' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'session_cookie' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'new', 'length' => 16),
		'has_dog' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'willing_to_volunteer' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'contact_for_feedback' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'client_ip' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'twitter_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 250),
		'twitter_secret' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 250),
		'complete' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'user_name', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	// Add the new users table before removing columns from the people table
	var $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'user_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'session_cookie' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'client_ip' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'user_name', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
