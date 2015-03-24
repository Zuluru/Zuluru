<?php

/**
 * Derived class for implementing functionality for individual signup to team events.
 */

class EventTypeIndividualComponent extends EventTypeComponent
{
	function configurationFields() {
		return array('level_of_play');
	}

	function configurationFieldsElement() {
		return 'individual';
	}

	// ID numbers don't much matter, but they can't be duplicated between event types,
	// and they can't ever be changed, because they're in the database.
	function registrationFields($event, $user_id, $for_output = false) {
		$fields = array();
		if (Configure::read('profile.shirt_size') == PROFILE_REGISTRATION) {
			$fields = array(
				array(
					'type' => 'group_start',
					'question' => __('Player Details', true),
				),
				array(
					'id' => SHIRT_SIZE,
					'type' => 'select',
					'question' => __('Shirt Size', true),
					'empty' => '---',
					'options' => Configure::read('options.shirt_size'),
					'required' => true,
				),
				array(
					'type' => 'group_end',
				),
			);
		}
		return $fields;
	}

	function registrationFieldsValidation($event, $for_edit = false) {
		$validation = array();
		if (Configure::read('profile.shirt_size') == PROFILE_REGISTRATION) {
			// 'message' must go into an array with key = 'q{answer}' because
			// field names when we display this are like Response.q{id}.answer
			$validation = array(
				'q' . SHIRT_SIZE => array(
					'inlist' => array(
						'rule' => array('inconfig', 'options.shirt_size'),
						'message' => array('answer' => 'You must select a valid shirt size.'),
					),
				),
			);
		}
		return $validation;
	}

	function register($event, &$data) {
		if (Configure::read('profile.shirt_size') == PROFILE_REGISTRATION) {
			$profile = $this->_extractAnswers ($data, array(
					'shirt_size' => SHIRT_SIZE,
			));
			if (!empty($profile)) {
				$this->_controller->Person->id = $this->_controller->UserCache->currentId();
				// If it fails, it fails. We're not going to reject the registration because of it.
				$this->_controller->Person->save($profile);
			}
		}

		return parent::register($event, $data);
	}
}

?>
