<?php
class Zuluru14Schema extends CakeSchema {
	var $name = 'Zuluru14';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'region_preference' => 'UPDATE `teams`, `regions` SET `region_preference` = `regions`.`id` WHERE `region_preference` = `regions`.`name`;',
					// Seems CakePHP's calculations produce the wrong SQL for converting a string to an int column,
					// leaving the character set, etc. in place, so we convert the column here instead.
					'teams' => 'ALTER TABLE `teams` CHANGE `region_preference` `region_preference` int(11);',
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

	var $teams = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100, 'key' => 'index'),
		'division_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'website' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'shirt_colour' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'home_field' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'region_preference' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'open_roster' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'track_attendance' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'attendance_reminder' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_summary' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_notification' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'rating' => array('type' => 'integer', 'null' => true, 'default' => '1500'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'division' => array('column' => 'division_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
