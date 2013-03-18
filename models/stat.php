<?php
class Stat extends AppModel {
	var $name = 'Stat';
	var $validate = array(
		'value' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Stats must be numeric',
			),
		),
	);

	var $belongsTo = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'StatType' => array(
			'className' => 'StatType',
			'foreignKey' => 'stat_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	static function applicable($stat_type, $position) {
		// If there's nothing specified, it's for everyone
		if (empty($stat_type['positions'])) {
			return true;
		}

		$positions = explode(',', $stat_type['positions']);
		$good = $bad = array();
		foreach ($positions as $p) {
			if ($p[0] == '!') {
				$bad[] = $p;
			} else {
				$good[] = $p;
			}
		}

		// If the player is one of the specified positions, it's for them
		if (in_array($position, $good)) {
			return true;
		}

		// If exclusions are specified and the player is NOT one of them,
		// if's for them
		if (!empty($bad) && !in_array("!$position", $bad)) {
			return true;
		}

		// It's not for them
		return false;
	}
}
?>