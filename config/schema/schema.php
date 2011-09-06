<?php
class ZuluruSchema extends CakeSchema {
	var $name = 'Zuluru';

	function before($event = array()) {
		return true;
	}

	function after($event = array()) {
	}

	var $activity_logs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128, 'key' => 'index'),
		'primary_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'secondary_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'SECONDARY' => array('column' => array('type', 'primary_id'), 'unique' => 0), 'full' => array('column' => array('type', 'primary_id', 'secondary_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $allstars = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'game_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'full' => array('column' => array('game_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $answers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'question_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'answer' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'sort' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'question_id' => array('column' => 'question_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $attendances = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'game_date' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'game_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'game_id' => array('column' => 'game_id', 'unique' => 0), 'team_id' => array('column' => 'team_id', 'unique' => 0), 'person_id' => array('column' => 'person_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $countries = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $days = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 10),
		'short_name' => array('type' => 'string', 'null' => false, 'length' => 3),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $event_types = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $events = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'description' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'event_type_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'waiver_type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'cost' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax1' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'tax2' => array('type' => 'float', 'null' => true, 'default' => NULL, 'length' => '7,2'),
		'open' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'close' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'cap_male' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'cap_female' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'multiple' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'questionnaire_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'custom' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'register_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'num' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15),
		'is_open' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'rating' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'code' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 3),
		'location_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'location_province' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'latitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'longitude' => array('type' => 'float', 'null' => true, 'default' => NULL),
		'angle' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'length' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'width' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'zoom' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'parking' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'region_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'driving_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'parking_details' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'transit_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'biking_directions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'washrooms' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'public_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'site_instructions' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'sponsor' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'location_url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'layout_url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $franchises = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'website' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $franchises_teams = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'franchise_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'franchise' => array('column' => 'franchise_id', 'unique' => 0), 'team' => array('column' => 'team_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $game_slots = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'field_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'game_date' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'game_start' => array('type' => 'time', 'null' => true, 'default' => NULL),
		'game_end' => array('type' => 'time', 'null' => true, 'default' => NULL),
		'game_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $games = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'round' => array('type' => 'string', 'null' => false, 'default' => '1', 'length' => 10),
		'home_team' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
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
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'game_league' => array('column' => 'league_id', 'unique' => 0), 'game_home_team' => array('column' => 'home_team', 'unique' => 0), 'game_away_team' => array('column' => 'away_team', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $groups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $incidents = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'game_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128),
		'details' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $league_gameslot_availabilities = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'game_slot_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $leagues = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'open' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'close' => array('type' => 'date', 'null' => false, 'default' => NULL),
		'tier' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'ratio' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 8),
		'current_round' => array('type' => 'integer', 'null' => true, 'default' => '1'),
		'roster_deadline' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'roster_method' => array('type' => 'string', 'null' => false, 'default' => 'invite', 'length' => 6),
		'roster_rule' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'year' => array('type' => 'integer', 'null' => true, 'default' => NULL),
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
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $leagues_days = array(
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'day_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'indexes' => array(),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $leagues_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'league_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'position' => array('type' => 'string', 'null' => true, 'default' => 'coordinator', 'length' => 64),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'leaguemembers_league' => array('column' => 'league_id', 'unique' => 0), 'full' => array('column' => array('league_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $locks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32, 'key' => 'index'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'key' => array('column' => 'key', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'user_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'key' => 'unique'),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'first_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'last_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'publish_email' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'home_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_home_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'work_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'work_ext' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
		'publish_work_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'mobile_phone' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'publish_mobile_phone' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'addr_street' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_city' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'addr_prov' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_country' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'addr_postalcode' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 7),
		'gender' => array('type' => 'string', 'null' => false, 'default' => 'Male', 'length' => 6),
		'birthdate' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'height' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 6),
		'skill_level' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'year_started' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'shirt_size' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'session_cookie' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'status' => array('type' => 'string', 'null' => false, 'default' => 'new', 'length' => 16),
		'has_dog' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'willing_to_volunteer' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'contact_for_feedback' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'client_ip' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'complete' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'user_name', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $preregistrations = array(
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('user_id' => array('column' => array('person_id', 'event_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $provinces = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $questionnaires = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $questionnaires_questions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'questionnaire_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'question_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'sort' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'required' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'QUESTION' => array('column' => array('questionnaire_id', 'question_id'), 'unique' => 1), 'questionnaire_id' => array('column' => 'questionnaire_id', 'unique' => 0), 'question_id' => array('column' => 'question_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $questions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128),
		'question' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 20),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'anonymous' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $regions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $registration_audits = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'registration_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'response_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'iso_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'date' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'time' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'transaction_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 18),
		'approval_code' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'transaction_name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 20),
		'charge_total' => array('type' => 'float', 'null' => false, 'default' => '0.00', 'length' => '7,2'),
		'cardholder' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 40),
		'expiry' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'f4l4' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'card' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'message' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'issuer' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'issuer_invoice' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 20),
		'issuer_confirmation' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $registrations = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'payment' => array('type' => 'string', 'null' => false, 'default' => 'Unpaid', 'length' => 16),
		'notes' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'user_id' => array('column' => array('person_id', 'event_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $responses = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'event_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'registration_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'question_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'answer_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'answer' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'event_id' => array('column' => 'event_id', 'unique' => 0), 'registration_id' => array('column' => 'registration_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $score_entries = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'game_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'score_for' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'score_against' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'spirit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'defaulted' => array('type' => 'string', 'null' => false, 'default' => 'no', 'length' => 8),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'team' => array('column' => array('team_id', 'game_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $settings = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'category' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'name' => array('type' => 'string', 'null' => false, 'length' => 50),
		'value' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $spirit_entries = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'created_team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'game_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'entered_sotg' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'score_entry_penalty' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q1' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q2' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q3' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q4' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q5' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q6' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q7' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q8' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q9' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'q10' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'comments' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'team' => array('column' => array('team_id', 'game_id'), 'unique' => 0), 'created' => array('column' => array('created_team_id', 'game_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $teams = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100, 'key' => 'index'),
		'league_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
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
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'league' => array('column' => 'league_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $teams_people = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'team_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'position' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 16),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'date', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'full' => array('column' => array('team_id', 'person_id'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $uploads = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'other_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32),
		'filename' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 128),
		'approved' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'updated' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'key' => array('column' => array('other_id', 'type'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	var $waivers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'person_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16),
		'created' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'expires' => array('type' => 'timestamp', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'key' => array('column' => 'person_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
