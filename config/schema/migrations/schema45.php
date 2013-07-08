<?php
class Zuluru45Schema extends CakeSchema {
	var $name = 'Zuluru45';

	function after($event = array()) {
		$commands = array(
			'season' => 'UPDATE `games` SET `type` = 1 WHERE `tournament` = 0;',
			'tournament' => 'UPDATE `games` SET `type` = 4 WHERE `tournament` = 1;',
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
		'round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'tournament' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
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
}
?>
