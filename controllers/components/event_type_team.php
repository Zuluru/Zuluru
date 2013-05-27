<?php

/**
 * Derived class for implementing functionality for team events.
 */

class EventTypeTeamComponent extends EventTypeComponent
{
	function configurationFields() {
		return array('level_of_play', 'ask_status', 'ask_region', 'ask_attendance');
	}

	function configurationFieldsElement() {
		return 'team';
	}

	// ID numbers don't much matter, but they can't be duplicated between event types,
	// and they can't ever be changed, because they're in the database.
	function registrationFields($event, $user_id, $for_output = false) {
		$fields = array(
			array(
				'type' => 'group_start',
				'question' => __('Team Details', true),
			),
			array(
				'id' => TEAM_NAME,
				'type' => 'text',
				'question' => __('Team Name', true),
				'after' => __('The full name of your team.', true),
				'required' => true,
			),
		);
		if (Configure::read('feature.shirt_colour')) {
			$fields[] = array(
				'id' => SHIRT_COLOUR,
				'type' => 'text',
				'question' => __('Shirt Colour', true),
				'after' => __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.', true),
				'required' => true,
			);
		}

		if ($for_output) {
			$fields[] = array(
				'id' => TEAM_ID,
				'type' => 'text',
				'question' => __('Team ID', true),
			);
		}

		// These questions are only meaningful when we are creating team records
		if (!empty($event['Event']['division_id'])) {
			if (Configure::read('feature.franchises')) {
				if (!isset ($this->_controller->Team)) {
					$this->_controller->Team = ClassRegistry::init ('Team');
				}

				if (array_key_exists('Division', $event)) {
					$division = $event;
				} else {
					$division = $event['Event'];
				}
				$this->_controller->Team->Division->addPlayoffs($division);
				$conditions = array();

				// Possibly narrow the list of possible franchises to those that are represented
				// in the configured divisions
				if ($division['Division']['is_playoff']) {
					$this->_controller->Team->contain('Franchise');
					$teams = $this->_controller->Team->find('all', array(
							'conditions' => array('Team.division_id' => $division['Division']['season_divisions']),
					));
					$franchise_ids = Set::extract ('/Franchise/id', $teams);
					$conditions['Franchise.id'] = $franchise_ids;
				}

				$franchises = $this->_controller->Team->Franchise->readByPlayerId($user_id, $conditions);
				$franchises = Set::combine($franchises, '{n}.id', '{n}.name');

				// Teams added to playoff divisions must be in pre-existing franchises
				if ($division['Division']['is_playoff']) {
					$extra = '<span class="warning-message">' . __('This MUST be the same franchise that the regular-season team belongs to, or you will NOT be able to correctly set up your roster.', true) . '</span>';
				} else {
					$franchises[-1] = __('Create a new franchise', true);
					$extra = __('You may also choose to start a new franchise.', true);
				}

				$fields[] = array(
					'id' => FRANCHISE_ID,
					'type' => 'select',
					'question' => __('Franchise', true),
					'after' => sprintf (__('Select an existing franchise to add this team to. %s You can only add teams to franchises you own; if you don\'t own the franchise this team should be added to, have the owner transfer ownership to you before registering this team.', true), $extra),
					'options' => $franchises,
					'required' => true,
				);
			}

			if (Configure::read('feature.region_preference') && !empty($event['Event']['ask_region'])) {
				// Could this model path be any more convoluted?
				$regions = $this->_controller->Team->Division->Game->GameSlot->Field->Facility->Region->find('list');
				$fields[] = array(
					'id' => REGION_PREFERENCE,
					'type' => 'select',
					'question' => __('Region Preference', true),
					'after' => __('Area of city where you would prefer to play.', true),
					'options' => $regions,
				);
			}

			if (!empty($event['Event']['ask_status'])) {
				$fields[] = array(
					'id' => OPEN_ROSTER,
					'type' => 'checkbox',
					'question' => __('Open Roster', true),
					'after' => __('If the team roster is open, others can request to join; otherwise, only the captain can add players.', true),
				);
			}

			if (Configure::read('feature.attendance') && !empty($event['Event']['ask_status'])) {
				$fields[] = array(
					'id' => TRACK_ATTENDANCE,
					'type' => 'checkbox',
					'question' => __('Attendance Tracking', true),
					'default' => true,
					'after' => __('Would you like to enable attendance tracking for this team?', true),
				);
			}
		}

		$fields[] = array('type' => 'group_end');

		return $fields;
	}

	function registrationFieldsValidation($event, $for_edit = false) {
		// 'message' must go into an array with key = 'q{answer}' because
		// field names when we display this are like Response.q{id}.answer
		$validation = array(
			'q' . TEAM_NAME => array(
				'notempty' => array(
					'rule' => array('response', 'notempty'),
					'message' => array('answer' => 'Team name must not be blank.'),
				),
			),
		);
		if (Configure::read('feature.shirt_colour')) {
			$validation['q' . SHIRT_COLOUR] = array(
				'notempty' => array(
					'rule' => array('response', 'notempty'),
					'message' => array('answer' => 'Shirt colour must not be blank.'),
				),
			);
		}

		// TODO: Add region and open roster validation, if necessary
		if (!empty($event['Event']['division_id'])) {
			if (array_key_exists('Response', $event)) {
				$team_id = $this->_extractAnswer ($event, TEAM_ID);
			} else {
				$team_id = null;
			}

			// If we're creating team records in a division, make sure the name is unique in that entire league
			$validation['q' . TEAM_NAME]['unique'] = array(
				'rule' => array('team_unique', $team_id, $event['Event']['division_id']),
				'message' => array('answer' => 'There is already a team by that name in this league.'),
			);

			if (Configure::read('feature.franchises')) {
				$validation['q' . FRANCHISE_ID] = array(
					'notempty' => array(
						'rule' => array('response', 'notempty'),
						'message' => array('answer_id' => 'Select one.'),
					),
					'owner' => array(
						'rule' => array('franchise_owner', $this->_controller->Auth->user('id'), $this->_controller->is_admin),
						'message' => array('answer_id' => 'That franchise does not belong to you.'),
					),
				);
				if (!$for_edit) {
					$validation['q' . FRANCHISE_ID]['unique'] = array(
						'rule' => array('franchise_unique'),
						'message' => array('answer_id' => 'New franchises are created with the same name as the team, but there is already a franchise with this name. To add this team to that franchise, you must be the franchise owner, which may require that the current owner add you as an owner.'),
					);
				}
			}
		}

		return $validation;
	}

	// TODO: A site or per-league configuration controlling whether team records
	// are created when registered or when paid
	function paid($event, $data) {
		$ret = $this->_createTeam($event, $data);
		if (parent::paid($event, $data)) {
			return $ret;
		} else {
			return false;
		}
	}

	function unpaid($event, $data) {
		if (parent::unpaid($event, $data)) {
			return $this->_deleteTeam($event, $data);
		} else {
			return false;
		}
	}

	function _createTeam($event, $data) {
		if (empty($event['Event']['division_id'])) {
			return true;
		}

		if (!isset ($this->_controller->Team)) {
			$this->_controller->Team = ClassRegistry::init ('Team');
		}

		$team = array_merge(
			array(
				'division_id' => $event['Event']['division_id'],
			),
			$this->_extractAnswers ($data, array(
				'name' => TEAM_NAME,
				'shirt_colour' => SHIRT_COLOUR,
				'region_id' => REGION_PREFERENCE,
				'open_roster' => OPEN_ROSTER,
				'track_attendance' => TRACK_ATTENDANCE,
			))
		);
		if (Configure::read('feature.attendance') && !empty($team['track_attendance'])) {
			// Add some default values, chosen based on averages found in the TUC database so far
			$team += array(
				'attendance_reminder' => 3,
				'attendance_summary' => 1,
				'attendance_notification' => 1,
			);
		}

		$this->_controller->Team->create();
		if ($this->_controller->Team->save ($team)) {
			// If this is a pre-existing registration that's being paid,
			// the captain is the person that registered it. This is the
			// case when an offline payment is recorded by an admin, for
			// example. Otherwise, the captain is the current user.
			if (array_key_exists ('person_id', $data['Registration'])) {
				$captain_id = $data['Registration']['person_id'];
			} else if (array_key_exists ('Registration', $event) && array_key_exists ('person_id', $event['Registration'])) {
				$captain_id = $event['Registration']['person_id'];
			} else {
				$captain_id = $this->_controller->Auth->user('id');
			}

			$this->_controller->Team->TeamsPerson->create();
			if (!$this->_controller->Team->TeamsPerson->save (array(
				'team_id' => $this->_controller->Team->id,
				'person_id' => $captain_id,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
			)))
			{
				return false;
			}

			$responses = array();

			if (Configure::read('feature.franchises')) {
				$franchise = $this->_extractAnswer($data, FRANCHISE_ID);
				// We may need to create a new franchise record
				if ($franchise == -1) {
					$this->_controller->Team->Franchise->create();
					if (!$this->_controller->Team->Franchise->saveAll (array(
						'Franchise' => array(
							'name' => $team['name'],
						),
						'Person' => array(
							$captain_id,
						),
					)))
					{
						// TODO: Some way to return the validation error, giving the user a better error message
						return false;
					}
					$franchise = $this->_controller->Team->Franchise->id;
					$responses[] = array(
						'question_id' => FRANCHISE_ID_CREATED,
						'answer' => $franchise,
					);
				}
				$this->_controller->Team->FranchisesTeam->create();
				if (!$this->_controller->Team->FranchisesTeam->save (array(
					'team_id' => $this->_controller->Team->id,
					'franchise_id' => $franchise,
				)))
				{
					return false;
				}
			}

			// TODO: Return validation errors?
			$responses[] = array(
				'question_id' => TEAM_ID,
				'answer' => $this->_controller->Team->id,
			);
			$this->_controller->_deleteTeamSessionData();
			$this->_controller->_deleteFranchiseSessionData();

			if (array_key_exists('Registration', $data) && array_key_exists('id', $data['Registration'])) {
				foreach (array_keys($responses) as $key) {
					$responses[$key]['registration_id'] = $data['Registration']['id'];
				}
			}

			return $responses;
		}
		return false;
	}

	function _deleteTeam($event, $data) {
		if (empty($event['Event']['division_id'])) {
			return true;
		}

		$team_id = $this->_extractAnswer ($data, TEAM_ID);
		if ($team_id) {
			$this->_controller->_deleteTeamSessionData();
			if (!isset ($this->_controller->Team)) {
				$this->_controller->Team = ClassRegistry::init ('Team');
			}
			if ($this->_controller->Team->delete ($team_id)) {
				$delete = array($this->_extractAnswerId ($data, TEAM_ID));
				if (Configure::read('feature.franchises')) {
					$franchise_id = $this->_extractAnswer($data, FRANCHISE_ID_CREATED);
					if ($franchise_id) {
						// Delete the franchise record too, if it's empty now
						$this->_controller->Team->Franchise->contain('Team');
						$franchise = $this->_controller->Team->Franchise->read(null, $franchise_id);
						if (empty($franchise['Team'])) {
							$this->_controller->Team->Franchise->delete ($franchise_id);
							$delete[] = $this->_extractAnswerId ($data, FRANCHISE_ID_CREATED);
						}
					}
				}

				$this->_controller->_deleteTeamSessionData();
				$this->_controller->_deleteFranchiseSessionData();
				return $delete;
			}
			return false;
		}

		return true;
	}

	function longDescription($data) {
		$team = $this->_extractAnswer ($data, TEAM_NAME);
		return "{$data['Event']['name']}: $team";
	}
}

?>
