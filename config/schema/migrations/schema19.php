<?php
class Zuluru19Schema extends CakeSchema {
	var $name = 'Zuluru19';

	function after($event = array()) {
		$commands = array(
			'email_score_mismatch' => 'UPDATE `activity_logs` SET `game_id` = `primary_id` WHERE `type` = \'email_score_mismatch\';',
			'email_score_reminder' => 'UPDATE `activity_logs` SET `game_id` = `primary_id`, `team_id` = `secondary_id` WHERE `type` = \'email_score_reminder\' OR `type` = \'email_score_approval\';',
			'email_attendance_reminder' => 'UPDATE `activity_logs` SET `game_id` = `primary_id`, `person_id` = `secondary_id` WHERE `type` = \'email_attendance_reminder\';',
			'email_attendance_summary' => 'UPDATE `activity_logs` SET `game_id` = `primary_id`, `team_id` = `secondary_id` WHERE `type` = \'email_attendance_summary\';',
			'email_membership_letter' => 'UPDATE `activity_logs` SET `custom` = `primary_id`, `person_id` = `secondary_id` WHERE `type` = \'email_membership_letter\';',
			'email_event_attendance_reminder' => 'UPDATE `activity_logs` SET `team_event_id` = `primary_id`, `person_id` = `secondary_id` WHERE `type` = \'email_event_attendance_reminder\';',
			'email_event_attendance_summary' => 'UPDATE `activity_logs` SET `team_event_id` = `primary_id`, `team_id` = `secondary_id` WHERE `type` = \'email_event_attendance_summary\';',
			'roster_invite_reminder' => 'UPDATE `activity_logs` SET `team_id` = `primary_id`, `person_id` = `secondary_id` WHERE `type` = \'roster_invite_reminder\' OR `type` = \'roster_request_reminder\';',
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

	// Interim version that adds new columns but doesn't remove the old ones
	var $activity_logs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128, 'key' => 'index'),
		'primary_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'secondary_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'team_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'person_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'game_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'team_event_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'custom' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
