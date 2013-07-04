<?php

/**
 * Configure various select options. These are used to populate various
 * SELECT inputs, and for validating those inputs.
 *
 * Changing the language of any of these should be done through the
 * internationalization methods at output, leaving English in the
 * database. This way, a single site can support multiple languages.
 *
 * If you have any local configuration customizations, adjust the $config
 * array by adding, altering or unsetting values through a file called
 * options_custom.php (which you must create).
 */

function make_options($values)
{
	return array_combine ($values, $values);
}

function make_human_options($values)
{
	$human = array_map (array('Inflector', 'Humanize'), $values);
	return array_combine ($values, $human);
}

$config['options'] = array(
	'enable' => array(
		'0'					=> __('Disabled', true),
		'1'					=> __('Enabled', true)
	),

	'access_required' => array(
		'1'					=> __('User can update', true),
		'2'					=> __('Admin can update', true),
	),

	'access_optional' => array(
		'1'					=> __('User can update', true),
		'2'					=> __('Admin can update', true),
		'0'					=> __('Disabled entirely', true),
	),

	'gender'				=> make_options(array(
		'Male',
		'Female',
	)),

	'shirt_size'			=> make_options(array(
		'Womens XSmall',
		'Womens Small',
		'Womens Medium',
		'Womens Large',
		'Womens XLarge',
		'Mens Small',
		'Mens Medium',
		'Mens Large',
		'Mens XLarge',
	)),

	'record_status'			=> make_options(array(
		'new',
		'inactive',
		'active',
		'locked',
	)),

	'sport'					=> make_human_options(array(
		'ultimate',
	)),

	'surface'				=> make_human_options(array(
		'grass',
		'turf',
		'sand',
		'dirt',
		'clay',
		'hardwood',
		'rubber',
		'urethane',
		'concrete',
		'asphalt',
		'ice',
		'snow',
	)),

	'sotg_display'			=> make_human_options(array(
		'coordinator_only',
		'symbols_only',
		'numeric',
		'all',
	)),

	'tie_breaker'			=> array(
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS => __('Plus-minus > Head-to-head > Goals for > Losses', true),
	),

	'tie_breaker_spirit'	=> array(
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS => __('Spirit > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS => __('Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT => __('Plus-minus > Head-to-head > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS => __('Spirit > Plus-minus > Head-to-head > Goals for > Losses', true),
	),

	'allstar'				=> make_options(array(
		'never',
		'optional',
		'always',
	)),

	'allstar_from'			=> make_options(array(
		'opponent',
		'submitter',
	)),

	'stat_tracking'			=> make_options(array(
		'never',
		'optional',
		'always',
	)),

	'payment'				=> make_options(array(
		'Unpaid',
		'Pending',
		'Paid',
		'Refunded',
		'Waiting',
	)),

	'incident_types'		=> make_options(array(
		Configure::read('ui.field_cap') . ' condition',
		'Injury',
		'Rules disagreement',
		'Illegal Substitution',
		'Escalated incident',
		'Other',
	)),

	// If additions are made to this, they must also be reflected in features.php
	'season'				=> make_options(array(
		'None',
		'Winter',
		'Winter Indoor',
		'Spring',
		'Spring Indoor',
		'Summer',
		'Summer Indoor',
		'Fall',
		'Fall Indoor',
	)),

	'skill' => array(
		'10'				=> '10: High calibre touring player (team was top 4 at nationals)',
		'9'					=> '9: Medium calibre touring player',
		'8'					=> '8: Key player in competitive league, or lower calibre touring player',
		'7'					=> '7: Competitive league player, minimal/no touring experience',
		'6'					=> '6: Key player in intermediate league, or lower player in competitive league',
		'5'					=> '5: Comfortable in intermediate league',
		'4'					=> '4: Key player in recreational league, or lower player in intermediate league',
		'3'					=> '3: Comfortable in recreational league',
		'2'					=> '2: Beginner, minimal experience but athletic with sports background',
		'1'					=> '1: Absolute Beginner',
	),

	'roster_role' => array(
		'captain'			=> 'Captain',
		'assistant'			=> 'Assistant captain',
		'coach'				=> 'Non-playing coach',
		'player'			=> 'Regular player',
		'substitute'		=> 'Substitute player',
		'none'				=> 'Not on team',
	),

	'roster_methods' => array(
		'invite'			=> 'Players are invited and must accept',
		'add'				=> 'Players are added directly',
	),

	'division_position' => array(
		'coordinator'		=> 'Coordinator',
	),

	'game_status' => make_human_options(array(
		'normal',
		'in_progress',
		'home_default',
		'away_default',
		'rescheduled',
		'cancelled',
		'forfeit',
	)),

	'field_rating' => array(
		'A'					=> 'A',
		'B'					=> 'B',
		'C'					=> 'C',
	),

	'test_payment' => array(
		'0'					=> 'Nobody',
		'1'					=> 'Everybody',
		'2'					=> 'Admins',
	),

	'currency' => array(
		'CAD'				=> 'Canadian',
		'USD'				=> 'USA',
	),

	'membership_types' => array(
		'full'				=> 'Full',
		'intro'				=> 'Introductory',
//		'trial'				=> 'Trial',
	),

	'waivers' => array(
		'expiry_type' => array(
			'fixed_dates'		=> 'Fixed dates',
			'elapsed_time'		=> 'A fixed duration',
			'event'				=> 'Duration of the event',
		),
	),

	'date_formats' => array(
		'M j, Y',
		'F j, Y',
		'd/m/Y',
		'Y/m/d',
	),

	'day_formats' => array(
		'D M j',
		'l F j',
	),

	'time_formats' => array(
		'H:i',
		'g:iA',
	),

	'question_types' => make_options(array(
		'radio',
		'select',
		'checkbox',
		'text',
		'textbox',
		'group_start',
		'group_end',
		'description',
		'label',
	)),

	// List of available badge categories
	'category' => array(
		'runtime' => 'Run-time determination',
		'game' => 'Determined by game outcomes',
		'team' => 'Determined by roster status',
		'registration' => 'Determined by registrations',
		'aggregate' => 'Aggregates multiple badges, e.g. "5x"',
		'nominated' => 'Nominated by anyone (must be approved)',
		'assigned' => 'Assigned by an admin',
	),

	'visibility' => array(
		BADGE_VISIBILITY_ADMIN => 'Admin only (same locations as high)',
		BADGE_VISIBILITY_HIGH => 'High (profile, pop-ups, team rosters)',
		BADGE_VISIBILITY_LOW => 'Low (profile only)',
	),

	/**
	 * The following options are for components that change the elements
	 * found on certain view and edit pages. Each group must have a base
	 * component class, and each item must have a derived component class.
	 * You can remove (comment out) any options in here that you don't
	 * want available for your leagues, but you can't just add things here
	 * without also adding the implementation to support it.
	 */

	// List of available scheduling types
	'schedule_type' => array(
		'none' => 'None',
		'roundrobin' => 'Round Robin',
		'ratings_ladder' => 'Ratings Ladder',
		'tournament' => 'Tournament',
	),

	// List of available rating calculators
	'rating_calculator' => array(
		'none' => 'None',
		'wager' => 'Wager System',
		'usau_college' => 'USA Ultimate College',
		'rri' => 'RRI',
		'krach' => 'KRACH',
		'rpi' => 'RPI',
		'modified_elo' => 'Modified Elo',
	),

	// List of available spirit questionnaires
	'spirit_questions' => array(
		'none' => 'No spirit questionnaire',
		'team' => 'Team spirit',
		'wfdf' => 'WFDF spirit',
	),

	// List of available payment providers
	'payment_provider' => array(
		'chase' => 'Chase Paymentech',
		'moneris' => 'Moneris',
		'paypal' => 'Paypal',
	),

	// List of available invoice outputs
	'invoice' => array(
		'invoice' => 'Standard',
	),
);

$config['options']['round'] = make_options (range (1, 5));
$config['options']['games_before_repeat'] = range (0, 9);

$year = date('Y');
$config['options']['year'] = array(
	'started' => array('min' => 1986, 'max' => $year),
	'born' => array('min' => $year - 75, 'max' => $year - 5),
	'event' => array('min' => $year - 1, 'max' => $year + 1),
	'gameslot' => array('min' => $year, 'max' => $year + 1),
);

if (file_exists(CONFIGS . 'options_custom.php')) {
	include(CONFIGS . 'options_custom.php');
}

?>
