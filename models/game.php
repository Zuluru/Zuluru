<?php
class Game extends AppModel {
	var $name = 'Game';
	var $displayField = 'id';
	var $validate = array(
		'home_score' => array(
			'range' => array(
				'rule' => array('valid_score', 0, 99),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Scores must be in the range 0-99',
				'on' => 'update',
			),
		),
		'away_score' => array(
			'range' => array(
				'rule' => array('valid_score', 0, 99),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Scores must be in the range 0-99',
				'on' => 'update',
			),
		),
		'home_carbon_flip' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'required' => false,
				'message' => 'You must select a valid carbon flip result',
				'on' => 'update',
			),
		),
		'status' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.game_status'),
				'required' => false,
				'message' => 'You must select a valid status.',
				'on' => 'update',
			),
		),
		'round' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'TODO',
			),
		),
	);

	var $belongsTo = array(
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
		),
		'GameSlot' => array(
			'className' => 'GameSlot',
			'foreignKey' => 'game_slot_id',
		),
		'Pool' => array(
			'className' => 'Pool',
			'foreignKey' => 'pool_id',
		),
		'HomeTeam' => array(
			'className' => 'Team',
			'foreignKey' => 'home_team',
		),
		'HomePoolTeam' => array(
			'className' => 'PoolsTeam',
			'foreignKey' => 'home_pool_team_id',
		),
		'AwayTeam' => array(
			'className' => 'Team',
			'foreignKey' => 'away_team',
		),
		'AwayPoolTeam' => array(
			'className' => 'PoolsTeam',
			'foreignKey' => 'away_pool_team_id',
		),
		'ApprovedBy' => array(
			'className' => 'Person',
			'foreignKey' => 'approved_by',
		)
	);

	var $hasMany = array(
		'Allstar' => array(
			'className' => 'Allstar',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
		'Attendance' => array(
			'className' => 'Attendance',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => array('team_event_id' => null),
		),
		'Incident' => array(
			'className' => 'Incident',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
		'ScoreDetail' => array(
			'className' => 'ScoreDetail',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
		'ScoreEntry' => array(
			'className' => 'ScoreEntry',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
		'SpiritEntry' => array(
			'className' => 'SpiritEntry',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
		'ScoreReminderEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => array('type' => array('email_score_reminder', 'email_approval_notice')),
		),
		'ScoreMismatchEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => array('type' => 'email_score_mismatch'),
		),
		'AttendanceReminderEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => array('type' => array('email_attendance_reminder')),
		),
		'AttendanceSummaryEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => array('type' => 'email_attendance_summary'),
		),
		'Note' => array(
			'className' => 'Note',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'order' => 'Note.created',
		),
		'Stat' => array(
			'className' => 'Stat',
			'foreignKey' => 'game_id',
			'dependent' => true,
		),
	);

	static function compareSportDateAndField ($a, $b) {
		if ($a['Division']['League']['sport'] < $b['Division']['League']['sport']) {
			return -1;
		} else if ($a['Division']['League']['sport'] > $b['Division']['League']['sport']) {
			return 1;
		}
		return Game::compareDateAndField($a, $b);
	}

	static function compareDateAndField ($a, $b) {
		// Handle game, team event and task records
		if (!empty($a['GameSlot']['game_date'])) {
			$a_date = $a['GameSlot']['game_date'];
			$a_time = $a['GameSlot']['game_start'];
		} else if (!empty($a['TeamEvent']['date'])) {
			$a_date = $a['TeamEvent']['date'];
			$a_time = $a['TeamEvent']['start'];
		} else if (!empty($a['TaskSlot']['task_date'])) {
			$a_date = $a['TaskSlot']['task_date'];
			$a_time = $a['TaskSlot']['task_start'];
		} else {
			$a_date = $a_time = 0;
		}

		if (!empty($b['GameSlot']['game_date'])) {
			$b_date = $b['GameSlot']['game_date'];
			$b_time = $b['GameSlot']['game_start'];
		} else if (!empty($b['TeamEvent']['date'])) {
			$b_date = $b['TeamEvent']['date'];
			$b_time = $b['TeamEvent']['start'];
		} else if (!empty($b['TaskSlot']['task_date'])) {
			$b_date = $b['TaskSlot']['task_date'];
			$b_time = $b['TaskSlot']['task_start'];
		} else {
			$b_date = $b_time = 0;
		}

		if ($a_date < $b_date) {
			return -1;
		} else if ($a_date > $b_date) {
			return 1;
		}

		if ($a_time < $b_time) {
			return -1;
		} else if ($a_time > $b_time) {
			return 1;
		}

		// Handle named playoff games (and team events have names too)
		if (array_key_exists ('name', $a) && !empty ($a['name'])) {
			if (strpos($a['name'], '-') !== false) {
				list($x, $a_name) = explode('-', $a['name']);
			} else {
				$a_name = $a['name'];
			}
			if (strpos($b['name'], '-') !== false) {
				list($x, $b_name) = explode('-', $b['name']);
			} else {
				$b_name = $b['name'];
			}

			if ($a_name < $b_name) {
				return -1;
			} else if ($a_name > $b_name) {
				return 1;
			}
		}

		// Handle games based on field id
		if (!empty($a['GameSlot']['field_id']) && !empty($b['GameSlot']['field_id'])) {
			if ($a['GameSlot']['field_id'] < $b['GameSlot']['field_id']) {
				return -1;
			} else {
				return 1;
			}
		}

		// Handle tasks based on task slot end time and then id
		if (!empty($a['TaskSlot']['task_end']) && !empty($b['TaskSlot']['task_end'])) {
			if ($a['TaskSlot']['task_end'] < $b['TaskSlot']['task_end']) {
				return -1;
			} else if ($a['TaskSlot']['task_end'] > $b['TaskSlot']['task_end']) {
				return 1;
			} else if ($a['TaskSlot']['id'] < $b['TaskSlot']['id']) {
				return -1;
			} else {
				return 1;
			}
		}

		// Handle other things just based on their type
		if (!empty($a['GameSlot'])) {
			return -1;
		} else if (!empty($b['GameSlot'])) {
			return 1;
		} else if (!empty($a['TeamEvent'])) {
			return -1;
		} else if (!empty($b['TeamEvent'])) {
			return 1;
		}

		// Shouldn't ever reach here, but just in case...
		return -1;
	}

	static function _readDependencies (&$record) {
		if (!empty($record['Game']['home_dependency_type'])) {
			Game::_readDependency ($record['Game'], $record['HomePoolTeam'], 'home');
		} else if (!empty($record['home_dependency_type'])) {
			Game::_readDependency ($record, $record['HomePoolTeam'], 'home');
		}

		if (!empty($record['Game']['away_dependency_type'])) {
			Game::_readDependency ($record['Game'], $record['AwayPoolTeam'], 'away');
		} else if (!empty($record['away_dependency_type'])) {
			Game::_readDependency ($record, $record['AwayPoolTeam'], 'away');
		}
	}

	static function _readDependency (&$record, $pool, $type) {
		$ths = ClassRegistry::init ('Game');
		$id = $record["{$type}_dependency_id"];
		switch ($record["{$type}_dependency_type"]) {
			case 'game_winner':
				$game = $ths->field('name', array('id' => $id));
				$dependency = sprintf (__('Winner of game %s', true), $game);
				break;

			case 'game_loser':
				$game = $ths->field('name', array('id' => $id));
				$dependency = sprintf (__('Loser of game %s', true), $game);
				break;

			case 'seed':
				$dependency = sprintf (__('%s seed', true), ordinal($id));
				break;

			case 'pool':
			case 'copy':
				$dependency = Pool::_dependency($pool);
				$alias = $pool['alias'];
				if (!empty($alias)) {
					$dependency = "$alias [$dependency]";
				}
				break;
		}

		$record["{$type}_dependency"] = $dependency;
	}

	/**
	 * We know that when we create tournament schedules, we create the most
	 * important games first. So, when generating the bracket, we start with
	 * the lowest-id game in the last round and work backwards, finding all
	 * games that it depends on. As we place games in the bracket, we remove
	 * them from the list. Repeat until there are no games left in that round,
	 * and repeat that whole process until there are no rounds left.
	 * This assumes that $games is indexed by game id.
	 */
	static function _extractBracket(&$games) {
		$bracket = array();

		// Find the "most important" remaining game to start the bracket
		// TODO: Add some kind of "bracket sort" field and use that instead
		$pools = array_unique(Set::extract('/Game/pool_id', array('Game' => $games)));
		sort($pools);
		$pool = reset($pools);
		if ($pool === null) {
			$pools = array_unique(Set::extract('/Game/tournament_pool', array('Game' => $games)));
			sort($pools);
			$pool = reset($pools);
			$pool_field = 'tournament_pool';
		} else {
			$pool_field = 'pool_id';
		}

		$names = array_unique(Set::extract("/Game[$pool_field=$pool]/name", array('Game' => $games)));
		usort($names, array('Game', 'compare_game_name'));
		$name = reset($names);
		$final = reset(Set::extract("/Game[name=$name]/.", array('Game' => $games)));
		$bracket[$final['round']] = array($final);
		unset ($games[$final['id']]);
		$round = $final['round'];

		// Work backwards through previous rounds
		while ($round > 1) {
			$round_games = array();
			$empty = true;

			foreach ($bracket[$round] as $game) {
				if (!empty($game) && in_array($game['home_dependency_type'], array('game_winner', 'game_loser'))) {
					if (array_key_exists($game['home_dependency_id'], $games)) {
						$round_games[] = $games[$game['home_dependency_id']];
						$empty = false;
						unset ($games[$game['home_dependency_id']]);
					} else {
						$round_games[] = array();
					}
				} else {
					$round_games[] = array();
				}

				if (!empty($game) && in_array($game['away_dependency_type'], array('game_winner', 'game_loser'))) {
					if (array_key_exists($game['away_dependency_id'], $games)) {
						$round_games[] = $games[$game['away_dependency_id']];
						$empty = false;
						unset ($games[$game['away_dependency_id']]);
					} else {
						$round_games[] = array();
					}
				} else {
					$round_games[] = array();
				}
			}

			if ($empty) {
				break;
			}
			$bracket[--$round] = $round_games;
		}

		return $bracket;
	}

	static function compare_game_name($a, $b) {
		// First, check pool names, if they exist
		if (strpos($a, '-') !== false) {
			list ($a_pool, $a) = explode('-', $a);
			list ($b_pool, $b) = explode('-', $b);
			if ($a_pool < $b_pool) {
				return -1;
			} else if ($a_pool > $b_pool) {
				return 1;
			}
		}

		// Strip off any "st" or "nd" or "rd" or "th".
		// Change back to strings, so that later comparisons work.
		$a_ordinal = $b_ordinal = false;
		if (intval($a) > 0) {
			$a_num = strval(intval($a));
			if ($a != $a_num) {
				$a_ordinal = true;
			}
		}
		if (intval($b) > 0) {
			$b_num = strval(intval($b));
			if ($b != $b_num) {
				$b_ordinal = true;
			}
		}

		if ($a_ordinal && $b_ordinal) {
			if ($a_num < $b_num) {
				return -1;
			} else if ($a_num > $b_num) {
				return 1;
			}
		} else if ($a_ordinal && !$b_ordinal) {
			return -1;
		} else if (!$a_ordinal && $b_ordinal) {
			return 1;
		}

		if ($a < $b) {
			return -1;
		} else if ($a > $b) {
			return 1;
		}

		return 0;
	}

	function _validateForScheduleEdit() {
		foreach (array('home_score', 'away_score', 'status') as $field) {
			unset ($this->validate[$field]);
		}
	}

	function _saveGames($games, $publish) {
		// Make sure that some other coordinator hasn't scheduled a game in a
		// different league on one of the unused slots.
		$slot_ids = Set::extract ('/game_slot_id', $games);
		$game_ids = Set::extract ('/id', $games);
		$this->contain();
		$taken = $this->find('all', array('conditions' => array(
				'game_slot_id' => $slot_ids,
				// Don't include game slots that are previously allocated to one of these games;
				// of course those will be taken, but it's okay!
				'NOT' => array('id' => $game_ids),
		)));
		if (!empty ($taken)) {
			return array('text' => __('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true), 'class' => 'info');
		}

		$this->_validateForScheduleEdit();
		foreach (array_keys($games) as $key) {
			$games[$key]['published'] = $publish;
		}
		if (!$this->saveAll($games)) {
			return array('text' => __('Failed to save schedule changes!', true), 'class' => 'warning');
		}
		return true;
	}

	function updateFieldRanking(&$game, $field = null, $home = null, $away = null) {
		$homes = Configure::read('feature.home_field');
		$facilities = Configure::read('feature.facility_preference');
		$regions = Configure::read('feature.region_preference');
		if (!$homes && !$facilities && !$regions) {
			return true;
		}

		if ($field) {
			$field_id = $field['id'];
			$facility_id = $field['facility_id'];
			$region_id = $field['Facility']['region_id'];
		} else {
			$field_id = $this->GameSlot->field('field_id', array('GameSlot.id' => $game['game_slot_id']));
			if ($facilities || $regions) {
				$facility_id = $this->GameSlot->Field->field('facility_id', array('Field.id' => $field_id));
				if ($regions) {
					$region_id = $this->GameSlot->Field->Facility->field('region_id', array('Facility.id' => $facility_id));
				}
			}
		}

		foreach (array('home', 'away') as $team) {
			$rank = null;
			if ($team == 'home') {
				$some_preference = false;
			}
			if (!empty($game["{$team}_team"])) {
				$team_id = $game["{$team}_team"];

				if ($homes) {
					if (${$team}) {
						$home_field = ${$team}['home_field'];
					} else {
						$home_field = $this->HomeTeam->field('home_field', array('HomeTeam.id' => $team_id), 'HomeTeam.id');
					}
					if ($home_field == $field_id) {
						$rank = 1;
					} else if ($home_field != null && $team == 'home') {
						$some_preference = true;
					}
				}

				if (!$rank && $facilities) {
					if (${$team}) {
						$r = Set::extract("/Facility[id=$facility_id]/TeamsFacility/rank", ${$team});
						if (!empty($r)) {
							$rank = $r[0];
						} else if ($team == 'home' && !empty(${$team}['Facility'])) {
							$some_preference = true;
						}
					} else {
						$r = $this->HomeTeam->TeamsFacility->find('first', array(
								'conditions' => array(
									'team_id' => $team_id,
									'facility_id' => $facility_id,
								),
								'fields' => array('TeamsFacility.rank'),
						));
						if ($r) {
							$rank = $r['TeamsFacility']['rank'];
						} else if ($team == 'home') {
							$count = $this->HomeTeam->TeamsFacility->find('count', array(
									'conditions' => array(
										'team_id' => $team_id,
									),
							));
							if ($count != 0) {
								$some_preference = true;
							}
						}
					}
				}

				if (!$rank && $regions) {
					if (${$team}) {
						$home_region = ${$team}['region_preference'];
					} else {
						$home_region = $this->HomeTeam->field('region_preference', array('HomeTeam.id' => $team_id), 'HomeTeam.id');
					}
					if ($home_region == $region_id) {
						if (${$team}) {
							$r = count(${$team}['Facility']);
						} else {
							$r = $this->HomeTeam->TeamsFacility->find('count', array(
									'conditions' => array(
										'team_id' => $team_id,
									),
							));
						}
						// A regional match won't count as more preferred than
						// a 2. This will give teams with regional preferences
						// a slight advantage over teams with specific field
						// preferences in terms of how often they're likely
						// to have their preferences met.
						$rank = max(2, $r + 1);
					} else if ($team == 'home' && $home_region !== null) {
						$some_preference = true;
					}
				}
			}

			if ($team == 'home' && $rank === null && $some_preference) {
				$rank = 0;
			}
			$game["{$team}_field_rank"] = $rank;
		}

		// If this is a field that the away team likes more than the home
		// team, swap the teams, so that the current home team doesn't get
		// penalized in future field selections. But only do it when we're
		// building a schedule, not when we're editing.
		if (!array_key_exists('id', $game) && $game['away_field_rank'] !== null && $some_preference &&
			$game['home_field_rank'] > $game['away_field_rank']
		)
		{
			list ($game['home_team'], $game['home_field_rank'], $game['away_team'], $game['away_field_rank']) =
				array($game['away_team'], $game['away_field_rank'], $game['home_team'], $game['home_field_rank']);
		}

		return true;
	}

	/**
	 * Adjust the indices of the ScoreEntry and SpiritEntry, so that
	 * the arrays are indexed by team_id instead of from zero.
	 *
	 */
	static function _adjustEntryIndices(&$game) {
		foreach (array('ScoreEntry' => 'team_id', 'SpiritEntry' => 'team_id', 'ScoreReminderEmail' => 'team_id') as $model => $field) {
			self::_reindexInner($game, $model, $field);
		}
	}

	/**
	 * Retrieve score entry for given team. Assumes that _adjustEntryIndices has been called.
	 *
	 * @return mixed Array with the requested score entry, or false if the team hasn't entered a final score yet.
	 */
	static function _get_score_entry ($game, $team_id)
	{
		if (array_key_exists ($team_id, $game['ScoreEntry']) && $game['ScoreEntry'][$team_id]['status'] != 'in_progress') {
			return $game['ScoreEntry'][$team_id];
		}

		return false;
	}

	/**
	 * Retrieve the best score entry for a game.
	 *
	 * @return mixed Array with the best score entry, false if neither team has entered a score yet,
	 * or null if there is no clear "best" entry.
	 */
	static function _get_best_score_entry ($game)
	{
		switch (count($game['ScoreEntry'])) {
			case 0:
				return false;

			case 1:
				return array_pop($game['ScoreEntry']);

			case 2:
				$entries = array_values($game['ScoreEntry']);
				if (Game::_score_entries_agree($entries[0], $entries[1])) {
					return $entries[0];
				} else if ($entries[0]['status'] == 'in_progress' && $entries[1]['status'] != 'in_progress') {
					return $entries[1];
				} else if ($entries[0]['status'] != 'in_progress' && $entries[1]['status'] == 'in_progress') {
					return $entries[0];
				} else if ($entries[0]['status'] == 'in_progress' && $entries[1]['status'] == 'in_progress') {
					return ($entries[0]['updated'] > $entries[1]['updated'] ? $entries[0] : $entries[1]);
				}
		}
		return null;
	}

	/**
	 * Retrieve spirit entry for given team. Assumes that _adjustEntryIndices has been called.
	 *
	 * @return mixed Array with the requested spirit entry, or false if the other team hasn't entered spirit yet.
	 */
	static function _get_spirit_entry ($game, $team_id, &$spirit_obj)
	{
		$entry = false;

		if (array_key_exists ('SpiritEntry', $game) && array_key_exists ($team_id, $game['SpiritEntry'])) {
			$entry = $game['SpiritEntry'][$team_id];
		}

		if (isset($spirit_obj) && Configure::read('scoring.spirit_default')) {
			if ($game['status'] == 'home_default') {
				if ($team_id == $game['home_team']) {
					$entry = $spirit_obj->defaulted();
				} else {
					$entry = $spirit_obj->expected();
				}
			} else if ($game['status'] == 'away_default') {
				if ($team_id == $game['home_team']) {
					$entry = $spirit_obj->expected();
				} else {
					$entry = $spirit_obj->defaulted();
				}
			}
			if ($entry == false) {
				$entry = $spirit_obj->expected();
			}
		}

		return $entry;
	}

	/**
	 * Compare two score entries
	 */
	static function _score_entries_agree ($one, $two)
	{
		if ($one['status'] == $two['status']) {
			if (in_array($one['status'], array('normal', 'in_progress'))) {
				// If carbon flips aren't enabled, both will have a score of 0 there, and they'll match anyway
				return (($one['score_for'] == $two['score_against']) && ($one['score_against'] == $two['score_for']) && ($one['home_carbon_flip'] == $two['home_carbon_flip']));
			}
			return true;
		}

		return false;
	}

	static function _is_finalized($game) {
		if (array_key_exists ('Game', $game)) {
			$test = $game['Game'];
		} else {
			$test = $game;
		}
		return (!empty($test['status']) && $test['status'] != 'normal' || isset($test['home_score']));
	}

	/**
	 * Read the attendance records for a game.
	 * This will also create any missing records, with "unknown" status.
	 *
	 * @param mixed $team The team to read attendance for.
	 * @param mixed $days The days on which the division operates.
	 * @param mixed $game_id The game id, if known.
	 * @param mixed $date The date of the game, or an array of dates.
	 * @param mixed $force If true, teams without attendance tracking will have a "default" attendance array generated; otherwise, they will get an empty array
	 * @return mixed List of attendance records.
	 *
	 */
	function _read_attendance($team, $days, $game_id, $dates = null, $force = false) {
		// We accept either a pre-read team array with roster info, or just an id
		if (!is_array($team)) {
			$team_id = $team;
			$this->Attendance->Team->contain (array(
				'Person' => array(
					'fields' => array('Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender'),
				),
			));
			$team = $this->Attendance->Team->read(null, $team_id);
			$track_attendance = $team['Team']['track_attendance'];
		} else if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
			$track_attendance = $team['track_attendance'];
		} else {
			$team_id = $team['Team']['id'];
			$track_attendance = $team['Team']['track_attendance'];
		}

		if (!$track_attendance) {
			// Teams without attendance tracking may get no data.
			// This shouldn't actually ever happen, and is really only in
			// place to help detect coding errors elsewhere.
			if (!$force) {
				return array();
			}

			// Make up data that looks like what we'd have if tracking was enabled.
			if (is_array($dates)) {
				trigger_error('Forcing attendance records for multiple dates for teams without attendance tracking enabled is not yet supported.', E_USER_ERROR);
			} else if (!$game_id) {
				trigger_error('Forcing attendance records for unscheduled games for teams without attendance tracking enabled is not yet supported.', E_USER_ERROR);
			} else {
				return $this->_forced_attendance($team, $game_id);
			}
		}

		// Make sure that all required records exist
		if (is_array($dates)) {
			foreach ($dates as $date) {
				$this->_create_attendance($team, $days, null, $date);
			}
			$conditions = array('game_date' => Game::_matchDates($dates, $days));
		} else {
			$this->_create_attendance($team, $days, $game_id, $dates);
			if ($game_id !== null) {
				$conditions = array('game_id' => $game_id);
			} else {
				$conditions = array('game_date' => Game::_matchDates($dates, $days));
			}
		}

		// Re-read whatever is current, including join tables that will be useful in the output
		$this->Attendance->Team->contain (array(
			'Person' => array(
				Configure::read('security.auth_model'),
				'Skill',
				'Attendance' => array(
					'conditions' => array_merge (array('team_id' => $team_id, 'team_event_id' => null), $conditions),
				),
				'Setting' => array(
					'conditions' => array('category' => 'personal', 'name' => 'attendance_emails'),
				),
				'conditions' => array('TeamsPerson.status' => ROSTER_APPROVED),
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender',
				),
			),
		));
		$attendance = $this->Attendance->Team->read(null, $team_id);

		// There may be other attendance records from people that are no longer on the roster
		$this->Attendance->contain (array(
			'Person' => array(
				Configure::read('security.auth_model'),				
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.gender',
				),
			),
		));
		$extra = $this->Attendance->find('all', array(
				'conditions' => array_merge($conditions, array(
					'team_id' => $team_id,
					'team_event_id' => null,
					'NOT' => array('person_id' => Set::extract('/Person/id', $attendance)),
				)),
		));

		// Mangle these records into the same format as from the read above
		$new = array();
		foreach ($extra as $person) {
			if (!array_key_exists($person['Person']['id'], $new)) {
				$new[$person['Person']['id']] = array_merge ($person['Person'], array(
					'Attendance' => array(),
					'TeamsPerson' => array('role' => 'none', 'status' => ROSTER_APPROVED),
				));
			}
			$new[$person['Person']['id']]['Attendance'][] = $person['Attendance'];
		}
		$attendance['Person'] = array_merge ($attendance['Person'], $new);

		usort ($attendance['Person'], array('Team', 'compareRoster'));
		return $attendance;
	}

	function _create_attendance($team, $days, $game_id, $date) {
		if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
		} else {
			$team_id = $team['Team']['id'];
		}

		// Find game details
		if ($game_id !== null) {
			$this->contain (array(
				'GameSlot',
			));
			$game = $this->read(null, $game_id);
			if (!$game) {
				return;
			}
			if ($game['Game']['home_team'] != $team_id && $game['Game']['away_team'] != $team_id) {
				return;
			}
			$date = $game['GameSlot']['game_date'];

			// Find all attendance records for this team for this game
			$attendance = $this->Attendance->find('all', array(
				'contain' => false,
				'conditions' => array(
						'team_id' => $team_id,
						'game_id' => $game_id,
				),
			));

			if (empty ($attendance)) {
				$match_dates = Game::_matchDates($date, $days);

				// There might be no attendance records because of a schedule change.
				// Check for other attendance records for this team on the same date.
				$attendance = $this->Attendance->find('all', array(
					'contain' => false,
					'conditions' => array(
							'team_id' => $team_id,
							'game_date' => $match_dates,
							'team_event_id' => null,
					),
				));
				$attendance_game_ids = array_unique (Set::extract('/Attendance/game_id', $attendance));

				// Check for other scheduled games including this team on the same date.
				$this->contain('GameSlot');
				$games = $this->find('all', array(
					'conditions' => array(
							'OR' => array(
								'Game.home_team' => $team_id,
								'Game.away_team' => $team_id,
							),
							'GameSlot.game_date' => $match_dates,
							'Game.id !=' => $game_id,
					),
					'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
				));
				$scheduled_game_ids = array_unique (Set::extract('/Game/id', $games));

				if (count($attendance_game_ids) > count($scheduled_game_ids)) {
					// If there are more other games with attendance records than there
					// are other games scheduled, then one of those games might be the
					// date-only placeholder game.
					if (in_array(null, $attendance_game_ids)) {
						// Find all placeholder game attendance records for this team for this date.
						$attendance = $this->Attendance->find('all', array(
							'contain' => false,
							'conditions' => array(
								'team_id' => $team_id,
								'game_date' => $match_dates,
								'game_id' => null,
								'team_event_id' => null,
							),
						));
					} else {
						// Otherwise, it must be this game, but it was rescheduled. Figure
						// out which one.
						// Note that this guess may not be right when a team has more than
						// one game that gets rescheduled; this will hopefully be a very
						// rare circumstance.
						foreach ($attendance_game_ids as $i) {
							if (!in_array($i, $scheduled_game_ids)) {
								$rescheduled_game_id = $i;
								break;
							}
						}
					}
				} else {
					// Otherwise, this game is a new one. If there are other attendance
					// records, we'll copy them.
					$copy_from_game_id = reset($attendance_game_ids);
				}
			}
		} else if ($date !== null) {
			$match_dates = Game::_matchDates($date, $days);

			$this->contain('GameSlot');
			$games = $this->find('all', array(
					'conditions' => array(
						'OR' => array(
							'Game.home_team' => $team_id,
							'Game.away_team' => $team_id,
						),
						'GameSlot.game_date' => $match_dates,
						'Game.published' => true,
					),
					'order' => 'GameSlot.game_start',
			));
			if (empty($games)) {
				// Find all game attendance records for this team for this date
				$attendance = $this->Attendance->find('all', array(
					'contain' => false,
					'conditions' => array(
						'team_id' => $team_id,
						'game_date' => $match_dates,
						'team_event_id' => null,
					),
				));
			} else {
				foreach ($games as $game) {
					$this->_create_attendance($team, $days, $game['Game']['id'], $date);
				}
				return;
			}
		} else {
			return;
		}

		// Extract list of players on the roster as of this date
		$roster = Set::extract ("/Person/TeamsPerson[created<=$date][status=" . ROSTER_APPROVED ."]/../.", $team);

		// Go through the roster and make sure there are records for all players on this date.
		$attendance_update = array();
		foreach ($roster as $person) {
			$update = false;
			$conditions = "[person_id={$person['id']}]";
			if (isset($copy_from_game_id)) {
				$conditions .= "[game_id=$copy_from_game_id]";
			} else if (isset($rescheduled_game_id)) {
				// We might need to update an existing record with a rescheduled game id.
				$conditions .= "[game_id=$rescheduled_game_id]";
			}

			$record = Set::extract ("/Attendance$conditions/.", $attendance);

			// Any record we have at this point is either something to copy from,
			// rescheduled or a new game on a date that we already had a placeholder
			// record for, or correct.
			if (!empty ($record)) {
				if (isset($copy_from_game_id)) {
					$update = $record[0];
					$update['game_id'] = $game_id;
					unset($update['id']);
				} else if ($game_id != $record[0]['game_id']) {
					$update = array(
						'id' => $record[0]['id'],
						'game_id' => $game_id,
						'game_date' => $date,
						// Preserve the last update time, don't overwrite with "now"
						'updated' => $record[0]['updated'],
					);
				}
			} else {
				// We didn't find any appropriate record, so create a new one
				$update = array(
					'team_id' => $team_id,
					'game_date' => $date,
					'game_id' => $game_id,
					'person_id' => $person['id'],
					'status' => ATTENDANCE_UNKNOWN,
				);
			}

			if ($update) {
				$attendance_update[] = $update;
			}
		}
		if (!empty ($attendance_update)) {
			$this->Attendance->saveAll($attendance_update);
		}
	}

	function _forced_attendance($team, $game_id) {
		if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
		} else {
			$team_id = $team['Team']['id'];
		}

		// Find game details
		$this->contain ();
		$game = $this->read(null, $game_id);
		if (!$game) {
			return array();
		}
		if ($game['Game']['home_team'] != $team_id && $game['Game']['away_team'] != $team_id) {
			return array();
		}

		// Go through the roster and make fake records for all players on this date.
		$player_roles = Configure::read('regular_roster_roles');
		foreach ($team['Person'] as $key => $person) {
			if ($person['TeamsPerson']['status'] == ROSTER_APPROVED) {
				if (in_array($person['TeamsPerson']['role'], $player_roles)) {
					$status = ATTENDANCE_ATTENDING;
				} else {
					$status = ATTENDANCE_UNKNOWN;
				}
				$team['Person'][$key]['Attendance'] = array(array(
					'team_id' => $team_id,
					'game_id' => $game_id,
					'person_id' => $person['id'],
					'status' => $status,
					'comment' => null,
				));
			}
		}

		usort ($team['Person'], array('Team', 'compareRoster'));
		return $team;
	}

	static function _matchDates($dates, $days) {
		if (!is_array($dates)) {
			$dates = array($dates);
		}

		$match_dates = array();
		foreach ($dates as $date) {
			$date_time = strtotime($date . ' 12:00:00');
			$date_day = date('w', $date_time) + 1;
			foreach ($days as $day) {
				$match_dates[] = date('Y-m-d', $date_time + ($day - $date_day) * DAY);
			}
		}
		return $match_dates;
	}

	static function _attendanceOptions($team_id, $role, $status, $past, $is_captain) {
		$is_regular = in_array($role, Configure::read('playing_roster_roles'));
		$options = Configure::read('attendance');

		// Only a captain can mark someone as a no show for a past game
		if (!$is_captain || !$past) {
			unset($options[ATTENDANCE_NO_SHOW]);
		}

		// Invited and available are only for subs
		if ($is_regular) {
			unset($options[ATTENDANCE_INVITED]);
			unset($options[ATTENDANCE_AVAILABLE]);
		} else if (!$is_captain) {
			// What a sub can set themselves to depends on their current status
			switch ($status) {
				case ATTENDANCE_UNKNOWN:
				case ATTENDANCE_ABSENT:
				case ATTENDANCE_AVAILABLE:
					unset($options[ATTENDANCE_ATTENDING]);
					unset($options[ATTENDANCE_INVITED]);
					break;

				case ATTENDANCE_ATTENDING:
					unset($options[ATTENDANCE_INVITED]);
					unset($options[ATTENDANCE_AVAILABLE]);
					break;

				case ATTENDANCE_INVITED:
					unset($options[ATTENDANCE_UNKNOWN]);
					unset($options[ATTENDANCE_AVAILABLE]);
					break;
			}
		}

		return $options;
	}

	static function twitterScore($team, $team_score, $opponent, $opponent_score) {
		if ($team_score >= $opponent_score) {
			return Team::twitterName($team) . ' ' . $team_score . ', ' . Team::twitterName($opponent) . ' ' . $opponent_score;
		} else {
			return Team::twitterName($opponent) . ' ' . $opponent_score . ', ' . Team::twitterName($team) . ' ' . $team_score;
		}
	}

	function affiliate($id) {
		return $this->Division->affiliate($this->field('division_id', array('Game.id' => $id)));
	}

	function afterSave() {
		if (!empty($this->data['Game']['game_slot_id'])) {
			$this->GameSlot->id = $this->data['Game']['game_slot_id'];
			if (!$this->GameSlot->saveField('assigned', true)) {
				return false;
			}
		}

		if (Configure::read('feature.badges') && $this->_is_finalized($this->data)) {
			$badge_obj = AppController::_getComponent('Badge');
			if (!$badge_obj->update('game', $this->data)) {
				return false;
			}
		}
	}

	function _validateAndSaveSchedule($data, $available_slots, $teams = null) {
		$publish = $data['Game']['publish'];
		unset ($data['Game']['publish']);
		if (array_key_exists('double_header', $data['Game'])) {
			$allow_double_header = $data['Game']['double_header'];
			unset ($data['Game']['double_header']);
		} else {
			$allow_double_header = false;
		}
		if (array_key_exists('multiple_days', $data['Game'])) {
			$allow_multiple_days = $data['Game']['multiple_days'];
			unset ($data['Game']['multiple_days']);
		} else {
			$allow_multiple_days = false;
		}
		if (array_key_exists('double_booking', $data['Game'])) {
			$allow_double_booking = $data['Game']['double_booking'];
			unset ($data['Game']['double_booking']);
		} else {
			$allow_double_booking = false;
		}
		if (array_key_exists('cross_division', $data['Game'])) {
			$allow_cross_division = $data['Game']['cross_division'];
			unset ($data['Game']['cross_division']);
		} else {
			$allow_cross_division = false;
		}

		// TODO: Remove workaround for Set::extract bug
		$data['Game'] = array_values($data['Game']);
		$used_slots = Set::extract ('/Game/game_slot_id', $data);
		if (in_array ('', $used_slots)) {
			return array('text' => __('You cannot choose the "---" as the game time/place!', true), 'class' => 'info');
		}

		if (!$allow_double_booking) {
			$slot_counts = array_count_values ($used_slots);
			foreach ($slot_counts as $slot_id => $count) {
				if ($count > 1) {
					$this->GameSlot->contain(array(
							'Field' => 'Facility',
					));
					$slot = $this->GameSlot->read(null, $slot_id);
					$slot_field = $slot['Field']['long_name'];
					$slot_time = "{$slot['GameSlot']['game_date']} {$slot['GameSlot']['game_start']}";
					return array('text' => sprintf (__('Game slot at %s on %s was selected more than once!', true), $slot_field, $slot_time), 'class' => 'info');
				}
			}
		}

		$team_ids = array_merge (
				Set::extract ('/Game/home_team', $data),
				Set::extract ('/Game/away_team', $data)
		);
		if (!empty($team_ids)) {
			if (in_array ('', $team_ids)) {
				return array('text' => __('You cannot choose the "---" as the team!', true), 'class' => 'info');
			}

			$team_names = $this->Division->Team->find('list', array(
					'contain' => false,
					'conditions' => array('Team.id' => $team_ids),
			));

			$team_counts = array_count_values ($team_ids);
			foreach ($team_counts as $team_id => $count) {
				if ($count > 1) {
					if ($allow_double_header || $allow_multiple_days) {
						// Check that the double-header doesn't cause conflicts; must be at the same facility, but different times
						$team_slot_ids = array_merge(
							Set::extract ("/Game[home_team=$team_id]/game_slot_id", $data),
							Set::extract ("/Game[away_team=$team_id]/game_slot_id", $data)
						);
						if (count ($team_slot_ids) != count (array_unique ($team_slot_ids))) {
							return array('text' => sprintf (__('Team %s was scheduled twice in the same time slot!', true), $team_names[$team_id]), 'class' => 'info');
						}

						$this->GameSlot->contain(array(
								'Field',
						));
						$team_slots = $this->GameSlot->find('all', array('conditions' => array(
								'GameSlot.id' => $team_slot_ids,
						)));
						foreach ($team_slots as $key1 => $slot1) {
							foreach ($team_slots as $key2 => $slot2) {
								if ($key1 != $key2) {
									if (!$allow_double_header && $slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date']) {
										return array('text' => sprintf (__('Team %s was scheduled twice on the same day!', true), $team_names[$team_id]), 'class' => 'info');
									}
									if (!$allow_multiple_days && $slot1['GameSlot']['game_date'] != $slot2['GameSlot']['game_date']) {
										return array('text' => sprintf (__('Team %s was scheduled on different days!', true), $team_names[$team_id]), 'class' => 'info');
									}
									if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] &&
										$slot1['GameSlot']['game_start'] >= $slot2['GameSlot']['game_start'] &&
										$slot1['GameSlot']['game_start'] < $slot2['GameSlot']['display_game_end'])
									{
										return array('text' => sprintf (__('Team %s was scheduled in overlapping time slots!', true), $team_names[$team_id]), 'class' => 'info');
									}
									if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] && $slot1['Field']['facility_id'] != $slot2['Field']['facility_id']) {
										return array('text' => sprintf (__('Team %s was scheduled on %s at different facilities!', true), $team_names[$team_id], Configure::read('ui.fields')), 'class' => 'info');
									}
								}
							}
						}
					} else {
						return array('text' => sprintf (__('Team %s was selected more than once!', true), $team_names[$team_id]), 'class' => 'info');
					}
				}
			}

			$team_divisions = $this->Division->Team->find('list', array(
					'contain' => array(),
					'fields' => array('Team.id', 'Team.division_id'),
					'conditions' => array('Team.id' => $team_ids),
			));
		}

		$seeds = array_merge (
				Set::extract ('/Game/home_pool_team_id', $data),
				Set::extract ('/Game/away_pool_team_id', $data)
		);
		if (!empty($seeds)) {
			if (in_array ('', $seeds)) {
				return array('text' => __('You cannot choose the "---" as the seed!', true), 'class' => 'info');
			}

			$seed_names = $this->Division->Game->Pool->PoolsTeam->find('list', array(
					'contain' => false,
					'conditions' => array('PoolsTeam.id' => $seeds),
			));

			$seed_counts = array_count_values ($seeds);
			foreach ($seed_counts as $seed_id => $count) {
				if ($count > 1) {
					// Check that the double-header doesn't cause conflicts; must be at the same facility, but different times
					$seed_slot_ids = array_merge(
						Set::extract ("/Game[home_pool_team_id=$seed_id]/game_slot_id", $data),
						Set::extract ("/Game[away_pool_team_id=$seed_id]/game_slot_id", $data)
					);
					if (count ($seed_slot_ids) != count (array_unique ($seed_slot_ids))) {
						return array('text' => sprintf (__('Seed %s was scheduled twice in the same time slot!', true), $seed_names[$seed_id]), 'class' => 'info');
					}

					$this->GameSlot->contain(array(
							'Field',
					));
					$seed_slots = $this->GameSlot->find('all', array('conditions' => array(
							'GameSlot.id' => $seed_slot_ids,
					)));
					foreach ($seed_slots as $key1 => $slot1) {
						foreach ($seed_slots as $key2 => $slot2) {
							if ($key1 != $key2) {
								if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] &&
									$slot1['GameSlot']['game_start'] >= $slot2['GameSlot']['game_start'] &&
									$slot1['GameSlot']['game_start'] < $slot2['GameSlot']['display_game_end'])
								{
									return array('text' => sprintf (__('Seed %s was scheduled in overlapping time slots!', true), $seed_names[$seed_id]), 'class' => 'info');
								}
								if ($slot1['GameSlot']['game_date'] == $slot2['GameSlot']['game_date'] && $slot1['Field']['facility_id'] != $slot2['Field']['facility_id']) {
									return array('text' => sprintf (__('Seed %s was scheduled on %s at different facilities!', true), $seed_names[$seed_id], Configure::read('ui.fields')), 'class' => 'info');
								}
							}
						}
					}
				}
			}

			$seed_divisions = $this->Division->Game->Pool->PoolsTeam->find('list', array(
					'contain' => array(),
					'joins' => array(
						array(
							'table' => "{$this->tablePrefix}pools",
							'alias' => 'Pool',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'PoolsTeam.pool_id = Pool.id',
						),
					),
					'fields' => array('PoolsTeam.id', 'Pool.division_id'),
					'conditions' => array('PoolsTeam.id' => $seeds),
			));
		}

		$no_dependencies = array_merge (
				Set::extract ('/Game[home_dependency_type=]', $data),
				Set::extract ('/Game[away_dependency_type=]', $data)
		);
		if (!empty($no_dependencies)) {
			return array('text' => __('You cannot choose the "---" as the dependency type!', true), 'class' => 'info');
		}

		$winners = array_merge (
				Set::extract ('/Game[home_dependency_type=game_winner]/home_dependency_id', $data),
				Set::extract ('/Game[away_dependency_type=game_winner]/away_dependency_id', $data)
		);
		if (!empty($winners)) {
			if (in_array ('', $winners)) {
				return array('text' => __('You cannot choose the "---" as the game dependency!', true), 'class' => 'info');
			}

			$game_names = $this->Division->Game->find('list', array(
					'contain' => false,
					'fields' => array('Game.id', 'Game.name'),
					'conditions' => array('Game.id' => $winners),
			));

			$winner_counts = array_count_values ($winners);
			foreach ($winner_counts as $winner_id => $count) {
				if ($count > 1) {
					return array('text' => sprintf (__('Winner of game %s was selected more than once!', true), $game_names[$winner_id]), 'class' => 'info');
				}
			}
		}

		$losers = array_merge (
				Set::extract ('/Game[home_dependency_type=game_loser]/home_dependency_id', $data),
				Set::extract ('/Game[away_dependency_type=game_loser]/away_dependency_id', $data)
		);
		if (!empty($losers)) {
			if (in_array ('', $losers)) {
				return array('text' => __('You cannot choose the "---" as the game dependency!', true), 'class' => 'info');
			}

			$game_names = $this->Division->Game->find('list', array(
					'contain' => false,
					'fields' => array('Game.id', 'Game.name'),
					'conditions' => array('Game.id' => $losers),
			));

			$loser_counts = array_count_values ($losers);
			foreach ($loser_counts as $loser_id => $count) {
				if ($count > 1) {
					return array('text' => sprintf (__('Loser of game %s was selected more than once!', true), $game_names[$loser_id]), 'class' => 'info');
				}
			}
		}

		if (!empty($winners) || !empty($losers)) {
			$game_divisions = $this->Division->Game->find('list', array(
					'contain' => array(),
					'fields' => array('Game.id', 'Game.division_id'),
					'conditions' => array('Game.id' => array_merge($winners, $losers)),
			));
		}

		if ($teams) {
			$this->_reindexOuter($teams, 'Team', 'id');
		}

		foreach ($data['Game'] as $key => $game) {
			if (array_key_exists('home_team', $game)) {
				$home_division = $team_divisions[$game['home_team']];
				$home_name = $team_names[$game['home_team']];
			} else if (array_key_exists('home_pool_team_id', $game)) {
				$home_division = $seed_divisions[$game['home_pool_team_id']];
				$home_name = $seed_names[$game['home_pool_team_id']];
			} else if (array_key_exists('home_dependency_id', $game)) {
				$home_division = $game_divisions[$game['home_dependency_id']];
				$home_name = $game['home_dependency_type'] . ' ' . $game_names[$game['home_dependency_id']];
			}

			if (array_key_exists('away_team', $game)) {
				$away_division = $team_divisions[$game['away_team']];
				$away_name = $team_names[$game['away_team']];
			} else if (array_key_exists('away_pool_team_id', $game)) {
				$away_division = $seed_divisions[$game['away_pool_team_id']];
				$away_name = $seed_names[$game['away_pool_team_id']];
			} else if (array_key_exists('away_dependency_id', $game)) {
				$away_division = $game_divisions[$game['away_dependency_id']];
				$away_name = $game['away_dependency_type'] . ' ' . $game_names[$game['away_dependency_id']];
			}

			if ($home_division != $away_division && !$allow_cross_division) {
				return array('text' => sprintf(__('You have scheduled teams from different divisions against each other (%s vs %s), but not checked the box allowing cross-division games.', true), $team_names[$game['home_team']], $team_names[$game['away_team']]), 'class' => 'info');
			} else {
				// Make sure that the game slot selected is available to one of the teams
				$available_to_home = Set::extract("/GameSlot[id={$game['game_slot_id']}]/..", $available_slots[$home_division]);
				if (empty($available_to_home)) {
					$available_to_away = Set::extract("/GameSlot[id={$game['game_slot_id']}]/..", $available_slots[$away_division]);
					if (empty($available_to_away)) {
						return array('text' => sprintf(__('You have scheduled a game between %s and %s in a game slot not available to them.', true), $home_name, $away_name), 'class' => 'info');
					} else {
						// Game is happening on a field only available to the away team, so make them the home team instead
						$data['Game'][$key]['division_id'] = $away_division;
						list($data['Game'][$key]['home_team'], $data['Game'][$key]['away_team']) = array($game['away_team'], $game['home_team']);
						$field = $available_to_away[0]['Field'];
					}
				} else {
					$field = $available_to_home[0]['Field'];
				}
				// At this point, we know that the home team has access to the game slot,
				// so we will make the division id of the game match that team
				$data['Game'][$key]['division_id'] = $home_division;
			}

			// Check for a dependency that has already been resolved
			foreach (array('home', 'away') as $team) {
				if (!empty($game["{$team}_dependency_type"])) {
					switch ($game["{$team}_dependency_type"]) {
						case 'game_winner':
							$this->contain();
							$result = $this->read(null, $game["{$team}_dependency_id"]);
							if (!empty($result['Game']['home_score'])) {
								if ($result['Game']['home_score'] >= $result['Game']['away_score']) {
									$data['Game'][$key]["{$team}_team"] = $result['Game']['home_team'];
								} else {
									$data['Game'][$key]["{$team}_team"] = $result['Game']['away_team'];
								}
							} else {
								$data['Game'][$key]["{$team}_team"] = null;
							}
							break;

						case 'game_loser':
							$this->contain();
							$result = $this->read(null, $game["{$team}_dependency_id"]);
							if (!empty($result['Game']['home_score'])) {
								if ($result['Game']['home_score'] >= $result['Game']['away_score']) {
									$data['Game'][$key]["{$team}_team"] = $result['Game']['away_team'];
								} else {
									$data['Game'][$key]["{$team}_team"] = $result['Game']['home_team'];
								}
							} else {
								$data['Game'][$key]["{$team}_team"] = null;
							}
							break;
					}
				}
			}

			$home = ($teams && !empty($data['Game'][$key]['home_team']) ? $teams[$data['Game'][$key]['home_team']] : null);
			$away = ($teams && !empty($data['Game'][$key]['away_team']) ? $teams[$data['Game'][$key]['away_team']] : null);
			if (!$this->updateFieldRanking($data['Game'][$key], $field, $home, $away)) {
				return array('text' => __('Failed to update field preference stats!', true), 'class' => 'warning');
			}
		}

		$ret = $this->_saveGames($data['Game'], $publish);
		if ($ret !== true) {
			return $ret;
		}

		$available_slot_ids = array();
		foreach ($available_slots as $slots) {
			$available_slot_ids = array_merge($available_slot_ids, Set::extract ('/Game/game_slot_id', $slots));
		}
		$unused_slots = array_diff ($available_slot_ids, $used_slots);
		if (empty($unused_slots) || $this->GameSlot->updateAll (array('assigned' => 0), array('GameSlot.id' => $unused_slots))) {
			return true;
		} else {
			return array('text' => __('Saved schedule changes, but failed to clear unused slots!', true), 'class' => 'warning', 'result' => true);
		}
	}
}
?>
