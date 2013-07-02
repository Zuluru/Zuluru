<?php

/**
 * Implementation of the team callback for the "active player" badge.
 */
class BadgePlayerActiveComponent extends Object
{
	function applicable($team) {
		// This conveniently checks both that the team is in a division and that the division is open.
		return (!empty($team['Division']['is_open']));
	}
}

?>