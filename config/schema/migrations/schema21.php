<?php
class Zuluru21Schema extends CakeSchema {
	var $name = 'Zuluru21';

	function after($event = array()) {
		switch ($event['update']) {
			case 'franchises_people':
				$commands = array(
					'franchises_people' => 'INSERT INTO `franchises_people` (`franchise_id`, `person_id`) SELECT `id`, `person_id` FROM `franchises`;',
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

	// If we're upgrading from a very early schema version, there won't
	// be a franchises table, and the INSERT in "after" will fail.
	var $franchises = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'website' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $franchises_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'franchise_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'franchise' => array('column' => 'franchise_id', 'unique' => 0), 'full' => array('column' => array('franchise_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
