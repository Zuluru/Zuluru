<?php

/**
 * Derived class for implementing functionality for the USA Ultimate College ratings calculator.
 */

class RatingsUsauCollegeComponent extends RatingsComponent
{
	var $per_game_ratings = false;

	function _initializeRatings($league, &$division, $games) {
		parent::_initializeRatings($league, $division, $games);

		// Start all teams with the same rating
		foreach ($league['Division'] as $d) {
			foreach (array_keys($d['Team']) as $team_id) {
				$division['Team'][$team_id]['current_rating'] = $d['Team'][$team_id]['initial_rating'];
				$division['Team'][$team_id]['rating_sum'] = 0;
				$division['Team'][$team_id]['weight_sum'] = 0;
			}
		}
	}

	function _recalculateRatings(&$division, $games) {
		$today = time();

		$pt_sum = $wt_sum = 0;
		foreach ($games as $game) {
			$days = intval(($today - strtotime($game['GameSlot']['game_date'])) / DAY);
			$weight = min(1, 1 / (pow(($days + 4) / 7, 0.4)));

			// TODO: Handle teams that have moved divisions
			$division['Team'][$game['Game']['home_team']]['rating_sum'] += $this->_calculateRating(
					$game['Game']['home_score'], $game['Game']['away_score'],
					$division['Team'][$game['Game']['away_team']]['current_rating']) * $weight;
			$division['Team'][$game['Game']['home_team']]['weight_sum'] += $weight;

			$division['Team'][$game['Game']['away_team']]['rating_sum'] += $this->_calculateRating(
					$game['Game']['away_score'], $game['Game']['home_score'],
					$division['Team'][$game['Game']['home_team']]['current_rating']) * $weight;
			$division['Team'][$game['Game']['away_team']]['weight_sum'] += $weight;
		}

		foreach (array_keys($division['Team']) as $team_id) {
			$division['Team'][$team_id]['current_rating'] = intval(
				$division['Team'][$team_id]['rating_sum'] / $division['Team'][$team_id]['weight_sum']
			);
		}
	}

	function _calculateRating($team_score, $opponent_score, $opponent_rating) {
		if ($team_score == $opponent_score) {
			return $opponent_rating;
		}
		$losing_score = min($team_score, $opponent_score);
		$winning_score = max($team_score, $opponent_score);
		$x = max(0.66, (2.5 * pow($losing_score / $winning_score, 2)));
		if ($team_score > $opponent_score) {
			return $opponent_rating + (400 / $x);
		} else {
			return $opponent_rating - (400 / $x);
		}
	}
}

?>