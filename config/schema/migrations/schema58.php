<?php
class Zuluru58Schema extends CakeSchema {
	var $name = 'Zuluru58';

	function after($event = array()) {
		switch ($event['update']) {
			case 'prices':
				$commands = array(
					'prices' => 'INSERT INTO `prices` (`id`, `event_id`, `cost`, `tax1`, `tax2`, `open`, `close`, `register_rule`) SELECT `id`, `id`, `cost`, `tax1`, `tax2`, `open`, `close`, `register_rule` FROM `events` ORDER BY `id`;',
				);
				break;

			case 'registrations':
				$commands = array(
					'registrations' => 'UPDATE `registrations` SET `price_id` = `event_id`;',
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

	var $prices = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => '1'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128),
		'description' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'cost' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax1' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax2' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'open' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'close' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'register_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'allow_deposit' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'fixed_deposit' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'deposit_only' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'minimum_deposit' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $registrations = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'price_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'payment' => array('type' => 'string', 'null' => false, 'default' => 'Unpaid', 'length' => 16),
		'total_amount' => array('type' => 'float', 'null' => true, 'default' => '0.00', 'length' => '7,2'),
		'deposit_amount' => array('type' => 'float', 'null' => true, 'default' => '0.00', 'length' => '7,2'),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'person_id' => array('column' => array('person_id', 'event_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
