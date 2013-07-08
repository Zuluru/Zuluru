<?php

/**
 * Derived class for implementing functionality for the KRACH ratings calculator.
 */

class RatingsKrachComponent extends RatingsComponent
{
	var $per_game_ratings = false;
	var $results = array();
	var $vs = array();

	function calculateExpectedWin ($rating1, $rating2) {
		return $rating1 / ($rating1 + $rating2);
	}

	// This method is a little awkward, but greatly simplifies the RRI implementation.
	function _odds($home_score, $away_score) {
		if ($home_score > $away_score) {
			return 1;
		} else if ($home_score < $away_score) {
			return 0;
		} else {
			return 0.5;
		}
	}

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

					$home_odds = $this->_odds($game['Game']['home_score'], $game['Game']['away_score']);
					$this->results[$game['Game']['home_team']]['wins'] += $home_odds;
					$this->results[$game['Game']['away_team']]['wins'] += (1 - $home_odds);
					$this->vs[$game['Game']['home_team']][$game['Game']['away_team']]['wins'] += $home_odds;
					$this->vs[$game['Game']['away_team']][$game['Game']['home_team']]['wins'] += (1 - $home_odds);
				}
			}
		}

		foreach ($league['Division'] as $d) {
			foreach (array_keys($d['Team']) as $team_id) {
				$division['Team'][$team_id]['current_rating'] = $d['Team'][$team_id]['initial_rating'];
			}
		}
	}

	function _recalculateRatings(&$division, $games) {
		foreach ($division['Team'] as $key => $team) {
			$division['Team'][$key]['calculated_rating'] = max(1, $this->results[$key]['wins'] * $this->_sos($division, $games, $key));
		}

		foreach (array_keys($division['Team']) as $key) {
			$division['Team'][$key]['current_rating'] = $division['Team'][$key]['calculated_rating'];
		}
	}

	function _sos($division, $games, $team_id) {
		$opponents = array_merge(
			Set::extract("/Game[home_team=$team_id][type=" . SEASON_GAME . "]/away_team", $games),
			Set::extract("/Game[away_team=$team_id][type=" . SEASON_GAME . "]/home_team", $games)
		);

		$sum = 0;
		foreach ($opponents as $opponent) {
			$sum += $this->vs[$team_id][$opponent]['games'] / ($division['Team'][$team_id]['current_rating'] + $division['Team'][$opponent]['current_rating']);
		}
		return $sum;
	}

	// This will normalize ratings so the average is 1500, similar to other calculators
	function _finalizeRatings(&$division) {
		$ratings = Set::extract('/Team/current_rating', $division);
		$factor = 1500 / (array_sum($ratings) / count($ratings));
		foreach (array_keys($division['Team']) as $key) {
			$division['Team'][$key]['current_rating'] = intval($division['Team'][$key]['current_rating'] * $factor);
		}
	}
}

?>