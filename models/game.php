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

	var $hasOne = array(
		'GameSlot' => array(
			'className' => 'GameSlot',
			'foreignKey' => 'game_id',
		)
	);

	var $belongsTo = array(
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
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

	static function compareDateAndField ($a, $b) {
		// Handle both game and team event records
		if (!empty($a['GameSlot']['game_date'])) {
			$a_date = $a['GameSlot']['game_date'];
			$a_time = $a['GameSlot']['game_start'];
		} else if (!empty($a['TeamEvent']['date'])) {
			$a_date = $a['TeamEvent']['date'];
			$a_time = $a['TeamEvent']['start'];
		} else {
			$a_date = $a_time = 0;
		}

		if (!empty($b['GameSlot']['game_date'])) {
			$b_date = $b['GameSlot']['game_date'];
			$b_time = $b['GameSlot']['game_start'];
		} else if (!empty($b['TeamEvent']['date'])) {
			$b_date = $b['TeamEvent']['date'];
			$b_time = $b['TeamEvent']['start'];
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

		if ($a['GameSlot']['field_id'] < $b['GameSlot']['field_id']) {
			return -1;
		}
		return 1;
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
		$pool = array_shift($pools);

		$names = array_unique(Set::extract("/Game[pool_id=$pool]/name", array('Game' => $games)));
		usort($names, array('Game', 'compare_game_name'));
		$name = array_shift($names);
		$final = array_shift(Set::extract("/Game[name=$name]/.", array('Game' => $games)));
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

	// saveAll doesn't save GameSlot records here (the hasOne relation
	// indicates to Cake that slots are supposed to be created for games,
	// rather than being created ahead of time and assigned to games).
	// So, we replicate the important bits of saveAll here.
	function _saveGames($games, $publish) {
		// Make sure that some other coordinator hasn't scheduled a game in a
		// different league on one of the unused slots.
		$slot_ids = Set::extract ('/GameSlot/id', $games);
		$game_ids = Set::extract ('/GameSlot/game_id', $games);
		$this->GameSlot->contain();
		$taken = $this->GameSlot->find('all', array('conditions' => array(
				'id' => $slot_ids,
				'game_id !=' => null,
				// Don't include game slots that are previously allocated to one of these games;
				// of course those will be taken, but it's okay!
				'NOT' => array('game_id' => $game_ids),
		)));
		if (!empty ($taken)) {
			$this->Session->setFlash(__('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true), 'default', array('class' => 'info'));
			return false;
		}

		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$begun = $db->begin($this);
		$this->_validateForScheduleEdit();
		foreach ($games as $game) {
			$game['GameSlot']['game_id'] = $game['id'];
			$game['published'] = $publish;
			if (!$this->save($game) ||
				!$this->GameSlot->save($game['GameSlot']))
			{
				if ($begun)
					$db->rollback($this);
				$this->Session->setFlash(__('Failed to save schedule changes!', true), 'default', array('class' => 'warning'));
				return false;
			}
		}
		if ($begun)
			$db->commit($this);
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
	static function _get_spirit_entry ($game, $team_id)
	{
		if (array_key_exists ('SpiritEntry', $game) && array_key_exists ($team_id, $game['SpiritEntry'])) {
			return $game['SpiritEntry'][$team_id];
		}

		return false;
	}

	/**
	 * Compare two score entries
	 */
	static function _score_entries_agree ($one, $two)
	{
		if ($one['status'] == $two['status']) {
			if (in_array($one['status'], array('normal', 'in_progress'))) {
				return (($one['score_for'] == $two['score_against']) && ($one['score_against'] == $two['score_for']));
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
		return (!empty($test['status']) && $test['status'] != 'normal' || (isset($test['home_score']) && isset($test['away_score'])));
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
				'Attendance' => array(
					'conditions' => array_merge (array('team_id' => $team_id, 'team_event_id' => null), $conditions),
				),
				'Setting' => array(
					'conditions' => array('category' => 'personal', 'name' => 'attendance_emails'),
				),
				'conditions' => array('TeamsPerson.status' => ROSTER_APPROVED),
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender', 'Person.skill_level',
					'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
					'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
				),
			),
		));
		$attendance = $this->Attendance->Team->read(null, $team_id);

		// There may be other attendance records from people that are no longer on the roster
		$this->Attendance->contain (array(
			'Person' => array(
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender', 'Person.skill_level',
					'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
					'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
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
							'order' => 'GameSlot.game_start',
							));
				$scheduled_game_ids = array_unique (Set::extract('/Game/id', $games));

				if (count($attendance_game_ids) > count($scheduled_game_ids)) {
					// If there are more other games with attendance records than there
					// are other games scheduled, then one of those games must be this
					// game, but it was rescheduled. Figure out which one.
					// Note that this guess may not be right when a team has more than
					// one game that gets rescheduled; this will hopefully be a very
					// rare circumstance.
					foreach ($attendance_game_ids as $i) {
						if (!in_array($i, $scheduled_game_ids)) {
							$rescheduled_game_id = $i;
							break;
						}
					}
				} else {
					// Otherwise, this game is a new one. If there are other attendance
					// records, we'll copy them.
					$copy_from_game_id = array_shift ($attendance_game_ids);
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

	static function _attendanceOptions($team_id, $role, $status, $past = false, $is_captain = null) {
		if ($is_captain === null) {
			$is_captain = in_array($team_id, CakeSession::read('Zuluru.OwnedTeamIDs'));
		}
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
		if (Configure::read('feature.badges') && $this->_is_finalized($this->data)) {
			$badge_obj = AppController::_getComponent('Badge');
			if (!$badge_obj->update('game', $this->data)) {
				return false;
			}
		}
	}
}
?>
