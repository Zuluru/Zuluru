<?php
class Zuluru60Schema extends CakeSchema {
	var $name = 'Zuluru60';

	function after($event = array()) {
		switch ($event['update']) {
			case 'payments':
				$commands = array(
					'online' => 'UPDATE `payments` SET `payment_method` = \'Online\' WHERE `created_person_id` IS NULL;',
					'other' => 'UPDATE `payments` SET `payment_method` = \'Other\' WHERE `created_person_id` IS NOT NULL;',
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

	var $payments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'registration_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'registration_audit_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'payment_type' => array('type' => 'string', 'null' => false, 'default' => 'Full', 'length' => '32'),
		'payment_method' => array('type' => 'string', 'null' => false, 'default' => 'Other', 'length' => '32'),
		'payment_amount' => array('type' => 'float', 'null' => true, 'default' => '0.00', 'length' => '7,2'),
		'refunded_amount' => array('type' => 'float', 'null' => false, 'default' => '0.00', 'length' => '7,2'),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'created_person_id' => array('type' => 'integer', 'null' => true),
		'updated_person_id' => array('type' => 'integer', 'null' => true),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'registration_id' => array('column' => array('registration_id', 'payment_type'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
