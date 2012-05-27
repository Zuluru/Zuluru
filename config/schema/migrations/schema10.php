<?php
class Zuluru10Schema extends CakeSchema {
	var $name = 'Zuluru10';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'fields' => 'ALTER TABLE `fields` CHANGE COLUMN `parent_id` `facility_id` INT DEFAULT NULL;',
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
		switch ($event['update']) {
			case 'facilities':
				$commands = array(
					'facilities' => 'INSERT INTO `facilities` (`id`, `is_open`, `name`, `code`, `location_street`, `location_city`, `location_province`, `parking`, `region_id`, `driving_directions`, `parking_details`, `transit_directions`, `biking_directions`, `washrooms`, `public_instructions`, `site_instructions`, `sponsor`) SELECT `id`, `is_open`, `name`, `code`, `location_street`, `location_city`, `location_province`, `parking`, `region_id`, `driving_directions`, `parking_details`, `transit_directions`, `biking_directions`, `washrooms`, `public_instructions`, `site_instructions`, `sponsor` FROM `fields` WHERE `facility_id` IS NULL ORDER BY `id`;',
				);
				break;

			case 'fields':
				$commands = array(
					'fields' => 'UPDATE `fields` SET `facility_id` = `id` WHERE `facility_id` IS NULL;',
				);
				break;

			default:
				return;
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

	var $facilities = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 3),
		'location_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_province' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'parking' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'region_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'driving_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'parking_details' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'transit_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'biking_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'washrooms' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'public_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'site_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'sponsor' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	// This is the interim version of the fields table, unchanged from the previous schema.
	// The migration process will create a new facilities table, then select data from fields
	// into facilities. Only after all of that is done is it safe to drop columns from this.
	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'num' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indoor' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'rating' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'facility_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 3),
		'location_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_province' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'latitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'longitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'angle' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'length' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'width' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'zoom' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'parking' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'region_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'driving_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'parking_details' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'transit_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'biking_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'washrooms' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'public_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'site_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'sponsor' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'location_url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'layout_url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'facility' => array('column' => 'facility_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
