<?php
class TeamEvent extends AppModel {
	var $name = 'TeamEvent';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'website' => array(
			'url' => array(
				'rule' => array('url'),
				'allowEmpty' => true,
				'required' => false,
			),
		),
		'date' => array(
			'date' => array(
				'rule' => array('date'),
			),
		),
		'start' => array(
			'time' => array(
				'rule' => array('time'),
			),
		),
		'end' => array(
			'time' => array(
				'rule' => array('time'),
			),
		),
		'location_name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'location_street' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'location_city' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'location_province' => array(
			'inquery' => array(
				'rule' => array('inquery', 'Province', 'name'),
				'required' => false,
				'message' => 'Select a province from the list',
			),
		),
	);
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Attendance' => array(
			'className' => 'Attendance',
			'foreignKey' => 'team_event_id',
			'dependent' => true,
			'conditions' => array('team_event_id !=' => null),
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'AttendanceReminderEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'team_event_id',
			'dependent' => true,
			'conditions' => array('type' => array('email_event_attendance_reminder')),
		),
		'AttendanceSummaryEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'team_event_id',
			'dependent' => true,
			'conditions' => array('type' => 'email_event_attendance_summary'),
		),
	);

	/**
	 * Read the attendance records for an event.
	 * This will also create any missing records, with "unknown" status.
	 *
	 * @param mixed $team The team to read attendance for.
	 * @param mixed $event_id The event id, or null for all team events.
	 * @return mixed List of events with attendance records.
	 *
	 */
	function _read_attendance($team, $event_id = null) {
		// We accept either a pre-read team array with roster info, or just an id
		if (!is_array($team)) {
			$team_id = $team;
			$this->Attendance->Team->contain (array(
				'Person' => array(
					'fields' => array('Person.id'),
				),
			));
			$team = $this->Attendance->Team->read(null, $team_id);
		} else if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
		} else {
			$team_id = $team['Team']['id'];
		}

		// Make sure that all required records exist
		$event_conditions = array('team_id' => $team_id);
		if ($event_id === null) {
			$events = $this->find('list', array(
					'fields' => array('id', 'date'),
					'conditions' => array('team_id' => $team_id),
					'contain' => false,
			));
			if (empty($events)) {
				return array();
			}
			$attendance_conditions = array('team_event_id' => array_keys($events));
			foreach ($events as $event_id => $date) {
				$this->_create_attendance($team, $event_id, $date);
			}
		} else {
			$event_conditions['id'] = $event_id;
			$attendance_conditions = array('team_event_id' => $event_id);
			$date = $this->field ('date', array('id' => $event_id));
			$this->_create_attendance($team, $event_id, $date);
		}

		// Re-read whatever is current, including join tables that will be useful in the output
		$attendance = $this->find('all', array(
				'conditions' => $event_conditions,
				'contain' => array(
					'Attendance' => array(
						'conditions' => $attendance_conditions,
					),
				),
				'order' => 'TeamEvent.date',
		));

		return $attendance;
	}

	function _create_attendance($team, $event_id, $date) {
		if (array_key_exists ('id', $team)) {
			$team_id = $team['id'];
		} else {
			$team_id = $team['Team']['id'];
		}

		// Find event details
		$this->contain();
		$event = $this->read(null, $event_id);
		if (!$event) {
			return;
		}
		if ($event['TeamEvent']['team_id'] != $team_id) {
			return;
		}

		// Find all attendance records for this event
		$attendance = $this->Attendance->find('all', array(
			'contain' => false,
			'conditions' => array(
					'team_event_id' => $event_id,
			),
		));

		// Extract list of players on the roster as of this date
		$roster = Set::extract ("/Person/TeamsPerson[created<=$date]/../.", $team);

		// Go through the roster and make sure there are records for all players on this date.
		$attendance_update = array();
		foreach ($roster as $person) {
			$update = false;
			$conditions = "[person_id={$person['id']}]";

			$record = Set::extract ("/Attendance$conditions/.", $attendance);

			if (empty ($record)) {
				// We didn't find any appropriate record, so create a new one
				$attendance_update[] = array(
					'team_id' => $team_id,
					'game_date' => $date,
					'team_event_id' => $event_id,
					'person_id' => $person['id'],
					'status' => ATTENDANCE_UNKNOWN,
				);
			}
		}
		if (!empty ($attendance_update)) {
			$this->Attendance->saveAll($attendance_update);
		}
	}
}
?>
