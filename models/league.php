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
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
			),
		),
		'sport' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.sport'),
				'message' => 'You must select a valid sport.',
			),
		),
		'season' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.season'),
				'message' => 'You must select a valid season.',
			),
		),
		'schedule_attempts' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Enter a valid number of schedules to try before picking the best.',
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
		'expected_max_score' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Enter the highest score that you expect the winning team to reasonably reach.',
			),
		),
		'tie_breaker' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.tie_breaker_spirit'),
				'message' => 'You must select a valid tie breaker method.',
			),
		),
	);

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $hasMany = array(
		'Division' => array(
			'className' => 'Division',
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
		'StatType' => array(
			'className' => 'StatType',
			'joinTable' => 'leagues_stat_types',
			'foreignKey' => 'league_id',
			'associationForeignKey' => 'stat_type_id',
			'unique' => true,
			'order' => 'StatType.sort',
		),
	);

	function _afterFind ($record) {
		$this->_addNames($record[$this->alias]);
		return $record;
	}

	static function _addNames(&$league) {
		$long_name = '';

		if (array_key_exists ('name', $league)) {
			$long_name = $league['name'];
		}

		// Add the season, if it's not already part of the name
		if (array_key_exists ('season', $league) && $league['season'] != 'None') {
			if (strpos ($long_name, $league['season']) === false) {
				$long_name = $league['season'] . ' ' . $long_name;
			}
		}

		// Add the sport, if there are multiple options
		if (array_key_exists ('sport', $league) && count(Configure::read('options.sport')) > 1) {
			$long_name .= ' ' . Inflector::humanize($league['sport']);
		}

		// Add the year, if it's not already part of the name
		$full_name = $long_name;
		if (array_key_exists ('open', $league) && $league['open'] != '0000-00-00') {
			$year = date ('Y', strtotime ($league['open']));
			if (strpos ($full_name, $year) === false) {
				// TODO: Add closing year, if different than opening
				$full_name = $year . ' ' . $full_name;
			}
			if (array_key_exists('season', $league) && $league['season'] != 'None') {
				$league['long_season'] = "$year {$league['season']}";
			} else {
				$league['long_season'] = $year;
			}
		} else if (array_key_exists('season', $league)) {
			$league['long_season'] = $league['season'];
		}

		$league['long_name'] = trim($long_name);
		$league['full_name'] = trim($full_name);
	}

	static function compareLeagueAndDivision ($a, $b) {
		if (array_key_exists('League', $a)) {
			$a_league = $a['League'];
			$b_league = $b['League'];
		} else {
			$a_league = $a['Division']['League'];
			$b_league = $b['Division']['League'];
		}

		if (array_key_exists('Affiliate', $a_league)) {
			$a_affiliate = $a_league['Affiliate'];
			$b_affiliate = $b_league['Affiliate'];
		} else {
			$a_affiliate = $a['Affiliate'];
			$b_affiliate = $b['Affiliate'];
		}

		// If they are different affiliates, we use that
		if ($a_affiliate['name'] > $b_affiliate['name']) {
			return 1;
		} else if ($a_affiliate['name'] < $b_affiliate['name']) {
			return -1;
		}

		// If they are different sports, we use that
		if ($a_league['sport'] > $b_league['sport']) {
			return 1;
		} else if ($a_league['sport'] < $b_league['sport']) {
			return -1;
		}

		// If they are in different years, we use that
		if (date('Y', strtotime($a_league['open'])) > date('Y', strtotime($b_league['open']))) {
			return 1;
		} else if (date('Y', strtotime($a_league['open'])) < date('Y', strtotime($b_league['open']))) {
			return -1;
		}

		// If they are in different seasons, we use that
		$seasons = array_flip(array_values(Configure::read('options.season')));
		$a_season = $seasons[$a_league['season']];
		$b_season = $seasons[$b_league['season']];
		if ($a_season > $b_season) {
			return 1;
		} else if ($a_season < $b_season) {
			return -1;
		}

		if (array_key_exists('Division', $a)) {
			if (array_key_exists('id', $a['Division'])) {
				$a_schedule_type = Configure::read("schedule_type.{$a['Division']['schedule_type']}");
				$b_schedule_type = Configure::read("schedule_type.{$b['Division']['schedule_type']}");
			} else {
				$a_schedule_type = $b_schedule_type = PHP_INT_MAX;
				foreach ($a['Division'] as $division) {
					$a_schedule_type = min($a_schedule_type, Configure::read("schedule_type.{$division['schedule_type']}"));
				}
				foreach ($b['Division'] as $division) {
					$b_schedule_type = min($b_schedule_type, Configure::read("schedule_type.{$division['schedule_type']}"));
				}
			}

			// Compare the schedule type, so "regular" leagues are grouped before tournaments
			if ($a_schedule_type > $b_schedule_type) {
				return 1;
			} else if ($a_schedule_type < $b_schedule_type) {
				return -1;
			}

			// For tournaments, use the league open date
			if ($a_schedule_type == SCHEDULE_TYPE_TOURNAMENT) {
				if ($a_league['open'] > $b_league['open']) {
					return 1;
				} else if ($a_league['open'] < $b_league['open']) {
					return -1;
				}
			}

			if (array_key_exists('season_days', $a['Division'])) {
				$a_days = $a['Division']['season_days'];
			} else if (array_key_exists('Day', $a)) {
				// Set::extract fails when there's a numeric key at the top level,
				// like when we have a count in the statistics page, so we use this
				// method instead of Set::extract('/Day/id', $a)
				$a_days = Set::extract('/id', $a['Day']);
			} else {
				$a_days = array();
				foreach (Configure::read('schedule_type') as $schedule_type => $sort_order) {
					if ($sort_order == SCHEDULE_TYPE_LEAGUE) {
						$a_days = array_merge($a_days, Set::extract("/Division[schedule_type=$schedule_type]/Day/id", $a));
					}
				}
			}

			if (array_key_exists('season_days', $b['Division'])) {
				$b_days = $b['Division']['season_days'];
			} else if (array_key_exists('Day', $b)) {
				$b_days = Set::extract('/id', $b['Day']);
			} else {
				$b_days = array();
				foreach (Configure::read('schedule_type') as $schedule_type => $sort_order) {
					if ($sort_order == 1) {
						$b_days = array_merge($b_days, Set::extract("/Division[schedule_type=$schedule_type]/Day/id", $b));
					}
				}
			}

			if (empty ($a_days)) {
				$a_min = 0;
			} else {
				$a_min = min($a_days);
			}
			if (empty ($b_days)) {
				$b_min = 0;
			} else {
				$b_min = min($b_days);
			}

			if ($a_min > $b_min) {
				return 1;
			} else if ($a_min < $b_min) {
				return -1;
			}
		}

		if ($a_league['name'] > $b_league['name']) {
			return 1;
		} else if ($a_league['name'] < $b_league['name']) {
			return -1;
		}

		if (!empty($a['Division']['id'])) {
			// Divisions on the same day use the id to sort. Assumption is that
			// higher-level divisions are created first.
			return $a['Division']['id'] > $b['Division']['id'];
		}

		return $a_league['id'] > $b_league['id'];
	}

	static function hasSpirit($league) {
		if (!Configure::read('feature.spirit')) {
			return false;
		} else if (array_key_exists('League', $league)) {
			$league = $league['League'];
		} else if (array_key_exists('Division', $league)) {
			if (array_key_exists('League', $league['Division'])) {
				$league = $league['Division']['League'];
			} else {
				return false;
			}
		}
		return ($league['numeric_sotg'] || $league['sotg_questions'] != 'none');
	}

	static function hasStats($league) {
		if (!Configure::read('scoring.stat_tracking')) {
			return false;
		} else if (array_key_exists('League', $league)) {
			$league = $league['League'];
		} else if (array_key_exists('Division', $league)) {
			if (array_key_exists('League', $league['Division'])) {
				$league = $league['Division']['League'];
			} else {
				return false;
			}
		}
		return ($league['stat_tracking'] != 'never');
	}

	function readFinalizedGames($league) {
		$teams = Set::extract ('/Division/Team/id', $league);
		if (empty($teams)) {
			return array();
		}

		$this->Division->Game->contain (array('GameSlot', 'HomeTeam', 'AwayTeam'));
		$games = $this->Division->Game->find('all', array(
				'conditions' => array(
					'OR' => array(
						'home_team' => $teams,
						'away_team' => $teams,
					),
					'home_score !=' => null,
					'away_score !=' => null,
				),
		));

		// Sort games by date, time and field
		usort ($games, array ('Game', 'compareDateAndField'));
		return $games;
	}

	function affiliate($id) {
		return $this->field('affiliate_id', array('League.id' => $id));
	}
}
?>
