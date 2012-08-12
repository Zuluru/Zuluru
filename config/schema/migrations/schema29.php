<?php
class Zuluru29Schema extends CakeSchema {
	var $name = 'Zuluru29';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'waivers' => 'ALTER TABLE `waivers` DROP KEY `key`;',
					'waivers_people' => 'RENAME TABLE `waivers` TO `waivers_people`;',
				);
				break;

			default:
				return true;
		}

		if (empty($event['execute'])) {
			return $commands;
		}

		// Execute the commands
		return $this->_execute($commands);
	}

	function after($event = array()) {
		$commands = array(
			'membership' => 'UPDATE `waivers_people` SET `waiver_id` = 1 WHERE `type` = \'membership\';',
			'event' => 'UPDATE `waivers_people` SET `waiver_id` = 2 WHERE `type` = \'event\';',
			'dog' => 'UPDATE `waivers_people` SET `waiver_id` = 3 WHERE `type` = \'dog\';',
			'valid_from' => 'UPDATE `waivers_people` SET `valid_from` = `created`;',
			'valid_until' => 'UPDATE `waivers_people` SET `valid_until` = `expires`;',
		);
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

	// Interim version that adds the new column but doesn't remove the old one
	var $waivers_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'waiver_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16),
		'created' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'expires' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'valid_from' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'valid_until' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'person' => array('column' => 'person_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}

?>