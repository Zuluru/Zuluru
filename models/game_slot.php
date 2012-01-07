<?php
class GameSlot extends AppModel {
	var $name = 'GameSlot';
	var $displayField = 'game_date';

	var $belongsTo = array(
		'Field' => array(
			'className' => 'Field',
			'foreignKey' => 'field_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'DivisionGameslotAvailability' => array(
			'className' => 'DivisionGameslotAvailability',
			'foreignKey' => 'game_slot_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);

	function _afterFind($record) {
		if (array_key_exists ('game_end', $record['GameSlot'])) {
			if ($record['GameSlot']['game_end'] === null) {
				$record['GameSlot']['display_game_end'] = local_sunset_for_date ($record['GameSlot']['game_date']);
			} else {
				$record['GameSlot']['display_game_end'] = $record['GameSlot']['game_end'];
			}
		}
		return $record;
	}

	function getAvailable($division_id, $date) {
		// Find available slots
		$join = array( array(
				'table' => "{$this->tablePrefix}division_gameslot_availabilities",
				'alias' => 'DivisionGameslotAvailability',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'DivisionGameslotAvailability.game_slot_id = GameSlot.id',
		));

		$this->contain (array (
				'Game',
				'Field' => array(
					'ParentField',
				),
		));
		$game_slots = $this->find('all', array(
			'conditions' => array('DivisionGameslotAvailability.division_id' => $division_id, 'GameSlot.game_date' => $date),
			'joins' => $join,
		));

		foreach ($game_slots as $key => $slot) {
			if ($slot['Game']['division_id'] != $division_id && !empty ($slot['Game']['division_id'])) {
				unset ($game_slots[$key]);
			}
		}

		return $game_slots;
	}
}
?>