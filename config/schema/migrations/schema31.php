<?php
class Zuluru31Schema extends CakeSchema {
	var $name = 'Zuluru31';

	function after($event = array()) {
		switch ($event['update']) {
			case 'affiliates':
				$commands = array(
					'affiliates' => 'INSERT INTO `affiliates` (`id`, `name`) SELECT 1, `value` FROM `settings` WHERE `category` = \'organization\' and `name` = \'name\';',
				);
				break;

			case 'affiliates_people':
				$commands = array(
					'affiliates_people' => 'INSERT INTO `affiliates_people` (`affiliate_id`, `person_id`) SELECT 1, `id` FROM `people`;',
				);
				break;
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

	var $affiliates = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $affiliates_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'affiliate_id' => array('type' => 'integer', 'null' => false, 'default' => '1', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'position' => array('type' => 'string', 'null' => true, 'default' => 'player', 'length' => 64),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'affiliate' => array('column' => 'affiliate_id', 'unique' => 0), 'full' => array('column' => array('affiliate_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
