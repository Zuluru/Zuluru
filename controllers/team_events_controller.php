<?php
class TeamEventsController extends AppController {

	var $name = 'TeamEvents';
	var $components = array('Lock');

	function publicActions() {
		return array('cron',
			// Attendance updates may come from emailed links; people might not be logged in
			'attendance_change',
		);
	}

	function isAuthorized() {
		// People can perform these operations on teams they run
		if (in_array ($this->params['action'], array(
				'add',
		)))
		{
			// If a team id is specified, check if we're a captain of that team
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				return true;
			}
		}
		if (in_array ($this->params['action'], array(
				'edit',
				'delete',
		)))
		{
			$event = $this->_arg('event');
			if ($event) {
				$team = $this->TeamEvent->field('team_id', array('id' => $event));
				if ($team && in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
					return true;
				}
			}
		}

		// People can perform these operations on teams they are on
		if (in_array ($this->params['action'], array(
				'view',
		)))
		{
			$event = $this->_arg('event');
			if ($event) {
				$team = $this->TeamEvent->field('team_id', array('id' => $event));
				if ($team && in_array ($team, $this->Session->read('Zuluru.TeamIDs'))) {
					return true;
				}
			}
		}

		return false;
	}

	function view() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->TeamEvent->contain(array(
			'Team' => array(
				'Person',
				'Division' => 'League',
			),
		));

		$event = $this->TeamEvent->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($event['Team']['Division']['League']['affiliate_id']);

		usort ($event['Team']['Person'], array('Team', 'compareRoster'));

		// _read_attendance returns an array, but we only want one event
		$attendance = array_shift ($this->TeamEvent->_read_attendance($event['Team'], $id));
		$this->set(compact('event', 'attendance'));
		$this->set('is_captain', in_array($event['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs')));
	}

	function add() {
		if (!empty($this->data)) {
			$this->TeamEvent->create();
			if ($this->TeamEvent->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('team event', true)), 'default', array('class' => 'success'));
				$this->redirect('/');
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team event', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->TeamEvent->Team->affiliate($this->data['TeamEvent']['team_id']));
			}
		}

		if (empty($this->data)) {
			$id = $this->_arg('team');
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->TeamEvent->Team->contain(array(
				'Division' => 'League',
			));

			$this->data = $this->TeamEvent->Team->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->data['TeamEvent'] = array('team_id' => $id);
			$this->Configuration->loadAffiliate($this->data['Division']['League']['affiliate_id']);
		}

		$this->_loadAddressOptions();
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('event');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!empty($this->data)) {
			if ($this->TeamEvent->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('team event', true)), 'default', array('class' => 'success'));
				$this->redirect('/');
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team event', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->TeamEvent->Team->affiliate($this->data['TeamEvent']['team_id']));
			}
		}
		if (empty($this->data)) {
			$this->TeamEvent->contain(array(
				'Team' => array(
					'Division' => 'League',
				),
			));
			$this->data = $this->TeamEvent->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->Configuration->loadAffiliate($this->data['Team']['Division']['League']['affiliate_id']);
		}

		$this->_loadAddressOptions();
	}

	function delete() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if ($this->TeamEvent->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Team event', true)), 'default', array('class' => 'success'));
			$this->redirect('/');
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Team event', true)), 'default', array('class' => 'warning'));
		$this->redirect('/');
	}

	function attendance_change() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$team_id = $this->TeamEvent->field ('team_id', compact('id'));
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->TeamEvent->contain(array(
			// Get the list of captains, for the team pop-up
			'Team' => array(
				'Person' => array(
					'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
					'fields' => array('id', 'first_name', 'last_name', 'email'),
				),
				'Division' => 'League',
			),
			'Attendance' => array(
				'conditions' => array(
					'person_id' => $person_id,
				),
				'Person' => array(
					'Team' => array(
						'conditions' => array('team_id' => $team_id),
					),
				),
			),
		));
		$event = $this->TeamEvent->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($event['Team']['Division']['League']['affiliate_id']);
		$date = $event['TeamEvent']['date'];
		$past = ("{$event['TeamEvent']['date']} {$event['TeamEvent']['start']}" < date('Y-m-d H:i:s'));

		// Pull out the player and attendance records.
		$attendance = $event['Attendance'][0];
		$person = $attendance['Person'];
		$team = $event['Team'];

		if (!$team['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!$attendance) {
			$this->Session->setFlash(__('That person does not have an attendance record for this event.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$is_me = ($person_id == $this->Auth->user('id'));
		$is_captain = in_array ($team['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$player_hash = $this->_hash($attendance);
			$captain_hash = $this->_hash(array_merge ($attendance, array('captain' => true)));
			// Temporary addition during hash conversion period
			$player_hash2 = $this->_hash($attendance, false);
			$captain_hash2 = $this->_hash(array_merge ($attendance, array('captain' => true)), false);
			if ($player_hash == $code || $player_hash2 == $code) {
				// Only the player will have this confirmation code
				$is_me = true;
			} else if ($captain_hash == $code || $captain_hash2 == $code) {
				$is_captain = true;
			} else {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect('/');
			}

			// Fake the posted data array with the status from the URL
			$this->data = array('Person' => array('status' => $this->_arg('status')));
		} else {
			// Players can change their own attendance, captains can change any attendance on their teams
			if (!$is_me && !$is_captain) {
				$this->Session->setFlash(__('You are not allowed to change this attendance record.', true), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$role = $person['Team'][0]['TeamsPerson']['role'];
		$attendance_options = Game::_attendanceOptions ($team['id'], $role, $attendance['status'], $past, $is_captain);
		$this->set(compact('event', 'date', 'team', 'person', 'status', 'attendance', 'attendance_options', 'is_captain', 'is_me'));

		if (!empty ($this->data)) {
			$this->TeamEvent->Attendance->id = $attendance['id'];

			// This "days" calculation isn't precise, as it doesn't handle leap years.
			// However, it's close enough since we're never looking at periods that span
			// from a year end to a leap day.
			$days = date('Y') * 365 + date('z');
			$days_to_event = date('Y', strtotime($date)) * 365 + date('z', strtotime($date)) - $days;

			if (array_key_exists('status', $this->data['Person'])) {
				$this->set('status', $this->data['Person']['status']);
				$this->set('comment', $attendance['comment']);
				$result = $this->_updateAttendanceStatus($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_event, $past, $attendance_options);
			} else {
				$this->set('status', $attendance['status']);
				$this->set('comment', $this->data['Person']['comment']);
				$result = $this->_updateAttendanceComment($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_event, $past);
			}

			// Where do we go from here? It depends...
			if (!$result) {
				if ($code) {
					$this->redirect('/');
				}
			} else {
				if ($this->RequestHandler->isAjax()) {
					$this->action = 'attendance_change_ajax';
					$this->set('dedicated', $this->data['dedicated']);
				} else if (!$this->is_logged_in) {
					$this->redirect(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']));
				} else {
					$this->redirect(array('action' => 'view', 'event' => $id));
				}
			}
		}
	}

	function _updateAttendanceStatus($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_event, $past, $attendance_options) {
		$status = $this->data['Person']['status'];
		if (!array_key_exists ($status, $attendance_options)) {
			$this->Session->setFlash(__('That is not currently a valid attendance status for this player for this event.', true), 'default', array('class' => 'info'));
			return false;
		}

		if ($status == $attendance['status'] &&
			// Non-JavaScript submissions might include a comment
			(!array_key_exists('comment', $this->data['Person']) || empty($this->data['Person']['comment'])) &&
			// Invitations might include a note from the captain
			(!array_key_exists('note', $this->data['Person']) || empty($this->data['Person']['note'])))
		{
			return true;
		}

		if (!$this->TeamEvent->Attendance->saveField ('status', $status)) {
			$this->Session->setFlash(__('Failed to update the attendance status!', true), 'default', array('class' => 'warning'));
			return false;
		}
		if (array_key_exists('comment', $this->data['Person'])) {
			$comment = $this->data['Person']['comment'];
			if ($comment != $attendance['comment']) {
				if (!$this->TeamEvent->Attendance->saveField ('comment', $comment)) {
					$this->Session->setFlash(__('Failed to update the attendance comment!', true), 'default', array('class' => 'warning'));
					return false;
				}
			}
		}

		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash(sprintf (__('Attendance has been updated to %s.', true), $attendance_options[$status]), 'default', array('class' => 'success'));
		}

		// Maybe send some emails, only if the event is in the future
		if (!$past) {
			$role = $person['Team'][0]['TeamsPerson']['role'];

			// Send email from the player to the captain if it's within the configured date range
			if ($is_me && $team['attendance_notification'] >= $days_to_event) {
				// Make sure the current player isn't in the list of captains to send to
				$captains = Set::extract ("/Person[id!={$person['id']}]", $team);
				if (!empty ($captains)) {
					if (array_key_exists('comment', $this->data['Person']) && !empty($this->data['Person']['comment'])) {
						$this->set('comment', $this->data['Person']['comment']);
					}

					$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));
					$this->set('code', $this->_hash(array_merge ($attendance, array('captain' => true))));
					$this->_sendMail (array (
							'to' => $captains,
							'replyTo' => $person,
							'subject' => "{$team['name']} attendance change",
							'template' => 'event_attendance_captain_notification',
							'sendAs' => 'both',
					));
				}
			}
			// Always send an email from the captain to substitute players. It will likely
			// be an invitation to play or a response to a request or cancelling attendance
			// if another player is available. Regardless, we need to communicate this.
			else if ($is_captain && !in_array($role, Configure::read('playing_roster_roles'))) {
				$captain = $this->Session->read('Zuluru.Person.full_name');
				if (!$captain) {
					$captain = __('A captain', true);
				}
				$this->set(compact('captain'));
				$this->set('player_options',
					Game::_attendanceOptions ($team['id'], $role, $status, $past, false));
				$this->set('code', $this->_hash ($attendance));
				if (array_key_exists('note', $this->data['Person']) && !empty($this->data['Person']['note'])) {
					$this->set('note', $this->data['Person']['note']);
				}

				$this->_sendMail (array (
						'to' => $person,
						'replyTo' => $this->Session->read('Zuluru.Person'),
						'subject' => "{$team['name']} attendance change for event on $date",
						'template' => 'event_attendance_substitute_notification',
						'sendAs' => 'both',
				));
			}
		}

		return true;
	}

	function _updateAttendanceComment($team, $person, $date, $is_captain, $is_me, $attendance, $days_to_event, $past) {
		$comment = $this->data['Person']['comment'];
		if ($comment == $attendance['comment']) {
			return true;
		}

		if (!$this->TeamEvent->Attendance->saveField ('comment', $comment)) {
			$this->Session->setFlash(__('Failed to update the attendance comment!', true), 'default', array('class' => 'warning'));
			return false;
		}

		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash(sprintf (__('Attendance has been updated to %s.', true), $attendance_options[$status]), 'default', array('class' => 'success'));
		}

		// Maybe send some emails, only if the event is in the future
		if (!$past) {
			// Send email from the player to the captain if it's within the configured date range
			if ($is_me && $team['attendance_notification'] >= $days_to_event) {
				// Make sure the current player isn't in the list of captains to send to
				$captains = Set::extract ("/Person[id!={$person['id']}]", $team);
				if (!empty ($captains)) {
					$this->set('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));
					$this->_sendMail (array (
							'to' => $captains,
							'replyTo' => $person,
							'subject' => "{$team['name']} attendance comment",
							'template' => 'event_attendance_comment_captain_notification',
							'sendAs' => 'both',
					));
				}
			}
		}

		return true;
	}

	function cron() {
		$this->layout = 'bare';

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		// This "days" calculation isn't precise, as it doesn't handle leap years.
		// However, it's close enough since we're never looking at periods that span
		// from a year end to a leap day.
		$days = date('Y') * 365 + date('z');

		// Find all of the events that might have players that need to be reminded about attendance
		$this->TeamEvent->contain(array(
			'Team' => array(
				'Person' => array(
					'Setting' => array(
						'conditions' => array('category' => 'personal', 'name' => 'attendance_emails'),
					),
				),
			),
			'AttendanceReminderEmail',
		));
		$remind = $this->TeamEvent->find ('all', array(
				'conditions' => array(
					'TeamEvent.date >= CURDATE()',
					'Team.track_attendance' => true,
					'Team.attendance_reminder !=' => -1,
					// DATEDIFF might be a better way to do this, but it's less standardized
					'DATE_ADD(CURDATE(), INTERVAL Team.attendance_reminder DAY) >= TeamEvent.date',
				),
		));

		$remind_count = 0;
		foreach ($remind as $event) {
			$event_date = strtotime($event['TeamEvent']['date']);
			$days_to_event = date('Y', $event_date) * 365 + date('z', $event_date) - $days;
			$reminded = Set::extract('/AttendanceReminderEmail/person_id', $event);

			if ($event['Team']['track_attendance'] && $event['Team']['attendance_reminder'] >= $days_to_event) {
				$remind_count += $this->_remindAttendance($event, $event['Team'], $reminded);
			}
		}

		// Find all of the events that might have captains that need attendance summaries
		$this->TeamEvent->contain(array(
			// Get the list of captains, we may need to email them
			'Team' => 'Person',
			'AttendanceSummaryEmail',
		));
		$summary = $this->TeamEvent->find ('all', array(
				'conditions' => array(
					'TeamEvent.date >= CURDATE()',
					'Team.track_attendance' => true,
					'Team.attendance_summary !=' => -1,
					// DATEDIFF might be a better way to do this, but it's less standardized
					'DATE_ADD(CURDATE(), INTERVAL Team.attendance_summary DAY) >= TeamEvent.date',
				),
		));

		$summary_count = 0;
		foreach ($summary as $event) {
			$event_date = strtotime($event['TeamEvent']['date']);
			$days_to_event = date('Y', $event_date) * 365 + date('z', $event_date) - $days;
			$summarized = Set::extract('/AttendanceSummaryEmail/team_id', $event);

			if ($event['Team']['track_attendance'] && $event['Team']['attendance_summary'] >= $days_to_event) {
				$summary_count += $this->_summarizeAttendance($event, $event['Team'], $summarized);
			}
		}

		$this->set(compact('remind_count', 'summary_count'));

		$this->Lock->unlock();
	}

	function _remindAttendance($event, $team, $reminded) {
		$this->set(compact ('event', 'team'));

		// Read the attendance records for this event.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = array_shift ($this->TeamEvent->_read_attendance($team, $event['TeamEvent']['id']));
		$sent = 0;

		usort ($team['Person'], array('Team', 'compareRoster'));

		foreach ($attendance['Attendance'] as $record) {
			$person = array_shift (Set::extract("/Person[id={$record['person_id']}]/.", $team));
			$regular = in_array($person['TeamsPerson']['role'], Configure::read('playing_roster_roles'));
			$sub = (!$regular && in_array($person['TeamsPerson']['role'], Configure::read('extended_playing_roster_roles')));
			$always = (!empty($person['Setting']) && $person['Setting'][0]['value'] != false);
			if (!is_array($reminded) || !in_array($person['id'], $reminded)) {
				if (($regular && $record['status'] == ATTENDANCE_UNKNOWN) ||
					($sub && $record['status'] == ATTENDANCE_INVITED) ||
					$always)
				{
					$this->set(compact ('person'));
					$this->set('status', $record['status']);
					$this->set('code', $this->_hash ($record));

					if ($this->_sendMail (array (
							'to' => $person,
							// Team array is sorted by role, so the first one is the captain
							'replyTo' => $team['Person'][0],
							'subject' => "{$team['name']} attendance reminder",
							'template' => 'event_attendance_reminder',
							'sendAs' => 'both',
					)))
					{
						++$sent;
						$this->TeamEvent->AttendanceReminderEmail->create();
						$this->TeamEvent->AttendanceReminderEmail->save(array(
							'type' => 'email_event_attendance_reminder',
							'team_event_id' => $event['TeamEvent']['id'],
							'person_id' => $person['id'],
						));
					}
				}
			}
		}

		return $sent;
	}

	function _summarizeAttendance($event, $team, $summarized) {
		if (is_array($summarized) && in_array($team['id'], $summarized)) {
			return;
		}

		$this->set(compact ('event', 'team'));

		// Read the attendance records for this event.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = array_shift ($this->TeamEvent->_read_attendance($team, $event['TeamEvent']['id']));

		// Summarize by attendance status
		$summary = array_fill_keys(array_keys(Configure::read('attendance')),
				array_fill_keys(array_keys(Configure::read('options.gender')), array())
		);
		$captains = array();
		foreach ($attendance['Attendance'] as $record) {
			$person = array_shift (Set::extract("/Person[id={$record['person_id']}]/.", $team));
			$summary[$record['status']][$person['gender']][] = $person['full_name'];
			if (in_array($person['TeamsPerson']['role'], Configure::read('privileged_roster_roles'))) {
				$captains[] = $person;
			}
		}
		$this->set(compact ('summary'));

		$this->set('captains', implode (', ', Set::extract ('/Person/first_name', array('Person' => $captains))));
		if ($this->_sendMail (array (
				'to' => $captains,
				'subject' => "{$team['name']} attendance summary",
				'template' => 'event_attendance_summary',
				'sendAs' => 'both',
		)))
		{
			$this->TeamEvent->AttendanceSummaryEmail->create();
			$this->TeamEvent->AttendanceSummaryEmail->save(array(
				'type' => 'email_event_attendance_summary',
				'team_event_id' => $event['TeamEvent']['id'],
				'team_id' => $team['id'],
			));
			return 1;
		}
		return 0;
	}

	function _hash ($attendance, $salt = true) {
		// Build a string of the inputs
		$input = "{$attendance['id']}:{$attendance['team_event_id']}:{$attendance['person_id']}:{$attendance['created']}";
		if (array_key_exists ('captain', $attendance)) {
			$input .= ":captain";
		}
		if ($salt) {
			$input = $input . ':' . Configure::read('Security.salt');
		}
		return md5($input);
	}
}
?>