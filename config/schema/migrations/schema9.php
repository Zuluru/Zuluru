<?php
class Zuluru9Schema extends CakeSchema {
	var $name = 'Zuluru9';

	function before($event = array()) {
		switch ($event['update']) {
			case 'schema':
				$commands = array(
					'leagues' => 'RENAME TABLE `leagues` TO `divisions`;',
					'leagues_days' => 'RENAME TABLE `leagues_days` TO `divisions_days`;',
					'league_gameslot_availabilities' => 'RENAME TABLE `league_gameslot_availabilities` TO `division_gameslot_availabilities`;',
					'leagues_people' => 'RENAME TABLE `leagues_people` TO `divisions_people`;',
					'games' => 'ALTER TABLE `games` CHANGE COLUMN `league_id` `division_id` INT NOT NULL DEFAULT 0;',
					'teams' => 'ALTER TABLE `teams` CHANGE COLUMN `league_id` `division_id` INT DEFAULT NULL;',
					'divisions_days' => 'ALTER TABLE `divisions_days` CHANGE COLUMN `league_id` `division_id` INT NOT NULL;',
					'divisions_people' => 'ALTER TABLE `divisions_people` CHANGE COLUMN `league_id` `division_id` INT NOT NULL DEFAULT 0;',
					'division_gameslot_availabilities' => 'ALTER TABLE `division_gameslot_availabilities` CHANGE COLUMN `league_id` `division_id` INT NOT NULL DEFAULT 0;',
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
			case 'divisions':
				// Change "team_league" to "team_division" in event custom configuration
				$event_obj = ClassRegistry::init('Event');
				$events = $event_obj->find('all', array(
						'conditions' => array('custom LIKE' => '%team_league%'),
						'contain' => false,
				));
				foreach ($events as $event) {
					$custom = unserialize ($event['Event']['custom']);
					$custom['team_division'] = $custom['team_league'];
					unset($custom['team_league']);
					$event['Event']['custom'] = serialize($custom);
					$event_obj->save($event['Event']);
				}

				$commands = array(
					'divisions' => 'UPDATE `divisions` SET `league_id` = `id`;',
				);
				break;

			case 'leagues':
				$commands = array(
					'leagues' => 'INSERT INTO `leagues` (`id`, `name`, `season`, `open`, `close`, `is_open`, `schedule_attempts`, `display_sotg`, `sotg_questions`, `numeric_sotg`, `expected_max_score`) SELECT `id`, `name`, `season`, `open`, `close`, `is_open`, `schedule_attempts`, `display_sotg`, `sotg_questions`, `numeric_sotg`, `expected_max_score` FROM `divisions` ORDER BY `id`;',
					// By default, we'll put the name on the league and blank the division name.
					// Clubs with existing league structures that contain multiple divisions will
					// need to do some manual processing of names and which divisions are in which
					// leagues, but TUC is probably the only such club.
					'divisions' => 'UPDATE `divisions` SET `name` = \'\';',
				);
				break;

			default:
				return;
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

	// This is the interim version of the divisions table, same as leagues was previously.
	// The migration process will rename leagues to divisions, create a new leagues table,
	// then select data from divisions into leagues. Only after all of that is done is it
	// safe to drop columns from this.
	var $divisions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'season' => array('type' => 'string', 'null' => false, 'default' => 'None', 'length' => 16),
		'open' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'close' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'ratio' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 8),
		'current_round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'roster_deadline' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'roster_method' => array('type' => 'string', 'null' => false, 'default' => 'invite', 'length' => 6),
		'roster_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'schedule_type' => array('type' => 'string', 'null' => false, 'default' => 'none', 'length' => 32),
		'games_before_repeat' => array('type' => 'integer', 'null' => true, 'default' => '4'),
		'schedule_attempts' => array('type' => 'integer', 'null' => true, 'default' => '100'),
		'display_sotg' => array('type' => 'string', 'null' => false, 'default' => 'all', 'length' => 32),
		'sotg_questions' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'numeric_sotg' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'expected_max_score' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'allstars' => array('type' => 'string', 'null' => false, 'default' => 'never', 'length' => 32),
		'exclude_teams' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'coord_list' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'capt_list' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'email_after' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'finalize_after' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'league' => array('column' => 'league_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $division_gameslot_availabilities = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'division_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'game_slot_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $divisions_days = array(
		'division_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'day_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'indexes' => array(),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $divisions_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'division_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'position' => array('type' => 'string', 'null' => true, 'default' => 'coordinator', 'length' => 64),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'division' => array('column' => 'division_id', 'unique' => 0), 'full' => array('column' => array('division_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $games = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'division_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'tournament' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'home_dependency_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'home_dependency_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'home_team' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'away_dependency_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'away_dependency_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'away_team' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'home_score' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 4),
		'away_score' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 4),
		'rating_home' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'rating_away' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'rating_points' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'approved_by' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'normal', 'length' => 32),
		'published' => array('type' => 'boolean', 'null' => true, 'default' => '1'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'game_division' => array('column' => 'division_id', 'unique' => 0), 'game_home_team' => array('column' => 'home_team', 'unique' => 0), 'game_away_team' => array('column' => 'away_team', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $leagues = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'season' => array('type' => 'string', 'null' => false, 'default' => 'None', 'length' => 16),
		'open' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'close' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'schedule_attempts' => array('type' => 'integer', 'null' => true, 'default' => '100'),
		'display_sotg' => array('type' => 'string', 'null' => false, 'default' => 'all', 'length' => 32),
		'sotg_questions' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'numeric_sotg' => array('type' => 'boolean', 'null' => true, 'default' => NULL),
		'expected_max_score' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $teams = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100, 'key' => 'index'),
		'division_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'website' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'shirt_colour' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'home_field' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'region_preference' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'open_roster' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'track_attendance' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'attendance_reminder' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_summary' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_notification' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'rating' => array('type' => 'integer', 'null' => true, 'default' => '1500'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'division' => array('column' => 'division_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
