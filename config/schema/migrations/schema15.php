<?php
class Zuluru15Schema extends CakeSchema {
	var $name = 'Zuluru15';

	function after($event = array()) {
		$commands = array(
			'wager' => 'UPDATE `divisions` SET `rating_calculator` = \'wager\' WHERE `schedule_type` = \'ratings_wager_ladder\';',
			'modified_elo' => 'UPDATE `divisions` SET `rating_calculator` = \'modified_elo\' WHERE `schedule_type` = \'ratings_ladder\' OR `schedule_type` = \'roundrobin\';',
			'schedule_type' => 'UPDATE `divisions` SET `schedule_type` = \'ratings_ladder\' WHERE `schedule_type` = \'ratings_wager_ladder\';',
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

	var $divisions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'open' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'close' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'ratio' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 8),
		'current_round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'roster_deadline' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'roster_method' => array('type' => 'string', 'null' => false, 'default' => 'invite', 'length' => 6),
		'roster_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'schedule_type' => array('type' => 'string', 'null' => false, 'default' => 'none', 'length' => 32),
		'rating_calculator' => array('type' => 'string', 'null' => false, 'default' => 'none', 'length' => 32),
		'games_before_repeat' => array('type' => 'integer', 'null' => true, 'default' => '4'),
		'allstars' => array('type' => 'string', 'null' => false, 'default' => 'never', 'length' => 32),
		'exclude_teams' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'coord_list' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'capt_list' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'email_after' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'finalize_after' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'league' => array('column' => 'league_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
