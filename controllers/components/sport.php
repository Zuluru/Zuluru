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

	function _init_rosters($stats) {
		if ($this->rosters) {
			return;
		}

		$teams = array_unique(Set::extract('/Stat/team_id', $stats));
		$this->rosters = array();
		foreach ($teams as $team) {
			$this->rosters[$team] = array_unique(Set::extract("/Stat[team_id=$team]/person_id", $stats));
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
		$stat_type_id = Set::extract("/StatType[name=$stat_name][type=entered]/id", $this->stat_types);
		if (empty($stat_type_id)) {
			$stat_type_id = Set::extract("/StatType[name=$stat_name][type=game_calc]/id", $this->stat_types);
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

	function _game_sum($stat_type_id, $game, &$stats, $stat_names) {
		$this->_init_rosters($stats);

		$ids = array();
		foreach ($stat_names as $stat_name) {
			$ids[] = $this->_stat_type_id($stat_name);
		}

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id) {
				$value = 0;
				foreach ($ids as $id) {
					$value += $this->_value($id, $person_id, $stats);
				}

				$stats['Stat'][] = array(
					'game_id' => $game['Game']['id'],
					'team_id' => $team_id,
					'person_id' => $person_id,
					'stat_type_id' => $stat_type_id,
					'value' => $value,
				);
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
			foreach ($roster as $person_id) {
				$value = $this->_value_sum($base_stat_type_id, $person_id, $stats);
				$stats['Calculated'][$person_id][$stat_type['id']] = $value;
			}
		}
	}

	function _season_avg($stat_type, &$stats) {
		$this->_init_rosters($stats);
		$base_stat_type_id = $this->_stat_type_id($stat_type['base']);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id) {
				$value = round($this->_value_sum($base_stat_type_id, $person_id, $stats) / $this->_games_played($person_id, $stats), 1);
				$stats['Calculated'][$person_id][$stat_type['id']] = $value;
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

	function wins_game($stat_type_id, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_win($game, $team_id);
			foreach ($roster as $person_id) {
				$stats['Stat'][] = array(
					'game_id' => $game['Game']['id'],
					'team_id' => $team_id,
					'person_id' => $person_id,
					'stat_type_id' => $stat_type_id,
					'value' => $value,
				);
			}
		}
	}

	function wins_season($stat_type_id, &$stats) {
		$win_id = $this->_stat_type_id('Wins');
		$tie_id = $this->_stat_type_id('Ties');

		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id) {
				// TODO: Make this "2" configurable for soccer, etc.
				$value = sprintf('%.03f', ($this->_value_sum($win_id, $person_id, $stats) +
								$this->_value_sum($tie_id, $person_id, $stats) / 2) /
								$this->_games_played($person_id, $stats));
				$stats['Calculated'][$person_id][$stat_type_id] = $value;
			}
		}
	}

	function games_season($stat_type_id, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id) {
				$stats['Calculated'][$person_id][$stat_type_id] = $this->_games_played($person_id, $stats);
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
		} else {
			pr($team_id);
			pr($game['ScoreEntry']);
			trigger_error('Handle score entries', E_USER_ERROR);
		}
	}

	function losses_game($stat_type_id, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_loss($game, $team_id);
			foreach ($roster as $person_id) {
				$stats['Stat'][] = array(
					'game_id' => $game['Game']['id'],
					'team_id' => $team_id,
					'person_id' => $person_id,
					'stat_type_id' => $stat_type_id,
					'value' => $value,
				);
			}
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
		} else {
			pr($team_id);
			pr($game['ScoreEntry']);
			trigger_error('Handle score entries', E_USER_ERROR);
		}
	}

	function ties_game($stat_type_id, $game, &$stats) {
		$this->_init_rosters($stats);
		foreach ($this->rosters as $team_id => $roster) {
			$value = $this->_is_tie($game, $team_id);
			foreach ($roster as $person_id) {
				$stats['Stat'][] = array(
					'game_id' => $game['Game']['id'],
					'team_id' => $team_id,
					'person_id' => $person_id,
					'stat_type_id' => $stat_type_id,
					'value' => $value,
				);
			}
		}
	}

	function _is_tie($game, $team_id) {
		if (Game::_is_finalized($game)) {
			if ($game['Game']['home_score'] == $game['Game']['away_score']) {
				return 1;
			} else {
				return 0;
			}
		} else {
			pr($team_id);
			pr($game['ScoreEntry']);
			trigger_error('Handle score entries', E_USER_ERROR);
		}
	}
}

?>
