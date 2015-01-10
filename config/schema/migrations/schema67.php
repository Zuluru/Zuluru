<?php
class Zuluru67Schema extends CakeSchema {
	var $name = 'Zuluru67';

	function after($event = array()) {
		switch ($event['update']) {
			case 'skills':
				$sport = array_shift(array_keys(Configure::read('options.sport')));
				$commands = array(
					'skills' => "INSERT INTO `skills` (`person_id`, `sport`, `enabled`, `skill_level`, `year_started`) SELECT `id`, '$sport', 1, `skill_level`, `year_started` FROM `people` WHERE `year_started` IS NOT NULL ORDER BY `id`;",
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

	// This is the interim version of the people table, unchanged from the previous schema.
	// The migration process will create a new skills table, then select data from people
	// into skills. Only after all of that is done is it safe to drop columns from this.
	var $people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'publish_email' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'home_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_home_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'work_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'work_ext' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
		'publish_work_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'mobile_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_mobile_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'alternate_first_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'alternate_last_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'alternate_email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'publish_alternate_email' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'alternate_work_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'alternate_work_ext' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
		'publish_alternate_work_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'alternate_mobile_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_alternate_mobile_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'addr_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_prov' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_country' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_postalcode' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 7),
		'gender' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
		'birthdate' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'height' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 6),
		'skill_level' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'year_started' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'shirt_size' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'new', 'length' => 16),
		'has_dog' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'contact_for_feedback' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'show_gravatar' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'twitter_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 250),
		'twitter_secret' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 250),
		'complete' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'updated' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $skills = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'sport' => array('type' => 'string', 'null' => false, 'default' => 'ultimate', 'length' => 32),
		'enabled' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'skill_level' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'year_started' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'person' => array('column' => 'person_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
