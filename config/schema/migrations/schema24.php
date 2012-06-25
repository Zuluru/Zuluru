<?php
class Zuluru24Schema extends CakeSchema {
	var $name = 'Zuluru24';

	function after($event = array()) {
		$this->Team = ClassRegistry::init('Team');
		$this->Team->contain(array());
		$teams = $this->Team->find('list', array('fields' => 'rating'));

		// Bare-bones models have already been created for the database introspection process.
		// Use of an alias here gets us the full model, with the required relations.
		$this->Game = ClassRegistry::init(array(array('class' => 'Game', 'alias' => 'GameAlias')));
		foreach (array_keys($teams) as $team_id) {
			$home_game = $this->Game->find('first', array(
					'conditions' => array('home_team' => $team_id),
					'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
					'contain' => 'GameSlot',
			));
			$away_game = $this->Game->find('first', array(
					'conditions' => array('away_team' => $team_id),
					'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
					'contain' => 'GameSlot',
			));
			if ($home_game === false) {
				$use_game = $away_game;
			} else if ($away_game === false) {
				$use_game = $home_game;
			} else if ("{$home_game['GameSlot']['game_date']} {$home_game['GameSlot']['game_start']}" < "{$away_game['GameSlot']['game_date']} {$away_game['GameSlot']['game_start']}") {
				$use_game = $home_game;
				$field = 'rating_home';
			} else {
				$use_game = $away_game;
				$field = 'rating_away';
			}

			// If the game doesn't have a rating, it might just be a very old game, from
			// before rating_home and rating_away fields were in use. Check all games this
			// team was in and wind back the ratings to find where they started.
			if ($use_game === false || $use_game['GameAlias'][$field] === null) {
				// Start with their current rating
				$rating = $teams[$team_id];
				$games = $this->Game->find('all', array(
						'conditions' => array('OR' => array(
								'home_team' => $team_id,
								'away_team' => $team_id,
						)),
						'contain' => array(),
				));
				foreach ($games as $game) {
					if (($game['GameAlias']['home_team'] == $team_id && $game['GameAlias']['home_score'] >= $game['GameAlias']['away_score']) ||
						($game['GameAlias']['away_team'] == $team_id && $game['GameAlias']['home_score'] < $game['GameAlias']['away_score']))
					{
						$rating -= $game['GameAlias']['rating_points'];
					} else {
						$rating += $game['GameAlias']['rating_points'];
					}
				}
			} else {
				$rating = $use_game['GameAlias'][$field];
			}

			$this->Team->updateAll(array('initial_rating' => $rating), array('id' => $team_id));
		}

		return array('teams' => __('updated.', true));
	}

	var $teams = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 100, 'key' => 'index'),
		'division_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
		'website' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100),
		'shirt_colour' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50),
		'home_field' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'region_preference' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'open_roster' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'track_attendance' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'attendance_reminder' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_summary' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'attendance_notification' => array('type' => 'integer', 'null' => true, 'default' => '-1'),
		'initial_rating' => array('type' => 'integer', 'null' => true, 'default' => '1500'),
		'rating' => array('type' => 'integer', 'null' => true, 'default' => '1500'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'division' => array('column' => 'division_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
?>
