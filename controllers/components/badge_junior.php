<?php

/**
 * Implementation of the runtime callback for the "junior" badge.
 */
class BadgeJuniorComponent extends Object
{
	function applicable($person) {
		return (Configure::read('profile.birthdate') && !empty($person['birthdate']) && strtotime($person['birthdate']) > strtotime('-18 years'));
	}
}

?>