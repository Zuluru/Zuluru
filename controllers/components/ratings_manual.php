<?php

/**
 * Derived class for implementing functionality for the manual ratings calculator.
 */

class RatingsManualComponent extends RatingsComponent
{
	function calculateRatingsChange($home_score) {
		// The manually-calculated game rating is entered as the score
		return $home_score;
	}
}

?>
