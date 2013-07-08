<?php
/**
 * This file has two sections.
 *
 * First, we set up some mappings to pre-defined database values and
 * a few other constants. These items should not ever be changed.
 *
 * Second, we set up some static global configuration. These items
 * generally won't change for different installations. If you have
 * any local configuration customizations, adjust the $config array
 * by adding, altering or unsetting values through a file called
 * features_custom.php (which you must create).
 */

if (!defined('SEASON_GAME')) {
	define('SEASON_GAME', 1);
	define('POOL_PLAY_GAME', 2);
	define('CROSSOVER_GAME', 3);
	define('BRACKET_GAME', 4);
}

if (!defined('APPROVAL_AUTOMATIC')) {
	define('APPROVAL_AUTOMATIC', -1);		// approval, scores agree
	define('APPROVAL_AUTOMATIC_HOME', -2);  // approval, home score used
	define('APPROVAL_AUTOMATIC_AWAY', -3);  // approval, away score used
	define('APPROVAL_AUTOMATIC_FORFEIT', -4); // approval, no score entered
}

if (!defined('ROSTER_APPROVED')) {
	define('ROSTER_APPROVED', 1);
	define('ROSTER_INVITED', 2);
	define('ROSTER_REQUESTED', 3);
}

if (!defined('REASON_TYPE_PLAYER_ACTIVE')) {
	define('REASON_TYPE_PLAYER_ACTIVE', 1);
	define('REASON_TYPE_PLAYER_PASSIVE', 2);
	define('REASON_TYPE_TEAM', 3);
}

if (!defined('ATTENDANCE_UNKNOWN')) {
	define('ATTENDANCE_UNKNOWN', 0);	// status is unknown
	define('ATTENDANCE_ATTENDING', 1);	// attendance has been confirmed by player (and captain, if a substitute)
	define('ATTENDANCE_ABSENT', 2);		// absence has been confirmed by player
	define('ATTENDANCE_INVITED', 3);	// substitute has been invited by the captain
	define('ATTENDANCE_AVAILABLE', 4);	// substitute has indicated they are available
	define('ATTENDANCE_NO_SHOW', 5);	// player said they were coming, but didn't show
}

// Constants for IDs of automatic questions
// Must all be negative to avoid conflicts with user-created questions
if (!defined('TEAM_NAME')) {
	define('TEAM_NAME', -1);
	define('SHIRT_COLOUR', -2);
	define('REGION_PREFERENCE', -3);
	define('OPEN_ROSTER', -4);
	define('TEAM_ID', -5);
	define('FRANCHISE_ID', -6);
	define('FRANCHISE_ID_CREATED', -7);
	define('TRACK_ATTENDANCE', -10);
}

// Event connection types
if (!defined('EVENT_PREDECESSOR')) {
	define('EVENT_PREDECESSOR', 1);
	define('EVENT_SUCCESSOR', 2);
	define('EVENT_ALTERNATE', 3);
}

if (!defined('VISIBILITY_PRIVATE')) {
	define('VISIBILITY_PRIVATE', 1);
	define('VISIBILITY_CAPTAINS', 2);
	define('VISIBILITY_TEAM', 3);
	define('VISIBILITY_PUBLIC', 4);
}

if (!defined('BADGE_VISIBILITY_ADMIN')) {
	define('BADGE_VISIBILITY_ADMIN', 1);
	define('BADGE_VISIBILITY_HIGH', 2);
	define('BADGE_VISIBILITY_MEDIUM', 3);
	define('BADGE_VISIBILITY_LOW', 4);
}

if (!defined('SCHEDULE_TYPE_LEAGUE')) {
	define('SCHEDULE_TYPE_LEAGUE', 1);
	define('SCHEDULE_TYPE_TOURNAMENT', 2);
	define('SCHEDULE_TYPE_NONE', 3);
}

if (!defined('TIE_BREAKER_HTH_HTHPM_PM_LOSS')) {
	define('TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS', 1);
	define('TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT', 2);
	define('TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS', 3);
	define('TIE_BREAKER_PM_HTH_GF_LOSS', 4);
	define('TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT', 5);
	define('TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS', 6);
}

// Minimum "fake id" to use for setting edit pages
if (!defined('MIN_FAKE_ID')) {
	define('MIN_FAKE_ID', 1000000000);
}

if (!defined('ZULURU')) {
	// This changes the name under which Zuluru presents itself.
	// It can only be changed if you are also making substantial
	// changes to the code, e.g. to customize for a specific use.
	// Even in that case, you are required to retain the Zuluru
	// trademark in the "Powered by" notice.
	define('ZULURU', 'Zuluru');
}

$config['season_is_indoor'] = array(
	'None'			=> false,
	'Winter'		=> false,
	'Winter Indoor'	=> true,
	'Spring'		=> false,
	'Spring Indoor'	=> true,
	'Summer'		=> false,
	'Summer Indoor'	=> true,
	'Fall'			=> false,
	'Fall Indoor'	=> true,
);

// List of roster roles which denote player status on a roster.
$config['playing_roster_roles'] = array(
	'captain',
	'assistant',
	'player',
);

$config['extended_playing_roster_roles'] = array(
	'captain',
	'assistant',
	'player',
	'substitute',
);

// List of roster roles which denote a full-time member on a roster.
$config['regular_roster_roles'] = array(
	'coach',
	'captain',
	'assistant',
	'player',
);

// List of roster roles which confer additional permissions such as viewing
// of contact information and updating a team roster.
$config['privileged_roster_roles'] = array(
	'coach',
	'captain',
	'assistant',
);

// List of game statuses that indicate that the game was not played.
$config['unplayed_status'] = array(
	'cancelled',
	'forfeit',
	'rescheduled',
);

// List of stat types for various displays
$config['stat_types'] = array(
	'game' => array(
		'entered',
		'game_calc',
	),
	'team' => array(
		'season_total',
		'season_avg',
		'season_calc',
	),
);

$config['approved_by'] = array(
	APPROVAL_AUTOMATIC			=> 'automatic approval',
	APPROVAL_AUTOMATIC_HOME		=> 'automatic approval using home submission',
	APPROVAL_AUTOMATIC_AWAY		=> 'automatic approval using away submission',
	APPROVAL_AUTOMATIC_FORFEIT	=> 'game automatically forfeited due to lack of score submission',
);

$config['attendance'] = array(
	ATTENDANCE_ATTENDING	=> 'Attending',
	ATTENDANCE_ABSENT		=> 'Absent',
	ATTENDANCE_UNKNOWN		=> 'Unknown',
	ATTENDANCE_INVITED		=> 'Invited',
	ATTENDANCE_AVAILABLE	=> 'Available',
	ATTENDANCE_NO_SHOW		=> 'No Show',
);

$config['attendance_alt'] = array(
	ATTENDANCE_ATTENDING	=> 'Y',
	ATTENDANCE_ABSENT		=> 'N',
	ATTENDANCE_UNKNOWN		=> '?',
	ATTENDANCE_INVITED		=> 'I',
	ATTENDANCE_AVAILABLE	=> 'A',
	ATTENDANCE_NO_SHOW		=> 'X',
);

$config['attendance_verb'] = array(
	ATTENDANCE_ATTENDING	=> 'attending',
	ATTENDANCE_ABSENT		=> 'absent for',
	ATTENDANCE_UNKNOWN		=> 'unknown/undecided for',
	ATTENDANCE_INVITED		=> 'invited to sub for',
	ATTENDANCE_AVAILABLE	=> 'available to sub for',
	ATTENDANCE_NO_SHOW		=> 'a no-show for',
);

$config['event_attendance_verb'] = array(
	ATTENDANCE_ATTENDING	=> 'attending',
	ATTENDANCE_ABSENT		=> 'absent for',
	ATTENDANCE_UNKNOWN		=> 'unknown/undecided for',
	ATTENDANCE_INVITED		=> 'invited to attend',
	ATTENDANCE_AVAILABLE	=> 'available to attend',
	ATTENDANCE_NO_SHOW		=> 'a no-show for',
);

$config['event_connection'] = array(
	EVENT_PREDECESSOR => 'Predecessor',
	EVENT_SUCCESSOR => 'Successor',
	EVENT_ALTERNATE => 'Alternate',
);

$config['visibility'] = array(
	VISIBILITY_PRIVATE => 'Private',
	VISIBILITY_CAPTAINS => 'Captains',
	VISIBILITY_TEAM => 'Team',
	VISIBILITY_PUBLIC => 'Public',
);

// Percent likelihood that a notice will be shown, if there is one to show
$config['notice_frequency'] = 20;

// List of colours to use for automatically-created teams
$config['automatic_team_colours'] = array(
	'Black',
	'White',
	'Red',
	'Blue',
	'Yellow',
	'Green',
	'Purple',
	'Orange',
);

$config['schedule_type'] = array(
	'roundrobin' => SCHEDULE_TYPE_LEAGUE,
	'ratings_ladder' => SCHEDULE_TYPE_LEAGUE,
	'tournament' => SCHEDULE_TYPE_TOURNAMENT,
	'none' => SCHEDULE_TYPE_NONE,
);

$config['tie_breakers'] = array(
	TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => array('hth', 'hthpm', 'pm', 'gf', 'loss'),
	TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT => array('hth', 'hthpm', 'pm', 'gf', 'loss', 'spirit'),
	TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS => array('spirit', 'hth', 'hthpm', 'pm', 'gf', 'loss'),
	TIE_BREAKER_PM_HTH_GF_LOSS => array('pm', 'hth', 'gf', 'loss'),
	TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT => array('pm', 'hth', 'gf', 'loss', 'spirit'),
	TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS => array('spirit', 'pm', 'hth', 'gf', 'loss'),
);

// MIME definitions for document types that CakePHP doesn't support
$config['new_mime_types'] = array(
	'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
	'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
	'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
	'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
	'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
	'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
	'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
);

// Extensions that we want to send to the browser instead of downloading
$config['no_download_extensions'] = array(  
	'html', 'htm', 'txt', 'pdf',  
	'bmp', 'gif', 'jpe', 'jpeg', 'jpg', 'png', 'tif', 'tiff',
);

if (file_exists(CONFIGS . 'features_custom.php')) {
	include(CONFIGS . 'features_custom.php');
}

?>
