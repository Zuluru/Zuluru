<?php
class GamesController extends AppController {

	var $name = 'Games';
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'ratings_table',
		)))
		{
			return true;
		}

		// People can perform these operations on teams they are on
		if (in_array ($this->params['action'], array(
				'attendance',
		)))
		{
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.TeamIDs'))) {
				return true;
			}
		}

		// Captains are permitted to perform these operations for their teams
		if (in_array ($this->params['action'], array(
				'submit_score',
		)))
		{
			// If a team id is specified, check if it belongs to the logged-in user
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				return true;
			}
		}

		// Permit coordinators to perform these operations on their games
		if (in_array ($this->params['action'], array(
				'edit',
		)))
		{
			$game = $this->_arg('game');
			if ($game) {
				$leagues = $this->Session->read('Zuluru.LeagueIDs');
				if (!empty ($leagues)) {
					$coord = $this->Game->find ('count', array(
							'conditions' => array(
								'Game.id'			=> $game,
								'Game.league_id'	=> $leagues,
							)
					));
					if ($coord > 0) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function view() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(__('Invalid game', true));
			$this->redirect('/');
		}

		$this->Game->contain (array (
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'GameSlot' => array('Field' => array('ParentField')),
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		));
		$game = $this->Game->read(null, $id);
		$this->Game->_adjustEntryIndices($game);
		$this->set('game', $game);
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $this->Game->data['League']['sotg_questions'], $this));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->Game->data['League']['schedule_type'], $this));
		$this->set('is_coordinator', in_array ($this->Game->data['League']['id'], $this->Session->read('Zuluru.LeagueIDs')));
	}

	function ratings_table() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(__('Invalid game', true));
			$this->redirect('/');
		}

		if (!empty ($this->data)) {
			$this->set('rating_home', $this->data['Game']['rating_home']);
			$this->set('rating_away', $this->data['Game']['rating_away']);
		}

		$this->Game->contain (array (
			'League',
			'HomeTeam',
			'AwayTeam',
		));
		$this->set('game', $this->Game->read(null, $id));
		$this->set('league_obj', $this->_getComponent ('LeagueType', $this->Game->data['League']['schedule_type'], $this));
		$this->set('max_score', $this->Game->data['League']['expected_max_score']);
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	function ical($game_id, $team_id) {
		$this->layout = 'ical';
		if (!$game_id || !$team_id) {
			return;
		}

		$this->Game->contain (array (
			'HomeTeam',
			'AwayTeam',
			'GameSlot' => array('Field' => array('ParentField')),
		));
		$game = $this->Game->read(null, $game_id);
		if ($game === false || !$game['Game']['published'] ||
			($team_id != $game['Game']['home_team'] && $team_id != $game['Game']['away_team']))
		{
			return;
		}

		$this->set ('calendar_type', 'Game');
		$this->set ('calendar_name', 'Game');
		$this->set(compact ('game', 'team_id'));

		Configure::write ('debug', 0);
	}

	function edit() {
		$id = $this->_arg('game');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid game', true));
			$this->redirect('/');
		}

		// We need some basic game information right off. Much of the
		// data we display here doesn't come from the form, so we have
		// to read the whole thing.
		$this->Game->contain (array (
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'GameSlot' => array('Field' => array('ParentField')),
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('extended_playing_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('extended_playing_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'gender'),
				),
			),
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		));
		$game = $this->Game->read(null, $id);
		$this->Game->_adjustEntryIndices($game);

		if (!$this->is_admin && !in_array ($game['League']['id'], $this->Session->read('Zuluru.LeagueIDs'))) {
			$this->Session->setFlash(__('You do not have permission to edit that game.', true));
			$this->redirect('/');
		}

		// Spirit score entry validation comes from the spirit component
		$spirit_obj = $this->_getComponent ('Spirit', $game['League']['sotg_questions'], $this);
		$league_obj = $this->_getComponent ('LeagueType', $game['League']['schedule_type'], $this);
		$this->Game->SpiritEntry->validate = $spirit_obj->getValidate($game['League']);

		if (!empty($this->data)) {
			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			$this->data['Game']['id'] = $id;
			$this->data['Game']['approved_by'] = $this->Auth->user('id');
			$this->data['SpiritEntry'][$game['Game']['home_team']]['team_id'] = $game['Game']['home_team'];
			$this->data['SpiritEntry'][$game['Game']['home_team']]['created_team_id'] = $game['Game']['away_team'];
			$this->data['SpiritEntry'][$game['Game']['away_team']]['team_id'] = $game['Game']['away_team'];
			$this->data['SpiritEntry'][$game['Game']['away_team']]['created_team_id'] = $game['Game']['home_team'];

			// We need to merge the two allstar nomination areas
			$allstars = array();
			if (array_key_exists ('Allstar', $this->data)) {
				foreach ($this->data['Allstar'] as $team_allstars) {
					foreach ($team_allstars['person_id'] as $allstar) {
						$allstars[] = array('person_id' => $allstar);
					}
				}
				$this->data['Allstar'] = $allstars;
			}

			$this->_adjustScoreAndRatings($game, $this->data);

			// Wrap the whole thing in a transaction, for safety.
			$db =& ConnectionManager::getDataSource($this->Game->useDbConfig);
			$db->begin($this->Game);

			if ($this->Game->Allstar->deleteAll(array('game_id' => $id))) {
				if ($this->Game->saveAll($this->data, array('validate' => 'first'))) {
					$this->Session->setFlash(__('The game has been saved', true));
					// Delete score entries
					$this->Game->ScoreEntry->deleteAll(array('game_id' => $id));
					$db->commit($this->Game);
					$this->redirect(array('action' => 'view', 'game' => $id));
				} else {
					$this->Session->setFlash(__('The game could not be saved. Please, try again.', true));
					// Save the validation errors, as they get reset by the read() below
					$validationErrors = $this->Game->validationErrors;
				}
			}

			// If we get here, something failed.
			$db->rollback($this->Game);
		}

		if (empty($this->data)) {
			$this->data = $game;
		} else {
			// If we have data, and we haven't redirected, it's because there was an error in the data
			$this->Game->validationErrors = $validationErrors;
		}

		// To maximize shared code between the edit and view templates, we'll
		// set it in the 'game' variable here too.
		$this->set(compact (array ('game', 'spirit_obj', 'league_obj')));
		$this->set('is_coordinator', in_array ($game['League']['id'], $this->Session->read('Zuluru.LeagueIDs')));
	}

	function delete() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for game', true));
			$this->redirect('/');
		}

		$this->Game->contain (array (
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'GameSlot' => array('Field' => array('ParentField')),
			'HomeTeam',
			'AwayTeam',
			'ApprovedBy',
			'ScoreEntry' => array('Person'),
			'SpiritEntry',
			'Allstar' => array('Person'),
			'Incident',
		));
		$game = $this->Game->read(null, $id);
		if (!$game) {
			$this->Session->setFlash(__('Invalid id for game', true));
			$this->redirect('/');
		}

		if (Game::_is_finalized($game)) {
			$this->Session->setFlash(__('The score for that game has already been finalized.', true));
			$this->redirect(array('action' => 'view', 'game' => $game['Game']['id']));
		}
		if (!empty ($game['ScoreEntry'])) {
			$this->Session->setFlash(__('A score has already been submitted for that game.', true));
			$this->redirect(array('action' => 'view', 'game' => $game['Game']['id']));
		}

		// If the game isn't finalized, and there's no score entry, then there won't
		// be any other related records either, and it's safe to delete it.
		// Wrap the whole thing in a transaction, for safety.
		$db =& ConnectionManager::getDataSource($this->Game->useDbConfig);
		$db->begin($this->Game);
		if ($this->Game->delete($id)) {
			if ($this->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $game['GameSlot']['id']))) {
				$this->Session->setFlash(__('Game deleted', true));
				$db->commit($this->Game);
				$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $game['League']['id']));
			} else {
				$this->Session->setFlash(__('Game was deleted, but game slot was not cleared', true));
			}
		} else {
			$this->Session->setFlash(__('Game was not deleted', true));
		}
		$db->rollback($this->Game);
		$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $game['League']['id']));
	}

	function attendance() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for game', true));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect('/');
		}

		$this->Game->contain(array(
			// Get the list of captains for each team, for the team pop-up
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'GameSlot',
		));
		$game = $this->Game->read(null, $id);
		if ($game['Game']['home_team'] == $team_id) {
			$team = $game['HomeTeam'];
			$opponent = $game['AwayTeam'];
		} else if ($game['Game']['away_team'] == $team_id) {
			$team = $game['AwayTeam'];
			$opponent = $game['HomeTeam'];
		} else {
			$this->Session->setFlash(__('That team is not playing in this game.', true));
			$this->redirect('/');
		}

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true));
			$this->redirect('/');
		}

		$attendance = $this->Game->_read_attendance($team_id, $id);
		$this->set(compact('game', 'team', 'opponent', 'attendance'));
		$this->set('is_captain', in_array($team_id, $this->Session->read('Zuluru.OwnedTeamIDs')));
	}

	function attendance_change() {
		$id = $this->_arg('game');
		$date = $this->_arg('date');
		if (!$id && !$date) {
			$this->Session->setFlash(__('Invalid id for game', true));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect('/');
		}

		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(__('Invalid id for player', true));
				$this->redirect('/');
			}
		}

		if ($id) {
			$this->Game->contain(array(
				// Get the list of captains for each team, for the team pop-up
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
						'fields' => array('id', 'first_name', 'last_name', 'email'),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
						'fields' => array('id', 'first_name', 'last_name', 'email'),
					),
				),
				'GameSlot',
				// We need to specify the team id here, in case the person is on both teams in this game
				'Attendance' => array(
					'conditions' => array(
						'team_id' => $team_id,
						'person_id' => $person_id,
					),
					'Person' => array(
						'Team' => array(
							'conditions' => array('team_id' => $team_id),
						),
					),
				),
			));
			$game = $this->Game->read(null, $id);
			$date = $game['GameSlot']['game_date'];
			$past = ("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}" < date('Y-m-d H:i:s'));

			if ($game['Game']['home_team'] == $team_id) {
				$team = $game['HomeTeam'];
				$opponent = $game['AwayTeam'];
			} else if ($game['Game']['away_team'] == $team_id) {
				$team = $game['AwayTeam'];
				$opponent = $game['HomeTeam'];
			} else {
				$this->Session->setFlash(__('That team is not playing in this game.', true));
				$this->redirect('/');
			}

			// Pull out the player and attendance records.
			$attendance = $game['Attendance'][0];
			$person = $attendance['Person'];
		} else {
			$this->Game->Attendance->contain(array(
				'Person' => array(
					'Team' => array(
						'conditions' => array('team_id' => $team_id),
					),
				),
				'Team',
			));
			$record = $this->Game->Attendance->find('first', array(
					'conditions' => array(
						'person_id' => $person_id,
						'team_id' => $team_id,
						'game_date' => $date,
					),
			));

			// Pull out the player, attendance and team records.
			$attendance = $record['Attendance'];
			$person = $record['Person'];
			$team = $record['Team'];
			$past = false;
		}

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true));
			$this->redirect('/');
		}

		if (!$attendance) {
			$this->Session->setFlash(__('That person does not have an attendance record for this game.', true));
			$this->redirect('/');
		}

		$is_me = ($person_id == $this->Auth->user('id'));
		$is_captain = in_array ($team_id, $this->Session->read('Zuluru.OwnedTeamIDs'));

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$player_hash = $this->_hash($attendance);
			$captain_hash = $this->_hash(array_merge ($attendance, array('captain' => true)));
			if ($player_hash == $code) {
				// Only the player will have this confirmation code
				$is_me = true;
			} else if ($captain_hash == $code) {
				$is_captain = true;
			} else {
				$this->Session->setFlash(__('The authorization code is invalid.', true));
				$this->redirect('/');
			}

			// Fake the posted data array with the status from the URL
			$this->data = array('Person' => array('status' => $this->_arg('status')));
		} else {
			// Players can change their own attendance, captains can change any attendance on their teams
			if (!$is_me && !$is_captain) {
				$this->Session->setFlash(__('You are not allowed to change this attendance record.', true));
				$this->redirect('/');
			}
		}

		$position = $person['Team'][0]['TeamsPerson']['position'];
		$attendance_options = $this->Game->_attendanceOptions ($team_id, $position, $attendance['status'], $past, $is_captain);

		if (!empty ($this->data)) {
			$status = $this->data['Person']['status'];
			if (!array_key_exists ($status, $attendance_options)) {
				$this->Session->setFlash(__('That is not currently a valid attendance status for this player for this game.', true));
				if ($code) {
					$this->redirect('/');
				}
			} else {
				$this->Game->Attendance->id = $attendance['id'];
				if ($this->Game->Attendance->saveField ('status', $status)) {
					$this->Session->setFlash(sprintf (__('Attendance has been updated to %s.', true), $attendance_options[$status]));

					// Maybe send some emails, only if the game is in the future and the status changed
					if (!$past && $status != $attendance['status']) {
						$this->set(compact('game', 'date', 'team', 'opponent', 'person', 'status'));

						// Send email from the player to the captain if it's within the configured date range
						// This "days" calculation isn't precise, as it doesn't handle leap years.
						// However, it's close enough since we're never looking at periods that span
						// from a year end to a leap day.
						$days = date('Y') * 365 + date('z');
						$days_to_game = date('Y', strtotime($date)) * 365 + date('z', strtotime($date)) - $days;
						if ($is_me && $team['attendance_notification'] >= $days_to_game) {
							// Make sure the current player isn't in the list of captains to send to
							$captains = Set::extract ("/Person[id!={$person['id']}]", $team);
							if (!empty ($captains)) {
								$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));
								$this->set('code', $this->_hash(array_merge ($attendance, array('captain' => true))));
								$this->_sendMail (array (
										'to' => $captains,
										'replyTo' => $person,
										'subject' => "{$team['name']} attendance change",
										'template' => 'attendance_captain_notification',
										'sendAs' => 'both',
								));
							}
						}
						// Always send an email from the captain to substitute players. It will likely
						// be an invitation to play or a response to a request or cancelling attendance
						// if another player is available. Regardless, we need to communicate this.
						else if ($is_captain && !in_array($position, Configure::read('playing_roster_positions'))) {
							$captain = $this->Session->read('Zuluru.Person.full_name');
							if (!$captain) {
								$captain = __('A captain', true);
							}
							$this->set(compact('captain'));
							$this->set('player_options',
								$this->Game->_attendanceOptions ($team_id, $position, $status, $past, false));
							$this->set('code', $this->_hash ($attendance));

							$this->_sendMail (array (
									'to' => $person,
									'replyTo' => $this->Session->read('Zuluru.Person'),
									'subject' => "{$team['name']} attendance change for game on $date",
									'template' => 'attendance_substitute_notification',
									'sendAs' => 'both',
							));
						}
					}

					// Where do we go from here? It depends...
					if ($this->RequestHandler->isAjax()) {
						$this->action = 'attendance_change_ajax';
						$this->set(compact('is_captain', 'status'));
					} else if (!$this->is_logged_in) {
						$this->redirect(array('controller' => 'teams', 'action' => 'view', 'team' => $team_id));
					} else if ($id) {
						$this->redirect(array('action' => 'attendance', 'team' => $team_id, 'game' => $id));
					} else {
						$this->redirect(array('controller' => 'teams', 'action' => 'attendance', 'team' => $team_id));
					}
				} else {
					$this->Session->setFlash(__('Failed to update the attendance status!', true));
				}
			}
		}

		$this->set(compact('game', 'date', 'team', 'opponent', 'person', 'attendance', 'attendance_options'));
	}

	function submit_score() {
		$id = $this->_arg('game');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for game', true));
			$this->redirect('/');
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect('/');
		}

		$contain = array (
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'GameSlot' => array('Field' => array('ParentField')),
			'ScoreEntry' => array('Person' => array('fields' => array('id', 'first_name', 'last_name'))),
			'SpiritEntry',
			'Incident',
		);
		if (Configure::read('scoring.allstars')) {
			// We need roster details for potential allstar nominations.
			$contain = array_merge($contain, array(
				'HomeTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('extended_playing_roster_positions')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
							'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
							'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
						),
					),
				),
				'AwayTeam' => array(
					'Person' => array(
						'conditions' => array('TeamsPerson.position' => Configure::read('extended_playing_roster_positions')),
						'fields' => array(
							'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.gender',
							'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
							'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
						),
					),
				),
				'Allstar' => array('Person'),
			));
		}

		$this->Game->contain ($contain);
		$game = $this->Game->read(null, $id);
		$this->Game->_adjustEntryIndices($game);
		if ($game['Game']['home_team'] == $team_id) {
			$opponent = $game['AwayTeam'];
		} else {
			$opponent = $game['HomeTeam'];
		}

		if ($team_id != $game['Game']['home_team'] && $team_id != $game['Game']['away_team']) {
			$this->Session->setFlash(__('That team did not play in that game!', true));
			$this->redirect('/');
		}

		if ($game['GameSlot']['game_date'] > time()) {
			$this->Session->setFlash(__('That game has not yet occurred!', true));
			$this->redirect('/');
		}

		if ($this->Game->_is_finalized ($game)) {
			$this->Session->setFlash(__('The score for that game has already been finalized.', true));
			$this->redirect('/');
		}

		if ($this->Game->_get_score_entry ($game, $team_id)) {
			// We have to use string 1 and 0 here, as that's what the
			// form helper checkbox function compares against, using ===
			$game['Game']['allstar'] = (!empty ($game['Allstar']) ? '1' : '0');
			$game['Game']['incident'] = (!empty ($game['Incident']) ? '1' : '0');
		}

		// We need this in a couple of places
		$spirit_obj = $this->_getComponent ('Spirit', $game['League']['sotg_questions'], $this);

		if (!empty ($this->data)) {
			// We could put these as hidden fields in the form, but we'd need to
			// validate them against the values from the URL anyway, so it's
			// easier to just set them directly here.
			// We use the team_id as the array index, here and in the views,
			// because order matters, and this is a good way to ensure that
			// the correct data gets into the correct form.
			$this->data['Game']['id'] = $id;
			$this->data['ScoreEntry'][$team_id]['team_id'] = $team_id;
			$this->_spiritTeams ($opponent['id'], $team_id, $this->data);

			// Ensure that the saved score entry ids (if any) are the same as the posted ids (if any)
			$saved = $posted = null;
			if (array_key_exists ('ScoreEntry', $game) && array_key_exists ($team_id, $game['ScoreEntry']) && array_key_exists ('id', $game['ScoreEntry'][$team_id])) {
				$saved = $game['ScoreEntry'][$team_id]['id'];
			}
			if (array_key_exists ('id', $this->data['ScoreEntry'][$team_id])) {
				$posted = $this->data['ScoreEntry'][$team_id]['id'];
			}
			if ($saved !== $posted) {
				if (!$posted) {
					$this->Session->setFlash(__('There is already a score submitted by your team for this game. To update this, use the "edit" link.', true));
				} else {
					$this->Session->setFlash(__('ID for posted score does not match the saved ID.', true));
				}
				$this->redirect('/');
			}

			// Same process, for spirit entries
			$saved = $posted = null;
			if (array_key_exists ('SpiritEntry', $game) && array_key_exists ($opponent['id'], $game['SpiritEntry']) && array_key_exists ('id', $game['SpiritEntry'][$opponent['id']])) {
				$saved = $game['SpiritEntry'][$opponent['id']]['id'];
			}
			if (array_key_exists ('id', $this->data['SpiritEntry'][$opponent['id']])) {
				$posted = $this->data['SpiritEntry'][$opponent['id']]['id'];
			}
			if ($saved !== $posted) {
				if (!$posted) {
					$this->Session->setFlash(__('There is already a spirit score submitted by your team for this game. To update this, use the "edit" link.', true));
				} else {
					$this->Session->setFlash(__('ID for posted spirit score does not match the saved ID.', true));
				}
				$this->redirect('/');
			}

			// TODO: Validate that the all-star submissions are on the opposing roster

			// Remove blank all-star fields, as they will cause insertion errors
			if (Configure::read('scoring.allstars') &&
				$game['League']['allstars'] != 'never' &&
				array_key_exists ('Allstar', $this->data))
			{
				foreach ($this->data['Allstar'] as $key => $data) {
					if (!$data['person_id']) {
						// Delete any pre-existing nominations that have been removed
						if (array_key_exists ('id', $data)) {
							$this->Game->Allstar->delete ($data['id']);
						}
						unset ($this->data['Allstar'][$key]);
					}
				}
				if (empty ($this->data['Allstar'])) {
					unset ($this->data['Allstar']);
				}
			} else {
				unset ($this->data['Allstar']);
			}

			// Remove blank incident reports, as they will cause insertion errors
			if (Configure::read('scoring.incident_reports') &&
				array_key_exists ('incident', $this->data['Game']) &&
				$this->data['Game']['incident'])
			{
				$this->data['Incident'][$team_id]['team_id'] = $team_id;
			} else {
				unset ($this->data['Incident']);
			}

			// Set default values in the case of a default reported
			$default = $this->data['ScoreEntry'][$team_id]['defaulted'];
			if ($default == 'us') {
				$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_losing_score');
				$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_winning_score');
				$this->_spiritMerge ($opponent['id'], $spirit_obj->perfect(), $this->data);
			} else if ($default == 'them') {
				$this->data['ScoreEntry'][$team_id]['score_for'] = Configure::read('scoring.default_winning_score');
				$this->data['ScoreEntry'][$team_id]['score_against'] = Configure::read('scoring.default_losing_score');
				$this->_spiritMerge ($opponent['id'], $spirit_obj->defaulted(), $this->data);
			}

			// Spirit score entry validation comes from the spirit component
			$this->Game->SpiritEntry->validate = $spirit_obj->getValidate($game['League']);

			if ($this->Game->saveAll($this->data, array('validate' => 'first'))) {
				// Check if the opponent has an entry
				if (!$this->Game->_get_score_entry($game, $opponent['id'])) {
					// No, so we just mention that it's been saved and move on
					$this->Session->setFlash(__('This score has been saved.  Once your opponent has entered their score, it will be officially posted.', true));
				} else {
					// Otherwise, both teams have an entry.  So, attempt to finalize using
					// this information.
					if( $this->_finalize($id) ) {
						$this->Session->setFlash(__('This score agrees with the score submitted by your opponent. It will now be posted as an official game result.', true));
					} else {
						// Or, we have a problem.  A flash message will have been set in the finalize function.
					}
				}

				// TODO: Check for changes to the incident text to avoid sending a duplicate email,
				// and we probably want to change the text of the email slightly to let the recipient
				// know that it's an update instead of a new incident.
				if (Configure::read('scoring.incident_reports') && $this->data['Game']['incident']) {
					$addr = Configure::read('email.incident_report_email');
					$incident = $this->data['Incident'][$team_id];
					$this->set(compact ('game', 'incident'));
					if ($this->_sendMail (array (
							'to' => "Incident Manager <$addr>",
							'from' => $this->Session->read('Zuluru.Person.full_name') . ' <' . $this->Session->read('Zuluru.Person.email') . '>',
							'subject' => "Incident report: {$incident['type']}",
							'template' => 'incident_report',
							'sendAs' => 'html',
					)))
					{
						// TODO: How to report extra information? Build a big flash message, or allow multiples?
						// Maybe we should send the incident report before saving data, and add in a column for
						// whether it was sent, thus allowing the cron to attempt to re-send it?
						// $resultMessage .= __('Your incident report details have been sent for handling.', true);
					} else {
						// TODO: Router has a url function, but not link; how do we build a link in a controller?
						// $link = Router::link($addr, "mailto:$addr");
						// $resultMessage .= sprintf (__('There was an error sending your incident report details. Please send them to %s to ensure proper handling.', true), $link);
					}
				}

				$this->redirect('/');
			} else {
				$this->Session->setFlash(__('The game results could not be saved. Please, try again.', true));
			}
		} else {
			$this->data = $game;
		}

		$this->set(compact ('game', 'team_id', 'spirit_obj'));
		$this->set('is_coordinator', in_array ($game['League']['id'], $this->Session->read('Zuluru.LeagueIDs')));
	}

	function _spiritTeams($to, $from, &$data) {
		$data['SpiritEntry'][$to]['created_team_id'] = $from;
		$data['SpiritEntry'][$to]['team_id'] = $to;
	}

	function _spiritMerge($to, $scores, &$data) {
		$data['SpiritEntry'][$to] = array_merge ($data['SpiritEntry'][$to], $scores);
	}

	function _finalize($id) {
		$this->Game->contain (array (
			'GameSlot',
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'ScoreEntry',
			'SpiritEntry',
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'ScoreReminderEmail',
			'ScoreMismatchEmail',
		));
		$game = $this->Game->read(null, $id);
		$this->Game->_adjustEntryIndices($game);

		if ($this->Game->_is_finalized($game)) {
			$this->Session->setFlash(__('Game has already been finalized.', true));
			return false;
		}

		$result = $this->_finalize_game ($game);
		if ($result !== true) {
			$this->Session->setFlash($result);
			return false;
		}
		return true;
	}

	/**
	 * Take what is currently known about the game, and finalize it.
	 * If we have:
	 * 	0) no scores entered
	 * 		- forfeit game as 0-0 tie
	 * 		- give poor spirit to both
	 * 	1) one score entered
	 * 		- use single score as final
	 * 		- give full spirit to entering team, assigned spirit, less
	 * 		  some configurable penalty, to non-entering team.
	 * 	2) two scores entered, not agreeing
	 * 		- send email to the coordinator(s).
	 *  3) two scores entered, agreeing
	 *  	- scores are entered as provided, as are spirit values.
	 */
	function _finalize_game($game) {
		// Initialize data to be saved
		$data = array('Game' => array('id' => $game['Game']['id'], 'status' => 'normal'));
		$spirit_obj = $this->_getComponent ('Spirit', $game['League']['sotg_questions'], $this);

		$home_entry = $this->Game->_get_score_entry($game, $game['Game']['home_team']);
		$away_entry = $this->Game->_get_score_entry($game, $game['Game']['away_team']);
		if ($home_entry && $away_entry) {
			if ($this->Game->_score_entries_agree($home_entry, $away_entry)) {
				switch( $home_entry['defaulted'] ) {
					case 'us':
						// HOME default
						$data['Game']['status'] = 'home_default';
						break;

					case 'them':
						// AWAY default
						$data['Game']['status'] = 'away_default';
						break;

					case 'no':
					default:
						// No default.  Just finalize score.
						$data['Game']['home_score'] = $home_entry['score_for'];
						$data['Game']['away_score'] = $home_entry['score_against'];
				}
				$data['Game']['approved_by'] = APPROVAL_AUTOMATIC;
			} else {
				// Maybe send a notification email to the convener
				// TODO: Do this on a recurring basis, every few days, instead of just once
				if (empty ($game['ScoreMismatchEmail'])) {
					$this->set(compact ('game'));
					if ($this->_sendMail (array (
							'to' => $game['League'],
							'subject' => 'Score entry mismatch',
							'template' => 'score_entry_mismatch',
							'sendAs' => 'both',
					)))
					{
						// TODO: Save this directly
						$this->Game->ScoreMismatchEmail->create();
//						$data['ScoreMismatchEmail'][0] = array(
						$this->Game->ScoreMismatchEmail->save(array(
							'type' => 'email_score_mismatch',
							'primary_id' => $game['Game']['id'],
						));
					}
				}
				return __('This score doesn\'t agree with the one your opponent submitted.  Because of this, the score will not be posted until your coordinator approves it.', true);
			}
		} else if ( $home_entry && !$away_entry ) {
			switch( $home_entry['defaulted'] ) {
				case 'us':
					// HOME default with no entry by AWAY
					$data['Game']['status'] = 'home_default';
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->defaulted(), $data);
					break;
				case 'them':
					// AWAY default with no entry by AWAY
					$data['Game']['status'] = 'away_default';
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->perfect(), $data);
					break;
				default:
					// no default, no entry by AWAY
					$data['Game']['home_score'] = $home_entry['score_for'];
					$data['Game']['away_score'] = $home_entry['score_against'];
					$this->_spiritTeams ($game['Game']['home_team'], $game['Game']['away_team'], $data);
					$this->_spiritMerge ($game['Game']['home_team'], $spirit_obj->perfect(), $data);
			}
			$penalty = Configure::read('scoring.missing_score_spirit_penalty');
			$data['SpiritEntry'][$game['Game']['away_team']] = array(
				'id' => $game['SpiritEntry'][$game['Game']['away_team']]['id'],
				'entered_sotg' => max (0, $game['SpiritEntry'][$game['Game']['away_team']]['entered_sotg'] - $penalty),
				'score_entry_penalty' => -$penalty,
			);
			$data['Game']['approved_by'] = APPROVAL_AUTOMATIC_HOME;
			$this->_remind_team($game, $game['AwayTeam'], $game['HomeTeam'], 'approval_notice', false);
		} else if ( !$home_entry && $away_entry ) {
			switch( $away_entry['defaulted'] ) {
				case 'us':
					// AWAY default with no entry by HOME
					$data['Game']['status'] = 'away_default';
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->defaulted(), $data);
					break;
				case 'them':
					// HOME default with no entry by HOME
					$data['Game']['status'] = 'home_default';
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->perfect(), $data);
					break;
				default:
					// no default, no entry by HOME
					$data['Game']['home_score'] = $away_entry['score_against'];
					$data['Game']['away_score'] = $away_entry['score_for'];
					$this->_spiritTeams ($game['Game']['away_team'], $game['Game']['home_team'], $data);
					$this->_spiritMerge ($game['Game']['away_team'], $spirit_obj->perfect(), $data);
			}
			$penalty = Configure::read('scoring.missing_score_spirit_penalty');
			$data['SpiritEntry'][$game['Game']['home_team']] = array(
				'id' => $game['SpiritEntry'][$game['Game']['home_team']]['id'],
				'entered_sotg' => max (0, $game['SpiritEntry'][$game['Game']['home_team']]['entered_sotg'] - $penalty),
				'score_entry_penalty' => -$penalty,
			);
			$data['Game']['approved_by'] = APPROVAL_AUTOMATIC_AWAY;
			$this->_remind_team($game, $game['HomeTeam'], $game['AwayTeam'], 'approval_notice', false);
		} else if ( !$home_entry && !$away_entry ) {
			// TODO: don't do automatic forfeit yet.  Make it per-league configurable
			return __('No score entry found for either team; cannot finalize this game.', true);
		}

		// We want to remember what each team's rating was going into this game.
		// Ratings are not set until a game is finalized, so we don't want to
		// change this if it was already present, as that means we are changing
		// a score that was already approved.
		if ($game['Game']['rating_home'] === null) {
			$data['Game']['rating_home'] = $game['HomeTeam']['rating'];
			$data['Game']['rating_away'] = $game['AwayTeam']['rating'];
		}

		// Possibly adjust the score if the game status changed
		$this->_adjustScoreAndRatings($game, $data);

		if (! $this->Game->saveAll($data)) {
			return __('Could not successfully save game results.', true);
		}

		// Delete score entries
		$this->Game->ScoreEntry->deleteAll(array('game_id' => $game['Game']['id']));

		return true;
	}

	function _adjustScoreAndRatings($game, &$data) {
		if ($data['Game']['status'] != $game['Game']['status']) {
			switch ($data['Game']['status']) {
				case 'home_default':
					$data['Game']['home_score'] = Configure::read('scoring.default_losing_score');
					$data['Game']['away_score'] = Configure::read('scoring.default_winning_score');
					break;

				case 'away_default':
					$data['Game']['home_score'] = Configure::read('scoring.default_winning_score');
					$data['Game']['away_score'] = Configure::read('scoring.default_losing_score');
					break;

				case 'forfeit':
					$data['Game']['home_score'] = $data['Game']['away_score'] = 0;
					break;

				case 'rescheduled':
					// TODO: Should we mangle the scores for a rescheduled game?
					break;

				case 'cancelled':
					$data['Game']['home_score'] = $data['Game']['away_score'] = null;
					break;

				case 'normal':
				default;
					break;
			}
		}

		// Finalize the rating change if we've just updated the score
		if ($data['Game']['home_score'] != $game['Game']['home_score'] || $data['Game']['away_score'] != $game['Game']['away_score']) {
			$this->_modify_team_ratings($game, $data);
		}
	}

	/**
	 * Calculate the value to be added/subtracted from the competing
	 * teams' ratings, using the defined league component.
	 */
	function _modify_team_ratings ($game, &$data) {
		// Initialize what the home and away team ratings will be after this game is finalized
		// We also need to set the team ids in the data to be saved
		$data['HomeTeam'] = array(
			'id' => $game['HomeTeam']['id'],
			'rating' => $game['HomeTeam']['rating'],
		);
		$data['AwayTeam'] = array(
			'id' => $game['AwayTeam']['id'],
			'rating' => $game['AwayTeam']['rating'],
		);

		// If we already have a rating, reverse the effect of this game from the
		// team ratings, and recalculate it.
		if (!is_null($game['Game']['rating_points']) && $game['Game']['rating_points'] != 0) {
			if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
				$data['HomeTeam']['rating'] -= $game['Game']['rating_points'];
				$data['AwayTeam']['rating'] += $game['Game']['rating_points'];
			} else if($game['Game']['away_score'] > $game['Game']['home_score']) {
				$data['HomeTeam']['rating'] += $game['Game']['rating_points'];
				$data['AwayTeam']['rating'] -= $game['Game']['rating_points'];
			}
		}

		// If we're not a normal game, avoid changing the rating.
		$change_rating = false;
		if ($data['Game']['status'] == 'normal') {
			$change_rating = true;
		}
		if (Configure::read('scoring.default_transfer_ratings') &&
			($data['Game']['status'] == 'home_default' || $data['Game']['status'] == 'away_default') )
		{
			$change_rating = true;
		}

		if (! $change_rating) {
			$data['Game']['rating_points'] = 0;
			return true;
		}

		$change = 0;
		$league_obj = $this->_getComponent ('LeagueType', $game['League']['schedule_type'], $this);

		// For a tie, we assume the home team wins
		if ($data['Game']['home_score'] >= $data['Game']['away_score']) {
			$change = $league_obj->calculateRatingsChange($data['Game']['home_score'], $data['Game']['away_score'],
					$this->Game->_calculate_expected_win($data['HomeTeam']['rating'], $data['AwayTeam']['rating']));
			$data['HomeTeam']['rating'] += $change;
			$data['AwayTeam']['rating'] -= $change;
		} else {
			$change = $league_obj->calculateRatingsChange($data['Game']['home_score'], $data['Game']['away_score'],
					$this->Game->_calculate_expected_win($data['AwayTeam']['rating'], $data['HomeTeam']['rating']));
			$data['HomeTeam']['rating'] -= $change;
			$data['AwayTeam']['rating'] += $change;
		}

		$data['Game']['rating_points'] = $change;

		return true;
	}

	function _remind_team($game, $team, $opponent, $reason, $update_db) {
		if (array_key_exists($team['id'], $game['ScoreEntry'])) {
			return false;
		}

		if ($update_db) {
			if (array_key_exists ($team['id'], $game['ScoreReminderEmail'])) {
				return false;
			}
		}

		$variables = array(
			'%fullname' => implode(', ', Set::extract('/Person/full_name', $team)),
			'%team' => $team['name'],
			'%opponent' => $opponent['name'],
			'%league' => $game['League']['name'],
			'%gamedate' => $game['GameSlot']['game_date'],
			'%scoreurl' => Router::url(array('controller' => 'games', 'action' => 'submit_score', 'game' => $game['Game']['id'], 'team' => $team['id']), true),
		);

		if (!$this->_sendMail (array (
				'to' => $team,
				// TODO: Make the email come from the league coordinator instead of club admin
				//'from' => $game['League'],
				'config_subject' => "{$reason}_subject",
				'config_body' => "{$reason}_body",
				'variables' => $variables,
		)))
		{
			return false;
		}

		if ($update_db) {
			$this->Game->ScoreReminderEmail->create();
			$this->Game->ScoreReminderEmail->save(array(
				'type' => "email_$reason",
				'primary_id' => $game['Game']['id'],
				'secondary_id' => $team['id'],
			));
		}
		return true;
	}

	function cron() {
		$this->layout = 'bare';

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		$this->Game->contain (array (
			'GameSlot',
			'League' => array('Person' => array('fields' => array('id', 'first_name', 'last_name', 'email'))),
			'ScoreEntry',
			'SpiritEntry',
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'ScoreReminderEmail',
			'ScoreMismatchEmail',
		));
		$offset = Configure::read('timezone.adjust') * 60;
		$games = $this->Game->find ('all', array(
				'conditions' => array(
					'League.is_open' => true,
					'Game.published' => true,
					"UNIX_TIMESTAMP(CONCAT_WS(' ', GameSlot.game_date, GameSlot.game_start)) + $offset + League.email_after * 60 * 60 < UNIX_TIMESTAMP(NOW())",
					array('OR' => array(
						'Game.home_score' => null,
						'Game.away_score' => null,
					)),
					array('OR' => array(
						'League.email_after >' => 0,
						'League.finalize_after >' => 0,
					)),
				),
				'order' => array('League.id', 'GameSlot.game_date', 'GameSlot.game_start', 'Game.id'),
		));

		$this->Game->_adjustEntryIndices($games);
		$now = time();
		foreach ($games as $key => $game) {
			$game_time = strtotime ("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}");
			$email_time = $game_time + $game['League']['email_after'] * 60 * 60;
			$finalize_time = $game_time + $game['League']['finalize_after'] * 60 * 60;
			$games[$key]['finalized'] = $games[$key]['emailed'] = false;
			if ($now > $finalize_time) {
				$games[$key]['finalized'] = true;
				$games[$key]['finalized'] = $this->_finalize_game ($game);
			}
			if ($games[$key]['finalized'] !== true && $now > $email_time) {
				$games[$key]['emailed'] =
					$this->_remind_team($game, $game['HomeTeam'], $game['AwayTeam'], 'score_reminder', true) ||
					$this->_remind_team($game, $game['AwayTeam'], $game['HomeTeam'], 'score_reminder', true);
			}
		}

		// This "days" calculation isn't precise, as it doesn't handle leap years.
		// However, it's close enough since we're never looking at periods that span
		// from a year end to a leap day.
		$days = date('Y') * 365 + date('z');

		// Find all of the games that might have players that need to be reminded about attendance
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$this->Game->contain(array(
			'GameSlot',
			'HomeTeam',
			'AwayTeam',
			'AttendanceReminderEmail',
		));
		$remind = $this->Game->find ('all', array(
				'conditions' => array(
					'Game.published' => true,
					'GameSlot.game_date >= CURDATE()',
					'OR' => array(
						// DATEDIFF might be a better way to do this, but it's less standardized
						array(
							'HomeTeam.track_attendance' => true,
							'HomeTeam.attendance_reminder !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL HomeTeam.attendance_reminder DAY) >= GameSlot.game_date',
						),
						array(
							'AwayTeam.track_attendance' => true,
							'AwayTeam.attendance_reminder !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL AwayTeam.attendance_reminder DAY) >= GameSlot.game_date',
						),
					),
				),
		));

		$remind_count = 0;
		foreach ($remind as $game) {
			$game_date = strtotime($game['GameSlot']['game_date']);
			$days_to_game = date('Y', $game_date) * 365 + date('z', $game_date) - $days;
			$reminded = Set::extract('/AttendanceReminderEmail/secondary_id', $game);

			if ($game['HomeTeam']['track_attendance'] && $game['HomeTeam']['attendance_reminder'] >= $days_to_game) {
				$remind_count += $this->_remind_attendance($game, $game['HomeTeam'], $game['AwayTeam'], $reminded);
			}
			if ($game['AwayTeam']['track_attendance'] && $game['AwayTeam']['attendance_reminder'] >= $days_to_game) {
				$remind_count += $this->_remind_attendance($game, $game['AwayTeam'], $game['HomeTeam'], $reminded);
			}
		}

		// Find all of the games that might have captains that need attendance summaries
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$this->Game->contain(array(
			'GameSlot',
			// Get the list of captains for each team, we may need to email them
			'HomeTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AwayTeam' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
			),
			'AttendanceSummaryEmail',
		));
		$summary = $this->Game->find ('all', array(
				'conditions' => array(
					'Game.published' => true,
					'GameSlot.game_date >= CURDATE()',
					'OR' => array(
						// DATEDIFF might be a better way to do this, but it's less standardized
						array(
							'HomeTeam.track_attendance' => true,
							'HomeTeam.attendance_summary !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL HomeTeam.attendance_summary DAY) >= GameSlot.game_date',
						),
						array(
							'AwayTeam.track_attendance' => true,
							'AwayTeam.attendance_summary !=' => -1,
							'DATE_ADD(CURDATE(), INTERVAL AwayTeam.attendance_summary DAY) >= GameSlot.game_date',
						),
					),
				),
		));

		$summary_count = 0;
		foreach ($summary as $game) {
			$game_date = strtotime($game['GameSlot']['game_date']);
			$days_to_game = date('Y', $game_date) * 365 + date('z', $game_date) - $days;
			$summarized = Set::extract('/AttendanceSummaryEmail/secondary_id', $game);

			if ($game['HomeTeam']['track_attendance'] && $game['HomeTeam']['attendance_summary'] >= $days_to_game) {
				$summary_count += $this->_summarize_attendance($game, $game['HomeTeam'], $game['AwayTeam'], $summarized);
			}
			if ($game['AwayTeam']['track_attendance'] && $game['AwayTeam']['attendance_summary'] >= $days_to_game) {
				$summary_count += $this->_summarize_attendance($game, $game['AwayTeam'], $game['HomeTeam'], $summarized);
			}
		}

		$this->set(compact('games', 'remind_count', 'summary_count'));

		$this->Lock->unlock();
	}

	function _remind_attendance($game, $team, $opponent, $reminded) {
		$this->set(compact ('game', 'team', 'opponent'));

		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->Game->_read_attendance($team['id'], $game['Game']['id']);
		$sent = 0;
		foreach ($attendance['Person'] as $person) {
			$regular = in_array($person['TeamsPerson']['position'], Configure::read('playing_roster_positions'));
			$sub = (!$regular && in_array($person['TeamsPerson']['position'], Configure::read('extended_playing_roster_positions')));
			if (!is_array($reminded) || !in_array($person['id'], $reminded)) {
				if (($regular && $person['Attendance'][0]['status'] == ATTENDANCE_UNKNOWN) ||
					($sub && $person['Attendance'][0]['status'] == ATTENDANCE_INVITED))
				{
					$this->set(compact ('person'));
					$this->set('code', $this->_hash ($person['Attendance'][0]));

					if ($this->_sendMail (array (
							'to' => $person,
							// Attendance array is sorted by position, so the first one is the captain
							'replyTo' => $attendance['Person'][0],
							'subject' => "{$team['name']} attendance reminder",
							'template' => 'attendance_reminder',
							'sendAs' => 'both',
					)))
					{
						++$sent;
						$this->Game->AttendanceReminderEmail->create();
						$this->Game->AttendanceReminderEmail->save(array(
							'type' => 'email_attendance_reminder',
							'primary_id' => $game['Game']['id'],
							'secondary_id' => $person['id'],
						));
					}
				}
			}
		}

		return $sent;
	}

	function _summarize_attendance($game, $team, $opponent, $summarized) {
		if (is_array($summarized) && in_array($team['id'], $summarized)) {
			return;
		}

		$this->set(compact ('game', 'team', 'opponent'));

		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->Game->_read_attendance($team['id'], $game['Game']['id']);

		// Summarize by attendance status
		$summary = array_fill_keys(array_keys(Configure::read('attendance')),
				array_fill_keys(array_keys(Configure::read('options.gender')), array())
		);
		foreach ($attendance['Person'] as $person) {
			$summary[$person['Attendance'][0]['status']][$person['gender']][] = $person['full_name'];
		}
		$this->set(compact ('summary'));

		$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $team)));
		if ($this->_sendMail (array (
				'to' => $team['Person'],
				'subject' => "{$team['name']} attendance summary",
				'template' => 'attendance_summary',
				'sendAs' => 'both',
		)))
		{
			$this->Game->AttendanceSummaryEmail->create();
			$this->Game->AttendanceSummaryEmail->save(array(
				'type' => 'email_attendance_summary',
				'primary_id' => $game['Game']['id'],
				'secondary_id' => $team['id'],
			));
			return 1;
		}
		return 0;
	}

	function _hash ($attendance) {
		// Build a string of the inputs
		$input = "{$attendance['id']}:{$attendance['team_id']}:{$attendance['game_id']}:{$attendance['person_id']}:{$attendance['created']}";
		if (array_key_exists ('captain', $attendance)) {
			$input .= ":captain";
		}
		return md5($input);
	}
}
?>
