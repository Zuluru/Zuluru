<?php
class Zuluru65Schema extends CakeSchema {
	var $name = 'Zuluru65';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'drop_groups' => 'DROP TABLE IF EXISTS `groups`',
					'non-player' => 'UPDATE `people` SET `group_id` = 0 WHERE `group_id` = 4;',
					'admin' => 'UPDATE `people` SET `group_id` = 7 WHERE `group_id` = 3;',
					'manager' => 'UPDATE `people` SET `group_id` = 6 WHERE `group_id` = 5;',
					'volunteer' => 'UPDATE `people` SET `group_id` = 4 WHERE `group_id` = 2;',
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
			case 'groups_people':
				$commands = array(
					// Need IGNORE on the first one, since the admin account will already be set via the groups_people_data file
					'groups_people' => 'INSERT IGNORE INTO `groups_people` (`id`, `person_id`, `group_id`) SELECT `id`, `id`, `group_id` FROM `people` WHERE `group_id` != 0 ORDER BY `id`;',
					'add_admins_as_players' => 'INSERT INTO `groups_people` (`person_id`, `group_id`) SELECT `id`, 1 FROM `people` WHERE `group_id` = 7 ORDER BY `id`;',
					'add_admins_as_volunteers' => 'INSERT INTO `groups_people` (`person_id`, `group_id`) SELECT `id`, 4 FROM `people` WHERE `group_id` = 7 ORDER BY `id`;',
					'add_volunteers_as_players' => 'INSERT INTO `groups_people` (`person_id`, `group_id`) SELECT `id`, 1 FROM `people` WHERE `group_id` = 4 ORDER BY `id`;',
					'add_willing_to_volunteer' => 'INSERT INTO `groups_people` (`person_id`, `group_id`) SELECT `id`, 4 FROM `people` WHERE `willing_to_volunteer` = 1 AND `id` NOT IN (SELECT `person_id` FROM `groups_people` WHERE `group_id` = 4) ORDER BY `id`;',
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

	var $groups_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'person' => array('column' => 'person_id', 'unique' => 0), 'group' => array('column' => 'group_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
