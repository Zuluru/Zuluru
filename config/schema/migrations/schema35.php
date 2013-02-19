<?php
class Zuluru35Schema extends CakeSchema {
	var $name = 'Zuluru35';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'teams_people' => 'ALTER TABLE `teams_people` CHANGE COLUMN `position` `role` VARCHAR(16) DEFAULT NULL;',
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
