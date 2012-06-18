<?php
class Zuluru13Schema extends CakeSchema {
	var $name = 'Zuluru13';

	function after($event = array()) {
		// Move "team_division" in event custom configuration to new "division_id" column
		$event_obj = ClassRegistry::init('Event');
		// An event object was created earlier during the schema read process,
		// and we'll get that same one back, with the obsolete schema in place
		$event_obj->_schema = null;
		$events = $event_obj->find('all', array(
				'conditions' => array('custom LIKE' => '%team_division%'),
				'contain' => false,
		));
		foreach ($events as $event) {
			$custom = unserialize ($event['Event']['custom']);
			$event['Event']['division_id'] = $custom['team_division'];
			unset($custom['team_division']);
			if (!empty($custom)) {
				$event['Event']['custom'] = serialize($custom);
			} else {
				$event['Event']['custom'] = null;
			}
			$event_obj->save($event['Event']);
		}

		$commands = array(
			'events' => 'UPDATE `event_types` SET `type` = \'individual\' WHERE `name` LIKE \'Individuals%\';',
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

	var $events = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128, 'key' => 'unique'),
		'description' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'event_type_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'waiver_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'cost' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax1' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax2' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'open' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'close' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'cap_male' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'cap_female' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'multiple' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'questionnaire_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'division_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'custom' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'register_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
