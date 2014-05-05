<?php
class Zuluru64Schema extends CakeSchema {
	var $name = 'Zuluru64';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'registrations' => 'UPDATE `registrations` SET `payment` = \'Cancelled\' WHERE `payment` = \'Refunded\';',
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
}
?>
