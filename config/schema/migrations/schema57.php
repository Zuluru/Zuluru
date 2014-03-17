<?php
class Zuluru57Schema extends CakeSchema {
	var $name = 'Zuluru57';

	function after($event = array()) {
		switch ($event['update']) {
			case 'payments':
				$commands = array(
					// Set the total amount for anything where we have an audit record that applies to a single registration
					'total_from_audit' => 'UPDATE `registrations` r, `registration_audits` a SET `r`.`total_amount` = `a`.`charge_total` WHERE `r`.`id` IN (SELECT `registration_id` from `registration_audits` GROUP BY `transaction_id` HAVING COUNT(`id`) = 1) AND `r`.`id` = `a`.`registration_id` AND `r`.`total_amount` = 0;',
					// Where we have a single audit record covering multiple registrations, take the total from the event, and hope that it didn't change
					'total_from_event' => 'UPDATE `registrations` r, `events` e SET `r`.`total_amount` = `e`.`cost` + IFNULL(`e`.`tax1`,0) + IFNULL(`e`.`tax2`,0) WHERE `r`.`event_id` = `e`.`id` AND `r`.`total_amount` = 0;',
					'payments' => 'INSERT INTO `payments` (`registration_id`, `registration_audit_id`, `payment_amount`, `created`) SELECT `registrations`.`id`, `registration_audits`.`id`, `total_amount`, `created` FROM `registrations`, `registration_audits` WHERE `registrations`.`id` = `registration_audits`.`registration_id`;',
					'refunds' => 'INSERT INTO `payments` (`registration_id`, `payment_type`, `payment_amount`, `created`) SELECT `registrations`.`id`, \'Refund\', -`total_amount`, `modified` FROM `registrations` WHERE `registrations`.`payment` = \'Refunded\';',
					'refund_amounts' => 'UPDATE `payments` p, `registrations` r SET `p`.`refunded_amount` = `p`.`payment_amount` WHERE `p`.`registration_id` = `r`.`id` AND `r`.`payment` = \'Refunded\' AND `p`.`payment_type` != \'Refund\';',
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

	var $registrations = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'payment' => array('type' => 'string', 'null' => false, 'default' => 'Unpaid', 'length' => 16),
		'total_amount' => array('type' => 'float', 'null' => true, 'default' => '0.00', 'length' => '7,2'),
		'deposit_amount' => array('type' => 'float', 'null' => true, 'default' => '0.00', 'length' => '7,2'),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'person_id' => array('column' => array('person_id', 'event_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $payments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'registration_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'registration_audit_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'payment_type' => array('type' => 'string', 'null' => false, 'default' => 'Full', 'length' => '32'),
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