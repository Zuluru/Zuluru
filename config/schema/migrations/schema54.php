<?php
class Zuluru54Schema extends CakeSchema {
	var $name = 'Zuluru54';

	function after($event = array()) {
		$commands = array(
			'slot' => 'UPDATE `games`,`game_slots` SET `games`.`game_slot_id` = `game_slots`.`id`, `game_slots`.`assigned` = 1 WHERE `games`.`id` = `game_slots`.`game_id`;',
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

	// Interim version that adds the new columns but doesn't remove the old ones
	var $games = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'division_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'game_slot_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'tournament_pool' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '1'),
		'pool_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'home_dependency_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'home_dependency_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'home_pool_team_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'home_team' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'away_dependency_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'away_dependency_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'away_pool_team_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'away_team' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'home_score' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 4),
		'away_score' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 4),
		'rating_points' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'approved_by' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'normal', 'length' => 32),
		'published' => array('type' => 'boolean', 'null' => true, 'default' => '1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'game_division' => array('column' => 'division_id', 'unique' => 0), 'game_home_team' => array('column' => 'home_team', 'unique' => 0), 'game_away_team' => array('column' => 'away_team', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $game_slots = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'field_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'game_date' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'game_start' => array('type' => 'time', 'null' => true, 'default' => NULL),
		'game_end' => array('type' => 'time', 'null' => true, 'default' => NULL),
		'game_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'assigned' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
