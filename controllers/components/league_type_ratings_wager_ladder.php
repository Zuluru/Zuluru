<?php

/**
 * Derived class for implementing functionality for the ratings wager ladder.
 */

// Extend the LeagueTypeRatingsLadderComponent
require_once('league_type_ratings_ladder.php');

class LeagueTypeRatingsWagerLadderComponent extends LeagueTypeRatingsLadderComponent
{
	/**
	 * Calculate the wager ratings change for the result provided. 
	 *
	 * This uses a wagering system, where:
	 * 	- the final score determines the total amount of the pot.
	 * 	  It's based around a winning score of 15 points and tweaked
	 * 	  to produce the same ratings change for similar point
	 * 	  differentials for higher/lower final scores.
	 *
	 * 	- each team contributes a percentage of the pot based on their
	 * 	  expected chance to win
	 *
	 * 	- the losing team always takes away the same number of rating
	 * 	  points as their game points
	 *
	 * 	- the winning team takes away the remainder
	 *
	 * 	- thus, the point differential change amounts to:
	 * 	   ($total_pot - $loser_score - $winner_wager)
	 */
	function calculateRatingsChange($home_score, $away_score, $expected_win) {
		// Total wager value varies based on score
		// High scoring games increase the wager value
		$wager = max($home_score, $away_score) * 2 + 10;

		$winner_wager = ceil( $wager * $expected_win );

		if($home_score == $away_score) {
			$winner_gain = $wager / 2;
		} else if ( $home_score > $away_score ) {
			$winner_gain = $wager - $away_score;
		} else {
			$winner_gain = $wager - $home_score;
		}

		return $winner_gain - $winner_wager;
	}
}

?>
