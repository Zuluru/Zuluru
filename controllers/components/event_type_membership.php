<?php

/**
 * Derived class for implementing functionality for membership events.
 */

class EventTypeMembershipComponent extends EventTypeComponent
{
	function configurationFields() {
		return array('membership_begins', 'membership_ends', 'membership_type');
	}

	function configurationFieldsElement() {
		return 'membership';
	}

	function configurationFieldsValidation() {
		return array(
			'membership_begins' => array(
				'date' => array(
					'rule' => array('date'),
					'message' => 'You must select a valid beginning date.',
				),
			),
			'membership_ends' => array(
				'date' => array(
					'rule' => array('date'),
					'message' => 'You must select a valid ending date.',
				),
				'greater' => array(
					'rule' => array('greaterdate', 'membership_begins'),
					'message' => 'The membership ends date must be after the membership begins date.',
				),
			),
			'membership_type' => array(
				'inlist' => array(
					'rule' => array('inconfig', 'options.membership_types'),
					'message' => 'You must select a valid membership type.',
				),
			),
		);
	}

	// Handle the type for any field in configurationFields above that
	// requires special $model->deconstruct handling.
	function getColumnType($column) {
		if (in_array ($column, array('membership_begins', 'membership_ends'))) {
			return 'date';
		}
		return null;
	}

	function longDescription($data) {
		return "{$data['Event']['name']}: Valid from {$data['Event']['membership_begins']} to {$data['Event']['membership_ends']}";
	}
}

?>