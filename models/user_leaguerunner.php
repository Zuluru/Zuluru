<?php
App::Import ('model', 'User');

/**
 * Class for handling authentication using a user database imported from Leaguerunner.
 */
class UserLeaguerunner extends User {
	var $name = 'UserLeaguerunner';

	/**
	 * Function to use for hashing passwords.
	 */
	var $hashMethod = 'md5';
}
?>
