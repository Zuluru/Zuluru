<?php
class GameSlot extends AppModel {
	var $name = 'GameSlot';
	var $displayField = 'game_date';

	var $validate = array(
		'game_date' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid game date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'gameslot'),
				'message' => 'You must provide a valid game date.',
			),
		),
	);

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

	function getAvailable($division_id, $date, $is_tournament) {
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
					'Facility',
				),
		));

		$conditions = array('DivisionGameslotAvailability.division_id' => $division_id);
		if ($is_tournament) {
			$conditions['OR'] = array(
				'AND' => array(
					"GameSlot.game_date >= DATE_ADD('$date', INTERVAL -6 DAY)",
					"GameSlot.game_date <= DATE_ADD('$date', INTERVAL 6 DAY)",
					'GameSlot.game_date !=' => $date,
					'GameSlot.game_id' => null,
				),
				'GameSlot.game_date' => $date,
			);
		} else {
			$conditions['GameSlot.game_date'] = $date;
		}

		$game_slots = $this->find('all', array(
			'conditions' => $conditions,
			'joins' => $join,
		));

		foreach ($game_slots as $key => $slot) {
			if ($slot['Game']['division_id'] != $division_id && !empty ($slot['Game']['division_id'])) {
				unset ($game_slots[$key]);
			}
		}

		return $game_slots;
	}

	function affiliate($id) {
		return $this->Field->affiliate($this->field('field_id', array('GameSlot.id' => $id)));
	}

	static function compareTimeAndField ($a, $b) {
		if ($a['GameSlot']['game_date'] > $b['GameSlot']['game_date']) {
			return 1;
		} else if ($a['GameSlot']['game_date'] < $b['GameSlot']['game_date']) {
			return -1;
		}

		if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		} else if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		}

		if ($a['GameSlot']['display_game_end'] > $b['GameSlot']['display_game_end']) {
			return 1;
		} else if ($a['GameSlot']['display_game_end'] < $b['GameSlot']['display_game_end']) {
			return -1;
		}

		if ($a['Field']['long_name'] > $b['Field']['long_name']) {
			return 1;
		} else if ($a['Field']['long_name'] < $b['Field']['long_name']) {
			return -1;
		}

		return 0;
	}
}
?>