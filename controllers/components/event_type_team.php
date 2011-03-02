<?php

/**
 * Derived class for implementing functionality for team events.
 */

class EventTypeTeamComponent extends EventTypeComponent
{
	function configurationFields() {
		return array('team_league', 'level_of_play', 'ask_status', 'ask_region');
	}

	function configurationFieldsElement() {
		return 'team';
	}

	function configurationFieldsValidation() {
		return array(
			'team_league' => array(
				'numeric' => array(
					'rule' => array('inquery', 'League', 'id'),
					'message' => 'You must select a valid league.',
					'allowEmpty' => true,
				),
			),
		);
	}

	// ID numbers don't much matter, but they can't be duplicated between event types,
	// and they can't ever be changed, because they're in the database.
	function registrationFields($event) {
		$fields = array(
			array(
				'type' => 'group_start',
				'question' => __('Team Details', true),
			),
			array(
				'id' => -1,
				'type' => 'text',
				'question' => __('Team Name', true),
				'after' => __('The full name of your team.', true),
				'required' => true,
			),
			array(
				'id' => -2,
				'type' => 'text',
				'question' => __('Shirt Colour', true),
				'after' => __('Shirt colour of your team. If you don\'t have team shirts, pick \'light\' or \'dark\'.', true),
				'required' => true,
			),
		);

		// These questions are only meaningful when we are creating team records
		if ($event['Event']['team_league'] != null) {
			if (Configure::read('feature.region_preference') && array_key_exists ('ask_region', $event['Event']) && $event['Event']['ask_region']) {
				$fields[] = array(
					'id' => -3,
					'type' => 'select',
					'question' => __('Region Preference', true),
					'after' => __('Area of city where you would prefer to play.', true),
					// TODO: Populate with possibilities from regions table
					'options' => array(),
				);
			}

			if (array_key_exists ('ask_status', $event['Event']) && $event['Event']['ask_status']) {
				$fields[] = array(
					'id' => -4,
					'type' => 'checkbox',
					'question' => __('Open Roster', true),
					'after' => __('If the team roster is open, others can request to join; otherwise, only the captain can add players.', true),
				);
			}
		}

		$fields[] = array('type' => 'group_end');

		return $fields;
	}

	function registrationFieldsValidation($event) {
		// 'message' must go into an array with key = 'q{answer}' because
		// field names when we display this are like Response.q{id}.answer
		$validation = array(
			'q-1' => array(
				'notempty' => array(
					'rule' => array('response', 'notempty'),
					'message' => array('answer' => 'Team name must not be blank.'),
				),
			),
			'q-2' => array(
				'notempty' => array(
					'rule' => array('response', 'notempty'),
					'message' => array('answer' => 'Shirt colour must not be blank.'),
				),
			),
		);

		if ($event['Event']['team_league'] == null) {
			// TODO: Add region and open roster validation, if necessary
		}

		return $validation;
	}

	function register($event, &$data) {
		if ($event['Event']['team_league'] == null) {
			return true;
		}

		if (!isset ($this->_controller->Team)) {
			$this->_controller->Team = ClassRegistry::init ('Team');
		}

		$team = array_merge(
			array(
				'league_id' => $event['Event']['team_league'],
			),
			$this->_extractAnswers ($data, array(
				'name' => -1,
				'shirt_colour' => -2,
				'region_preference' => -3,
				'open_roster' => -4,
			))
		);

		if ($this->_controller->Team->save ($team)) {
			$this->Roster = ClassRegistry::init ('TeamsPerson');
			$this->Roster->save (array(
				'team_id' => $this->_controller->Team->id,
				'person_id' => $this->_controller->Auth->user('id'),
				'status' => 'captain',
			));

			// TODO: Return validation errors?
			$data['Response'][] = array(
				'question_id' => -5,
				'answer' => $this->_controller->Team->id,
			);
			$this->_controller->_deleteTeamSessionData();
			return true;
		}
		return false;
	}

	function unregister($event, $data) {
		if ($event['Event']['team_league'] == null) {
			return true;
		}

		if (!isset ($this->_controller->Team)) {
			$this->_controller->Team = ClassRegistry::init ('Team');
		}

		$team = $this->_extractAnswer ($data, -5);
		$this->_controller->_deleteTeamSessionData();
		return $this->_controller->Team->delete ($team);
	}
}

?>
