<?php

/**
 * Derived class for implementing functionality for the RPI ratings calculator.
 */

class RatingsRpiComponent extends RatingsComponent
{
	var $per_game_ratings = false;
	var $iterative = false;
	var $results = array();
	var $vs = array();

	function _initializeRatings($league, &$division, $games) {
		// Build some counters to make the later calculations trivial
		foreach ($games as $game) {
			if ($game['Game']['type'] == SEASON_GAME && $this->game_obj->_is_finalized ($game) && $game['Game']['status'] != 'rescheduled') {
				if (!array_key_exists($game['Game']['home_team'], $this->results)) {
					$this->results[$game['Game']['home_team']] = array('games' => 0, 'wins' => 0);
				}
				if (!array_key_exists($game['Game']['away_team'], $this->results)) {
					$this->results[$game['Game']['away_team']] = array('games' => 0, 'wins' => 0);
				}

				if (!array_key_exists($game['Game']['home_team'], $this->vs)) {
					$this->vs[$game['Game']['home_team']] = array();
				}
				if (!array_key_exists($game['Game']['away_team'], $this->vs)) {
					$this->vs[$game['Game']['away_team']] = array();
				}

				if (!array_key_exists($game['Game']['home_team'], $this->vs[$game['Game']['away_team']])) {
					$this->vs[$game['Game']['away_team']][$game['Game']['home_team']] = array('games' => 0, 'wins' => 0);
				}
				if (!array_key_exists($game['Game']['away_team'], $this->vs[$game['Game']['home_team']])) {
					$this->vs[$game['Game']['home_team']][$game['Game']['away_team']] = array('games' => 0, 'wins' => 0);
				}

				if (strpos($game['Game']['status'], 'default') !== false && !Configure::read('default_transfer_ratings')) {
					// We might just ignore defaults
				} else {
					++ $this->results[$game['Game']['home_team']]['games'];
					++ $this->results[$game['Game']['away_team']]['games'];
					++ $this->vs[$game['Game']['home_team']][$game['Game']['away_team']]['games'];
					++ $this->vs[$game['Game']['away_team']][$game['Game']['home_team']]['games'];
					if ($game['Game']['home_score'] > $game['Game']['away_score']) {
						++ $this->results[$game['Game']['home_team']]['wins'];
						++ $this->vs[$game['Game']['home_team']][$game['Game']['away_team']]['wins'];
					} else if ($game['Game']['home_score'] < $game['Game']['away_score']) {
						++ $this->results[$game['Game']['away_team']]['wins'];
						++ $this->vs[$game['Game']['away_team']][$game['Game']['home_team']]['wins'];
					} else {
						$this->results[$game['Game']['home_team']]['wins'] += 0.5;
						$this->results[$game['Game']['away_team']]['wins'] += 0.5;
						$this->vs[$game['Game']['home_team']][$game['Game']['away_team']]['wins'] += 0.5;
						$this->vs[$game['Game']['away_team']][$game['Game']['home_team']]['wins'] += 0.5;
					}
				}
			}
		}

		foreach (array_keys($division['Team']) as $team_id) {
			$division['Team'][$team_id]['current_rating'] = $division['Team'][$team_id]['initial_rating'];
		}
	}

	function _recalculateRatings(&$division, $games) {
		foreach ($division['Team'] as $key => $team) {
			if (!array_key_exists($team['id'], $this->results)) {
				// If they haven't played yet, give them a neutral win percentage
				$division['Team'][$key]['current_rating'] = 1500;
			} else {
				// This will put teams in the range from 1000-2000, similar to other calculators
				$division['Team'][$key]['current_rating'] = intval(1000 * (
					0.25 * $this->_wp($team['id']) +
					0.50 * $this->_owp($games, $team['id']) +
					0.25 * $this->_oowp($games, $team['id'])
				)) + 1000;
			}
		}
	}

	function _wp($team_id, $ignore_id = false) {
		$wins = $this->results[$team_id]['wins'];
		$games = $this->results[$team_id]['games'];
		if ($ignore_id) {
			// Ignore results from games between these two teams
			if (!empty($this->vs[$team_id][$ignore_id]['games'])) {
				$wins -= $this->vs[$team_id][$ignore_id]['wins'];
				$games -= $this->vs[$team_id][$ignore_id]['games'];
				if ($games == 0) {
					// If they haven't played anyone else yet, give them a neutral win percentage
					return 0.5;
				}
			}
		}

		return $wins / $games;
	}

	function _owp($games, $team_id) {
		$opponents = array_merge(
			Set::extract("/Game[home_team=$team_id][type=" . SEASON_GAME . "]/away_team", $games),
			Set::extract("/Game[away_team=$team_id][type=" . SEASON_GAME . "]/home_team", $games)
		);

		$sum = 0;
		foreach ($opponents as $opponent) {
			$sum += $this->_wp($opponent, $team_id);
		}
		return $sum / count($opponents);
	}

	function _oowp($games, $team_id) {
		$opponents = array_merge(
			Set::extract("/Game[home_team=$team_id][type=" . SEASON_GAME . "]/away_team", $games),
			Set::extract("/Game[away_team=$team_id][type=" . SEASON_GAME . "]/home_team", $games)
		);

		$sum = 0;
		foreach ($opponents as $opponent) {
			$sum += $this->_owp($games, $opponent);
		}
		return $sum / count($opponents);
	}
}

?>