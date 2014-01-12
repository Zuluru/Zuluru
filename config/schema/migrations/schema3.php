<?php
class Zuluru3Schema extends CakeSchema {
	var $name = 'Zuluru3';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'leagues' => 'ALTER TABLE `leagues_people` CHANGE COLUMN `status` `position` VARCHAR(64) DEFAULT \'coordinator\';',
					'teams' => 'ALTER TABLE `teams_people` CHANGE COLUMN `status` `position` VARCHAR(16);'
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
			'invite' => 'UPDATE `teams_people` SET `status` = 2, `position` = \'player\' WHERE `position` = \'captain_request\';',
			'request' => 'UPDATE `teams_people` SET `status` = 3, `position` = \'player\' WHERE `position` = \'player_request\';',
			'confirmed' => 'UPDATE `teams_people` SET `status` = 1 WHERE `status` = 0;',
		);

		// Execute the commands
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
	var $teams_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'position' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'full' => array('column' => array('team_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
