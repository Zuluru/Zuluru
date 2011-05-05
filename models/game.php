<?php
class Game extends AppModel {
	var $name = 'Game';
	var $displayField = 'id';
	var $validate = array(
		'home_score' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 99),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Scores must be in the range 0-99',
				'on' => 'update',
			),
		),
		'away_score' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 99),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Scores must be in the range 0-99',
				'on' => 'update',
			),
		),
		'status' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.game_status'),
				'required' => false,
				'message' => 'You must select a valid status.',
				'on' => 'update',
			),
		),
		'round' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'TODO',
			),
		),
	);

	var $hasOne = array(
		'GameSlot' => array(
			'className' => 'GameSlot',
			'foreignKey' => 'game_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $belongsTo = array(
		'League' => array(
			'className' => 'League',
			'foreignKey' => 'league_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'HomeTeam' => array(
			'className' => 'Team',
			'foreignKey' => 'home_team',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'AwayTeam' => array(
			'className' => 'Team',
			'foreignKey' => 'away_team',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ApprovedBy' => array(
			'className' => 'Person',
			'foreignKey' => 'approved_by',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Allstar' => array(
			'className' => 'Allstar',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Incident' => array(
			'className' => 'Incident',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ScoreEntry' => array(
			'className' => 'ScoreEntry',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'SpiritEntry' => array(
			'className' => 'SpiritEntry',
			'foreignKey' => 'game_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ScoreReminderEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'primary_id',
			'dependent' => true,
			'conditions' => array('type' => array('email_score_reminder', 'email_approval_notice')),
		),
		'ScoreMismatchEmail' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'primary_id',
			'dependent' => true,
			'conditions' => array('type' => 'email_score_mismatch'),
		),
	);

	function _validateForScheduleEdit() {
		foreach (array('home_score', 'away_score', 'status') as $field) {
			unset ($this->validate[$field]);
		}
	}

	// saveAll doesn't save GameSlot records here (the hasOne relation
	// indicates to Cake that slots are supposed to be created for games,
	// rather than being created ahead of time and assigned to games).
	// So, we replicate the important bits of saveAll here.
	function _saveGames($games, $publish) {
		// Make sure that some other coordinator hasn't scheduled a game in a
		// different league on one of the unused slots.
		$slot_ids = Set::extract ('/GameSlot/id', $games);
		$game_ids = Set::extract ('/GameSlot/game_id', $games);
		$this->GameSlot->recursive = -1;
		$taken = $this->GameSlot->find('all', array('conditions' => array(
				'id' => $slot_ids,
				'game_id !=' => null,
				// Don't include game slots that are previously allocated to one of these games;
				// of course those will be taken, but it's okay!
				'NOT' => array('game_id' => $game_ids),
		)));
		if (!empty ($taken)) {
			$this->Session->setFlash(__('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true));
			return false;
		}

		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$begun = $db->begin($this);
		$this->_validateForScheduleEdit();
		foreach ($games as $game) {
			$game['GameSlot']['game_id'] = $game['id'];
			$game['published'] = $publish;
			if (!$this->save($game) ||
				!$this->GameSlot->save($game['GameSlot']))
			{
				if ($begun)
					$db->rollback($this);
				$this->Session->setFlash(__('Failed to save schedule changes!', true));
				return false;
			}
		}
		if ($begun)
			$db->commit($this);
		return true;
	}

	/**
	 * Adjust the indices of the ScoreEntry and SpiritEntry, so that
	 * the arrays are indexed by team_id instead of from zero.
	 *
	 */
	static function _adjustEntryIndices(&$game) {
		if (empty ($game)) {
			return;
		}
		if (Set::numeric (array_keys ($game))) {
			foreach (array_keys ($game) as $i) {
				Game::_adjustEntryIndices($game[$i]);
			}
			return;
		}

		foreach (array('ScoreEntry' => 'team_id', 'SpiritEntry' => 'team_id', 'ScoreReminderEmail' => 'secondary_id') as $model => $field) {
			if (array_key_exists ($model, $game)) {
				$keys = array_keys ($game[$model]);
				$new = array();
				foreach ($keys as $key) {
					$team = $game[$model][$key][$field];
					$new[$team] = $game[$model][$key];
				}
				$game[$model] = $new;
			}
		}
	}

	/**
	 * Retrieve score entry for given team. Assumes that _adjustEntryIndices has been called.
	 *
	 * @return mixed Array with the requested score entry, or false if the team hasn't entered a score yet.
	 */
	static function _get_score_entry ($game, $team_id)
	{
		if (array_key_exists ($team_id, $game['ScoreEntry'])) {
			return $game['ScoreEntry'][$team_id];
		}

		return false;
	}

	/**
	 * Retrieve spirit entry for given team. Assumes that _adjustEntryIndices has been called.
	 *
	 * @return mixed Array with the requested spirit entry, or false if the other team hasn't entered spirit yet.
	 */
	static function _get_spirit_entry ($game, $team_id)
	{
		if (array_key_exists ('SpiritEntry', $game) && array_key_exists ($team_id, $game['SpiritEntry'])) {
			return $game['SpiritEntry'][$team_id];
		}

		return false;
	}

	/**
	 * Compare two score entries
	 */
	static function _score_entries_agree ($one, $two)
	{
		if(
			($one['defaulted'] == 'us' && $two['defaulted'] == 'them')
			||
			($one['defaulted'] == 'them' && $two['defaulted'] == 'us')
		) {
			return true;
		}

		if(! (($one['defaulted'] == 'no') && ($two['defaulted'] == 'no'))) {
			return false;
		}

		if(($one['score_for'] == $two['score_against']) && ($one['score_against'] == $two['score_for']) ) {
			return true;
		}

		return false;
	}

	/**
	 * Calculate the expected win ratio.  Answer
	 * is always 0 <= x <= 1
	 */
	static function _calculate_expected_win ($rating1, $rating2) {
		$difference = $rating1 - $rating2;
		$power = pow(10, (0 - $difference) / 400);
		return ( 1 / ($power + 1) );
	}

	static function _is_finalized($game) {
		if (array_key_exists ('Game', $game)) {
			return (isset($game['Game']['home_score']) && isset($game['Game']['away_score']));
		} else {
			return (isset($game['home_score']) && isset($game['away_score']));
		}
	}
}
?>
