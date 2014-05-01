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
				'on' => 'create',
			),
		),
	);

	var $belongsTo = array(
		'Field' => array(
			'className' => 'Field',
			'foreignKey' => 'field_id',
		),
	);

	var $hasMany = array(
		'DivisionGameslotAvailability' => array(
			'className' => 'DivisionGameslotAvailability',
			'foreignKey' => 'game_slot_id',
			'dependent' => false,
		),
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_slot_id',
			'dependent' => false,
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

	function getAvailable($divisions, $date, $is_tournament, $allow_double_booking, $multi_day) {
		// Find available slots
		$join = array( array(
				'table' => "{$this->tablePrefix}division_gameslot_availabilities",
				'alias' => 'DivisionGameslotAvailability',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'DivisionGameslotAvailability.game_slot_id = GameSlot.id',
		));

		if (!is_array($divisions)) {
			$divisions = array($divisions);
		}
		if ($is_tournament) {
			$conditions = array('OR' => array(
				'AND' => array(
					"GameSlot.game_date >= DATE_ADD('$date', INTERVAL -6 DAY)",
					"GameSlot.game_date <= DATE_ADD('$date', INTERVAL 6 DAY)",
					'GameSlot.game_date !=' => $date,
					'GameSlot.assigned' => false,
				),
				'GameSlot.game_date' => $date,
			));
		} else if ($multi_day) {
			$first_day = Configure::read('organization.first_day');
			$offset = (6 + $first_day - date('N', strtotime($date))) % 7;
			$conditions = array(
				'GameSlot.game_date >=' => $date,
				"GameSlot.game_date <= DATE_ADD('$date', INTERVAL $offset DAY)",
			);
		} else {
			$conditions = array('GameSlot.game_date' => $date);
		}

		$game_slots = array();
		foreach ($divisions as $division_id) {
			$conditions['DivisionGameslotAvailability.division_id'] = $division_id;

			$game_slots[$division_id] = $this->find('all', array(
				'conditions' => $conditions,
				'joins' => $join,
				'contain' => array (
					'Game',
					'Field' => array(
						'Facility',
					),
				),
			));

			if (!$allow_double_booking) {
				// Remove any game slots that are exclusively assigned to a division
				// that we are not interested in
				foreach ($game_slots[$division_id] as $key => $slot) {
					$assigned_to_divisions = array_unique(Set::extract('/Game/division_id', $slot));
					$intersect = array_intersect($assigned_to_divisions, $divisions);
					if (!empty($assigned_to_divisions) && empty($intersect)) {
						unset ($game_slots[$division_id][$key]);
					}
				}
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