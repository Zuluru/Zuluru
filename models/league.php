<?php
class League extends AppModel {
	var $name = 'League';
	var $displayField = 'name';

	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'A valid league name must be entered.',
			),
		),
		'open' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date for the first game.',
			),
		),
		'close' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date for the last game.',
			),
		),
		'tier' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.tier'),
				'message' => 'You must select a valid tier.',
			),
		),
		'ratio' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.ratio'),
				'message' => 'You must select a valid gender ratio.',
			),
		),
		'roster_deadline' => array(
			'date' => array(
				'rule' => array('date'),
				'allowEmpty' => true,
				'message' => 'You must provide a valid roster deadline.',
			),
		),
		'roster_rule' => array(
			'valid' => array(
				'rule' => array('rule'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'There is an error in the rule syntax.',
			),
		),
		'schedule_type' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.schedule_type'),
				'message' => 'You must select a valid schedule type.',
			),
		),
		'display_sotg' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.sotg_display'),
				'message' => 'You must select a valid spirit display method.',
			),
		),
		'sotg_questions' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.spirit_questions'),
				'message' => 'You must select a valid spirit questionnaire.',
			),
		),
		'numeric_sotg' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not numeric spirit entry is enabled.',
			),
		),
		'allstars' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.allstar'),
				'message' => 'You must select a valid allstar entry option.',
			),
		),
		'exclude_teams' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.enable'),
				'message' => 'You must select whether or not teams can be excluded.',
			),
		),
		'email_after' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
			),
		),
		'finalize_after' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'allowEmpty' => true,
			),
		),
	);

	var $hasMany = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'league_id',
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
		'LeagueGameslotAvailability' => array(
			'className' => 'LeagueGameslotAvailability',
			'foreignKey' => 'league_id',
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
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'league_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'leagues_people',
			'foreignKey' => 'league_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Day' => array(
			'className' => 'Day',
			'joinTable' => 'leagues_days',
			'foreignKey' => 'league_id',
			'associationForeignKey' => 'day_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
	);

	function _afterFind ($record) {
		$long_name = '';
		if (array_key_exists ('name', $record[$this->alias])) {
			$long_name = $record[$this->alias]['name'];
		}
		if (array_key_exists ('tier', $record[$this->alias]) && $record[$this->alias]['tier'] != 0) {
			$long_name .= __('Tier', true) . ' ' . $record[$this->alias]['tier'];
		}

		// Add the year, if it's not already part of the name
		if (array_key_exists ('open', $record[$this->alias])) {
			$year = date ('Y', strtotime ($record[$this->alias]['open']));
			if (strpos ($long_name, $year) === false) {
				// TODO: Add closing year, if different than opening
				$long_name = "$year $long_name";
			}
			// TODO: Add the season "enum" back into the database? Calculate based on opening date?
			$record[$this->alias]['long_season'] = $year;
		}

		$record[$this->alias]['long_name'] = $long_name;
		return $record;
	}

	// TODO: Add validation details before rendering, so required fields are properly highlighted
	function beforeValidate() {
		if (array_key_exists ('schedule_type', $this->data['League'])) {
			$league_obj = AppController::_getComponent ('LeagueType', $this->data['League']['schedule_type']);
			$this->validate = array_merge ($this->validate, $league_obj->schedulingFieldsValidation());
		}
		if (!Configure::read('scoring.allstars')) {
			unset ($this->validate['allstars']);
		}
		return true;
	}

	function readByDate($date) {
		// Our database has Sunday as 1, but date('w') gives it as 0
		$day = date('w', strtotime ($date)) + 1;

		$this->contain('Day');
		$leagues = $this->find('all', array(
				'conditions' => array('OR' => array(
					'League.is_open' => true,
					'League.open > CURDATE()',
				)),
		));
		return Set::extract("/Day[id=$day]/..", $leagues);
	}

	function readByPlayerId($id, $open = true, $teams = false) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$conditions = array(
			'LeaguesPerson.person_id' => $id,
			'League.is_open' => $open,
		);

		$this->recursive = -1;
		$leagues = $this->find('all', array(
			'conditions' => $conditions,
			// By grouping, we get only one record per team, regardless
			// of how many days the league may operate on. Without this,
			// a league that runs on two nights would generate two records
			// here. Nothing that uses this function needs the full list
			// of nights, so it's okay.
			'group' => 'League.id',
			'order' => 'LeaguesDay.day_id, League.open',
			'fields' => array(
				'League.*',
				'LeaguesPerson.person_id', 'LeaguesPerson.position',
				'LeaguesDay.day_id',
			),
			'contain' => ($teams ? 'Team' : null),
			'joins' => array(
				array(
					'table' => "{$this->tablePrefix}leagues_people",
					'alias' => 'LeaguesPerson',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'LeaguesPerson.league_id = League.id',
				),
				array(
					'table' => "{$this->tablePrefix}leagues_days",
					'alias' => 'LeaguesDay',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'LeaguesDay.league_id = League.id',
				),
			),
		));

		return $leagues;
	}

	/**
	 * Find all leagues according to the provided conditions, and sort them by
	 * the day of the week they operate on. In the case of multiple days, use
	 * the first day.
	 *
	 * Parameters are the same as the standard find function.
	 */
	function findSortByDay($conditions = null, $fields = array(), $order = null, $recursive = null) {
		// 'find' has a bit of bipolar disorder, and behaves differently based
		// on the types of the parameters. We need to simulate that here to
		// correctly add the 'contain' condition.
		if (is_array($conditions)) {
			if (array_key_exists ('contain', $conditions)) {
				$conditions['contain'][] = 'Day';
			} else {
				$conditions['contain'] = array('Day');
			}
		} else {
			if (array_key_exists ('contain', $fields)) {
				$fields['contain'][] = 'Day';
			} else {
				$fields['contain'] = array('Day');
			}
		}

		$leagues = $this->find ($conditions, $fields, $order, $recursive);

		usort ($leagues, array('League', 'compareDay'));

		return $leagues;
	}

	static function compareDay ($a, $b) {
		// If the league open dates are far apart, we use that
		$a_open = strtotime ($a['League']['open']);
		$b_open = strtotime ($b['League']['open']);
		if (abs ($a_open - $b_open) > 5 * 7 * 24 * 60 * 60) {
			if ($a_open > $b_open) {
				return 1;
			} else if ($a_open < $b_open) {
				return -1;
			}
		}

		$a_days = Set::extract ('/Day/id', $a);
		$b_days = Set::extract ('/Day/id', $b);
		if (empty ($a_days)) {
			$a_min = 0;
		} else {
			$a_min = min($a_days);
			// Make Sunday the last day of the week instead of first, for playoff ordering
			if ($a_min == 1)
				$a_min = 8;
		}
		if (empty ($b_days)) {
			$b_min = 0;
		} else {
			$b_min = min($b_days);
			if ($b_min == 1)
				$b_min = 8;
		}

		if ($a_min > $b_min) {
			return 1;
		} else if ($a_min < $b_min) {
			return -1;
		}
		// Leagues on the same day use the id to sort. Assumption is that
		// higher-level leagues are created first.
		return $a['League']['id'] > $b['League']['id'];
	}

	static function compareDateAndField ($a, $b) {
		if ($a['GameSlot']['game_date'] < $b['GameSlot']['game_date']) {
			return -1;
		} else if ($a['GameSlot']['game_date'] > $b['GameSlot']['game_date']) {
			return 1;
		}

		if ($a['GameSlot']['game_start'] < $b['GameSlot']['game_start']) {
			return -1;
		} else if ($a['GameSlot']['game_start'] > $b['GameSlot']['game_start']) {
			return 1;
		}

		if (array_key_exists ('name', $a) && !empty ($a['name'])) {
			if ($a['name'] < $b['name']) {
				return -1;
			} else if ($a['name'] > $b['name']) {
				return 1;
			}
		}

		if ($a['GameSlot']['field_id'] < $b['GameSlot']['field_id']) {
			return -1;
		}
		return 1;
	}
}
?>