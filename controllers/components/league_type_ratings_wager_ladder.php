<?php

/**
 * Derived class for implementing functionality for the ratings wager ladder.
 */

// Extend the LeagueTypeRatingsLadderComponent
require_once('league_type_ratings_ladder.php');

class LeagueTypeRatingsWagerLadderComponent extends LeagueTypeRatingsLadderComponent
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'ladder';

	function addMenuItems ($league, $is_coordinator = false) {
		if ($this->_controller->is_admin || $is_coordinator) {
			$this->_controller->_addMenuItem ('Adjust ratings', array('controller' => 'leagues', 'action' => 'ratings', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		}
	}

	/**
	 * Sort a ladder league by:
	 * 1: Rating
	 * 2: Spirit
	 * 3: Wins/ties
	 * 4: +/-
	 * 5: Goals for
	 */
	static function compareTeams($a, $b) {
		if ($a['rating'] < $b['rating'])
			return 1;
		if ($a['rating'] > $b['rating'])
			return -1;

		// TODO: Compare spirit scores?

		if (array_key_exists ('results', $a))
		{
			if ($a['results']['pts'] < $b['results']['pts'])
				return 1;
			if ($a['results']['pts'] > $b['results']['pts'])
				return -1;

			if ($a['results']['gf'] - $a['results']['ga'] < $b['results']['gf'] - $b['results']['ga'])
				return 1;
			if ($a['results']['gf'] - $a['results']['ga'] > $b['results']['gf'] - $b['results']['ga'])
				return -1;

			if ($a['results']['gf'] < $b['results']['gf'])
				return 1;
			if ($a['results']['gf'] > $b['results']['gf'])
				return -1;
		}

		return 0;
	}

	function schedulingFields($is_admin, $is_coordinator) {
		if ($is_admin || $is_coordinator) {
			return array(
				'games_before_repeat' => array(
					'label' => 'Games Before Repeat',
					'options' => Configure::read('options.games_before_repeat'),
					'empty' => '---',
					'after' => __('The number of games before two teams can be scheduled to play each other again.', true),
					'required' => true,	// Since this is not in the model validation list, we must force this
				),
			);
		} else {
			return array();
		}
	}

	function schedulingFieldsValidation() {
		return array(
			'games_before_repeat' => array(
				'inlist' => array(
					'rule' => array('inconfig', 'options.games_before_repeat'),
					'message' => 'You must select a valid number of games before repeat.',
				),
			),
		);
	}

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
