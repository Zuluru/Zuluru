<?php
/**
 * Base class for sport-specific functionality, primarily for stat-tracking.
 * This class defines default functions for some common stats that multiple
 * sports need, as well as providing some common utility functions that
 * derived classes need.
 */

class SportComponent extends Object
{
	var $rosters = null;
	var $stat_types = null;

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function validate_play($team, $play, $score_from, $details) {
		switch ($play) {
			case 'Start':
				if (!empty($details)) {
					return __('Game timer was already initialized.', true);
				}
		}
		return true;
	}

	/*
	 * Default functions for how many points the various outcomes are worth.
	 */
	function winValue() {
		return 2;
	}

	function tieValue() {
		return 1;
	}

	function lossValue() {
		return 0;
	}

	function _init_rosters($stats) {
		if ($this->rosters) {
			return;
		}

		if (!isset($this->_controller->Roster)) {
			$this->_controller->Roster = ClassRegistry::init ('TeamsPerson');
		}

		$teams = array_unique(Set::extract('/Stat/team_id', $stats));
		$this->rosters = array();
		foreach ($teams as $team) {
			$players = array_unique(Set::extract("/Stat[team_id=$team]/person_id", $stats));
			$this->rosters[$team] = $this->_controller->Roster->find('list', array(
					'conditions' => array(
						'team_id' => $team,
						'person_id' => $players,
					),
					'fields' => array('person_id', 'position'),
			));

			// Add subs, if any, as unspecified positions
			foreach ($players as $player) {
				if (!array_key_exists($player, $this->rosters[$team])) {
					$this->rosters[$team][$player] = 'unspecified';
				}
			}
		}
	}

	function _init_stat_types() {
		if (!$this->stat_types) {
			$stat_type = ClassRegistry::init('StatType');
			$this->stat_types = $stat_type->find('all', array(
					'contain' => array(),
					'conditions' => array(
						'StatType.sport' => $this->sport,
					),
			));
		}
	}

	function _stat_type_id($stat_name) {
		$this->_init_stat_types();
		$stat_type_id = Set::extract("/StatType[internal_name=$stat_name][type=entered]/id", $this->stat_types);
		if (empty($stat_type_id)) {
			$stat_type_id = Set::extract("/StatType[internal_name=$stat_name][type=game_calc]/id", $this->stat_types);
			if (empty($stat_type_id)) {
				trigger_error("Can't find stat type $stat_name in {$this->sport}!", E_USER_ERROR);
				return null;
			}
		}
		return array_pop($stat_type_id);
	}

	function _value($stat_type_id, $person_id, $stats) {
		$value = Set::extract("/Stat[stat_type_id=$stat_type_id][person_id=$person_id]/value", $stats);
		if (empty($value)) {
			// Since we're only dealing with people that have had at least some stats entered here,
			// we consider missing values to be zeros that were just not entered.
			return 0;
		}
		return array_pop($value);
	}

	function _game_sum($stat_type, $game, &$stats, $stat_names) {
		$this->_init_rosters($stats);
		$ids = array();
		foreach ($stat_names as $stat_name) {
			$ids[] = $this->_stat_type_id($stat_name);
		}

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = 0;
				foreach ($ids as $id) {
					$value += $this->_value($id, $person_id, $stats);
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function _game_ratio($stat_type, $game, &$stats, $numerator_id, $denominator_id) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$denominator = $this->_value($denominator_id, $person_id, $stats);
				if ($denominator) {
					$value = round($this->_value($numerator_id, $person_id, $stats) / $denominator, 3);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function _game_percent($stat_type, $game, &$stats, $numerator_id, $denominator_id) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$denominator = $this->_value($denominator_id, $person_id, $stats);
				if ($denominator) {
					$value = round($this->_value($numerator_id, $person_id, $stats) * 100 / $denominator, 1);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function _value_sum($stat_type_id, $person_id, $stats) {
		$values = Set::extract("/Stat[stat_type_id=$stat_type_id][person_id=$person_id]/value", $stats);
		if (empty($values)) {
			// Since we're only dealing with people that have had at least some stats entered here,
			// we consider missing values to be zeros that were just not entered.
			return 0;
		}
		return array_sum($values);
	}

	function _season_total($stat_type, &$stats) {
		$this->_init_rosters($stats);
		$base_stat_type_id = $this->_stat_type_id($stat_type['base']);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_value_sum($base_stat_type_id, $person_id, $stats);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function _season_avg($stat_type, &$stats) {
		$this->_init_rosters($stats);
		$base_stat_type_id = $this->_stat_type_id($stat_type['base']);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = round($this->_value_sum($base_stat_type_id, $person_id, $stats) / $this->_games_played($person_id, $stats), 1);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function _season_ratio($stat_type, &$stats, $numerator_id, $denominator_id) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$denominator = $this->_value_sum($denominator_id, $person_id, $stats);
				if ($denominator) {
					$value = round($this->_value_sum($numerator_id, $person_id, $stats) / $denominator, 3);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function _season_percent($stat_type, &$stats, $numerator_id, $denominator_id) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$denominator = $this->_value_sum($denominator_id, $person_id, $stats);
				if ($denominator) {
					$value = round($this->_value_sum($numerator_id, $person_id, $stats) * 100 / $denominator, 1);
				} else {
					$value = 0;
				}

				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function _games_played($person_id, $stats) {
		$games = array_unique(Set::extract("/Stat[person_id=$person_id]/game_id", $stats));
		return count($games);
	}

	/**
	 * For most sports, all players can be given a count of the wins, losses and ties they participated in.
	 * Sports like hockey and baseball where a specific goalie or pitcher will be credited with the win or
	 * loss will need to override these functions or specify a different handler.
	 */

	function wins_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_win($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (Stat::applicable($stat_type, $position)) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function wins_game_recalc($stat_type, $data) {
		foreach (array('home', 'away') as $team) {
			$this->_controller->Game->Stat->updateAll(
					array('Stat.value' => $this->_is_win($data, $data['Game']["{$team}_team"])),
					array('Stat.stat_type_id' => $stat_type['id'], 'Stat.game_id' => $data['Game']['id'], 'Stat.team_id' => $data['Game']["{$team}_team"])
			);
		}
	}

	function wins_season($stat_type, &$stats) {
		$win_id = $this->_stat_type_id('Wins');
		$tie_id = $this->_stat_type_id('Ties');
		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				// TODO: Make this "2" configurable for soccer, etc.
				$value = sprintf('%.03f', ($this->_value_sum($win_id, $person_id, $stats) +
								$this->_value_sum($tie_id, $person_id, $stats) / 2) /
								$this->_games_played($person_id, $stats));
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function games_season($stat_type, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->_games_played($person_id, $stats);
				if (Stat::applicable($stat_type, $position) || $value != 0) {
					$stats['Calculated'][$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	function _is_win($game, $team_id) {
		if (Game::_is_finalized($game)) {
			if (($team_id == $game['Game']['home_team'] && $game['Game']['home_score'] > $game['Game']['away_score']) ||
				($team_id == $game['Game']['away_team'] && $game['Game']['away_score'] > $game['Game']['home_score']))
			{
				return 1;
			} else {
				return 0;
			}
		} else if (array_key_exists($team_id, $game['ScoreEntry'])) {
			// Use our score entry
			if ($game['ScoreEntry'][$team_id]['status'] != 'in_progress' && $game['ScoreEntry'][$team_id]['score_for'] > $game['ScoreEntry'][$team_id]['score_against']) {
				return 1;
			} else {
				return 0;
			}
		} else if (!empty($game['ScoreEntry'])) {
			// Must be an entry from the other team
			$entry = array_shift($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress' && $entry['score_for'] < $entry['score_against']) {
				return 1;
			} else {
				return 0;
			}
		} else {
			// Return a 0 and trust that it will be corrected later
			return 0;
		}
	}

	function losses_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_loss($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (Stat::applicable($stat_type, $position)) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function losses_game_recalc($stat_type, $data) {
		foreach (array('home', 'away') as $team) {
			$this->_controller->Game->Stat->updateAll(
					array('Stat.value' => $this->_is_loss($data, $data['Game']["{$team}_team"])),
					array('Stat.stat_type_id' => $stat_type['id'], 'Stat.game_id' => $data['Game']['id'], 'Stat.team_id' => $data['Game']["{$team}_team"])
			);
		}
	}

	function _is_loss($game, $team_id) {
		if (Game::_is_finalized($game)) {
			if (($team_id == $game['Game']['home_team'] && $game['Game']['home_score'] < $game['Game']['away_score']) ||
				($team_id == $game['Game']['away_team'] && $game['Game']['away_score'] < $game['Game']['home_score']))
			{
				return 1;
			} else {
				return 0;
			}
		} else if (array_key_exists($team_id, $game['ScoreEntry'])) {
			// Use our score entry
			if ($game['ScoreEntry'][$team_id]['status'] != 'in_progress' && $game['ScoreEntry'][$team_id]['score_for'] < $game['ScoreEntry'][$team_id]['score_against']) {
				return 1;
			} else {
				return 0;
			}
		} else if (!empty($game['ScoreEntry'])) {
			// Must be an entry from the other team
			$entry = array_shift($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress' && $entry['score_for'] > $entry['score_against']) {
				return 1;
			} else {
				return 0;
			}
		} else {
			// Return a 0 and trust that it will be corrected later
			return 0;
		}
	}

	function ties_game($stat_type, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_tie($game, $team_id);
			foreach ($roster as $person_id => $position) {
				if (Stat::applicable($stat_type, $position)) {
					$stats['Stat'][] = array(
						'game_id' => $game['Game']['id'],
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type['id'],
						'value' => $value,
					);
				}
			}
		}
	}

	function ties_game_recalc($stat_type, $data) {
		foreach (array('home', 'away') as $team) {
			$this->_controller->Game->Stat->updateAll(
					array('Stat.value' => $this->_is_tie($data, $data['Game']["{$team}_team"])),
					array('Stat.stat_type_id' => $stat_type['id'], 'Stat.game_id' => $data['Game']['id'], 'Stat.team_id' => $data['Game']["{$team}_team"])
			);
		}
	}

	function _is_tie($game, $team_id) {
		if (Game::_is_finalized($game)) {
			if ($game['Game']['home_score'] == $game['Game']['away_score']) {
				return 1;
			} else {
				return 0;
			}
		} else if (!empty($game['ScoreEntry'])) {
			$entry = array_shift($game['ScoreEntry']);
			if ($entry['status'] != 'in_progress' && $entry['score_for'] == $entry['score_against']) {
				return 1;
			} else {
				return 0;
			}
		} else {
			// Return a 0 and trust that it will be corrected later
			return 0;
		}
	}

	/**
	 *
	 * Sum functions
	 *
	 */

	function null_sum() {
		return '';
	}

	function minutes_sum($minutes) {
		$ret = 0;
		foreach ($minutes as $m) {
			if (strpos($m, '.') !== false) {
				list($m,$s) = explode('.', $m);
			} else {
				$s = 0;
			}
			$ret += $m * 60 + $s;
		}
		return sprintf('%d.%02d', floor($ret / 60), $ret % 60);
	}

	/**
	 *
	 * Formatter functions
	 *
	 */

	function minutes_format($value) {
		$minutes = floor($value);
		$seconds = floor(($value - $minutes) * 100);
		return sprintf('%d:%02d', $minutes, $seconds);
	}

	/**
	 *
	 * Validation helpers
	 *
	 */	

	function validate_team_score($stat) {
		$ret = array();
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() > team_score) alert_msg += 'The number of {$stat['name']} entered is more than the score.\\n';";
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() < team_score) confirm_msg += 'The number of {$stat['name']} entered is less than the score.\\n';";
		return $ret;
	}

	function validate_team_score_two($stat) {
		$ret = array();
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() > team_score * 2) alert_msg += 'The number of {$stat['name']} entered is more than the score.\\n';";
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() < team_score * 2) confirm_msg += 'The number of {$stat['name']} entered is less than the score.\\n';";
		return $ret;
	}

	function validate_opponent_score($stat) {
		$ret = array();
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() > opponent_score) alert_msg += 'The number of {$stat['name']} entered is more than the score.\\n';";
		$ret[] = "if (jQuery('#team_' + team_id + ' th.stat_{$stat['id']}').html() < opponent_score) confirm_msg += 'The number of {$stat['name']} entered is less than the score.\\n';";
		return $ret;
	}
}

?>
