<?php

/**
 * Set up some mappings and other static global configuration.
 * These items generally won't change for different installations.
 */

$config['roster_requirements'] = array(
	'4/3'	=> 12,
	'5/2'	=> 12,
	'3/3'	=> 10,
	'4/2'	=> 10,
	'3/2'	=> 8,
	'womens'=> 12,
	'mens'	=> 12,
	'open'	=> 12,
);

$config['approved_by'] = array(
	-1		=> 'automatic approval',
	-2		=> 'automatic approval using home submission',
	-3		=> 'automatic approval using away submission',
	-4		=> 'game automatically forfeited due to lack of score submission',
);

// List of roster positions which denote player status on a roster.
$config['playing_roster_positions'] = array(
	'captain',
	'assistant',
	'player',
);

$config['extended_playing_roster_positions'] = array(
	'captain',
	'assistant',
	'player',
	'substitute',
);

// List of roster positions which confer additional permissions such as viewing
// of contact information and updating a team roster.
$config['privileged_roster_positions'] = array(
	'coach',
	'captain',
	'assistant',
);

?>