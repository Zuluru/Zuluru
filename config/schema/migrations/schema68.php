<?php
class Zuluru68Schema extends CakeSchema {
	var $name = 'Zuluru68';

	function after($event = array()) {
		$sport = array_shift(array_keys(Configure::read('options.sport')));

		switch ($event['update']) {
			case 'facilities':
				$commands = array(
					'sport' => "UPDATE `facilities` SET `sport` = '$sport';",
					// This isn't related to the other changes, just something that should be done...
					'event_type' => 'UPDATE `event_types` SET `type` = \'individual\' WHERE `id` IN (3,5);',
				);
				break;

			case 'fields':
				$commands = array(
					'sport' => "UPDATE `fields` SET `sport` = '$sport';",
					// Change existing Ultimate fields from 25 yard end zones to 20
					'ultimate' => 'UPDATE `fields` SET `length` = 110 WHERE `length` > 110 AND `sport` = \'ultimate\';',
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

	// Have to include these to get the "after" callback above to be run
	var $facilities = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 3),
		'sport' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'location_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_province' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'parking' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'entrances' => array('type' => 'text', 'null' => true, 'default' => NULL),
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
	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'facility_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'num' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indoor' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'sport' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'surface' => array('type' => 'string', 'null' => false, 'default' => 'grass', 'length' => 32),
		'rating' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'latitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'longitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'angle' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'length' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'width' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'zoom' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'availability' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => '10'),
		'layout_url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'facility' => array('column' => 'facility_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
