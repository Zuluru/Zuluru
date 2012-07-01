<?php
class Zuluru25Schema extends CakeSchema {
	var $name = 'Zuluru25';

	function after($event = array()) {
		$commands = array(
			'no_default' => 'UPDATE `score_entries` SET `status` = \'normal\' WHERE `status` = \'no\';',
			'home_default' => 'UPDATE `score_entries`,`games` SET `score_entries`.`status` = \'home_default\' WHERE `games`.`id` = `score_entries`.`game_id` AND ((`defaulted` = \'us\' AND `team_id` = `home_team`) OR (`defaulted` = \'them\' AND `team_id` = `away_team`));',
			'away_default' => 'UPDATE `score_entries`,`games` SET `score_entries`.`status` = \'away_default\' WHERE `games`.`id` = `score_entries`.`game_id` AND ((`defaulted` = \'them\' AND `team_id` = `home_team`) OR (`defaulted` = \'us\' AND `team_id` = `away_team`));',
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

	// Interim version that adds the new column but doesn't remove the old one
	var $score_entries = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'game_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'score_for' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'score_against' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'spirit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'defaulted' => array('type' => 'string', 'null' => false, 'default' => 'no', 'length' => 8),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'normal', 'length' => 32),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'team' => array('column' => array('team_id', 'game_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
