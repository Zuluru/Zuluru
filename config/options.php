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

if (!function_exists('make_options')) {
	function make_options($values)
	{
		if (empty($values)) {
			return array();
		}
		return array_combine ($values, array_map ('__', $values, array_fill(0, count($values), true)));
	}

	function make_human_options($values)
	{
		if (empty($values)) {
			return array();
		}
		$human = array_map (array('Inflector', 'Humanize'), $values);
		$human = array_map ('__', $human, array_fill(0, count($human), true));
		return array_combine ($values, $human);
	}
}

$config['options'] = array(
	'enable' => array(
		'0'					=> __('Disabled', true),
		'1'					=> __('Enabled', true)
	),

	'access_required' => array(
		PROFILE_USER_UPDATE	=> __('User can update', true),
		PROFILE_ADMIN_UPDATE=> __('Admin can update', true),
	),

	'access_optional' => array(
		PROFILE_USER_UPDATE	=> __('User can update', true),
		PROFILE_ADMIN_UPDATE=> __('Admin can update', true),
		PROFILE_DISABLED	=> __('Disabled entirely', true),
	),

	'access_registration' => array(
		PROFILE_USER_UPDATE	=> __('User can update', true),
		PROFILE_ADMIN_UPDATE=> __('Admin can update', true),
		PROFILE_REGISTRATION=> __('Updated during event registration', true),
		PROFILE_DISABLED	=> __('Disabled entirely', true),
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
		'Mens XXLarge',
		'Youth Small',
		'Youth Medium',
		'Youth Large',
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

	'tie_breaker_carbon'	=> array(
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS => __('Carbon flip > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Carbon flip > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Carbon flip > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS => __('Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_CF_PM_HTH_GF_LOSS => __('Carbon flip > Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_CF_HTH_GF_LOSS => __('Plus-minus > Carbon flip > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_CF_GF_LOSS => __('Plus-minus > Head-to-head > Carbon flip > Goals for > Losses', true),
	),

	'tie_breaker_spirit'	=> array(
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS => __('Spirit > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS => __('Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT => __('Plus-minus > Head-to-head > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS => __('Spirit > Plus-minus > Head-to-head > Goals for > Losses', true),
	),

	'tie_breaker_spirit_carbon' => array(
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_LOSS_SPIRIT => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_LOSS => __('Spirit > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS => __('Carbon flip > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_CF_HTH_HTHPM_PM_GF_LOSS_SPIRIT => __('Carbon flip > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses > Spirit', true),
		TIE_BREAKER_CF_SPIRIT_HTH_HTHPM_PM_GF_LOSS => __('Carbon flip > Spirit > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS => __('Head-to-head > Head-to-head plus-minus > Carbon flip > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_CF_PM_GF_LOSS_SPIRIT => __('Head-to-head > Head-to-head plus-minus > Carbon flip > Plus-minus > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_HTH_HTHPM_CF_PM_GF_LOSS => __('Spirit > Head-to-head > Head-to-head plus-minus > Carbon flip > Plus-minus > Goals for > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Carbon flip > Losses', true),
		TIE_BREAKER_HTH_HTHPM_PM_GF_CF_LOSS_SPIRIT => __('Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Carbon flip > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_HTH_HTHPM_PM_GF_CF_LOSS => __('Spirit > Head-to-head > Head-to-head plus-minus > Plus-minus > Goals for > Carbon flip > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS => __('Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_GF_LOSS_SPIRIT => __('Plus-minus > Head-to-head > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_PM_HTH_GF_LOSS => __('Spirit > Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_CF_PM_HTH_GF_LOSS => __('Carbon flip > Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_CF_PM_HTH_GF_LOSS_SPIRIT => __('Carbon flip > Plus-minus > Head-to-head > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_CF_PM_HTH_GF_LOSS => __('Spirit > Carbon flip > Plus-minus > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_CF_HTH_GF_LOSS => __('Plus-minus > Carbon flip > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_CF_HTH_GF_LOSS_SPIRIT => __('Plus-minus > Carbon flip > Head-to-head > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_PM_CF_HTH_GF_LOSS => __('Spirit > Plus-minus > Carbon flip > Head-to-head > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_CF_GF_LOSS => __('Plus-minus > Head-to-head > Carbon flip > Goals for > Losses', true),
		TIE_BREAKER_PM_HTH_CF_GF_LOSS_SPIRIT => __('Plus-minus > Head-to-head > Carbon flip > Goals for > Losses > Spirit', true),
		TIE_BREAKER_SPIRIT_PM_HTH_CF_GF_LOSS => __('Spirit > Plus-minus > Head-to-head > Carbon flip > Goals for > Losses', true),
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

	'most_spirited'			=> make_options(array(
		'never',
		'optional',
		'always',
	)),

	'stat_tracking'			=> make_options(array(
		'never',
		'optional',
		'always',
	)),

	'payment'				=> make_options(array(
		'Unpaid',
		'Reserved',
		'Pending',
		'Deposit',
		'Partial',
		'Paid',
		'Cancelled',
		'Waiting',
	)),

	'payment_method'		=> make_options(array(
		'Online',
		'Credit Card',
		'Cheque',
		'Electronic Funds Transfer',
		'Cash',
		'Money Order',
		'Other',
		'Credit Redeemed',
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
		'10'				=> __('10: High calibre touring player (team was top 4 at nationals)', true),
		'9'					=> __('9: Medium calibre touring player', true),
		'8'					=> __('8: Key player in competitive league, or lower calibre touring player', true),
		'7'					=> __('7: Competitive league player, minimal/no touring experience', true),
		'6'					=> __('6: Key player in intermediate league, or lower player in competitive league', true),
		'5'					=> __('5: Comfortable in intermediate league', true),
		'4'					=> __('4: Key player in recreational league, or lower player in intermediate league', true),
		'3'					=> __('3: Comfortable in recreational league', true),
		'2'					=> __('2: Beginner, minimal experience but athletic with sports background', true),
		'1'					=> __('1: Absolute Beginner', true),
	),

	'roster_role' => array(
		'captain'			=> __('Captain', true),
		'assistant'			=> __('Assistant captain', true),
		'coach'				=> __('Non-playing coach', true),
		'player'			=> __('Regular player', true),
		'substitute'		=> __('Substitute player', true),
		'none'				=> __('Not on team', true),
	),

	'roster_methods' => array(
		'invite'			=> __('Players are invited and must accept', true),
		'add'				=> __('Players are added directly', true),
	),

	'division_position' => array(
		'coordinator'		=> __('Coordinator', true),
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

	'game_lengths' => make_options(array(
		0,
		15,
		30,
		45,
		60,
		75,
		90,
		105,
		120,
	)),

	'game_buffers' => make_options(array(
		0,
		5,
		10,
		15,
	)),

	'field_rating' => array(
		'A'					=> 'A',
		'B'					=> 'B',
		'C'					=> 'C',
	),

	'test_payment' => array(
		'0'					=> __('Nobody', true),
		'1'					=> __('Everybody', true),
		'2'					=> __('Admins', true),
	),

	'currency' => array(
		'CAD'				=> __('Canadian', true),
		'USD'				=> __('USA', true),
	),

	'units' => make_options(array(
		'Imperial',
		'Metric',
	)),

	'membership_types' => array(
		'full'				=> __('Full', true),
		'intro'				=> __('Introductory', true),
//		'trial'				=> __('Trial', true),
	),

	'waivers' => array(
		'expiry_type' => array(
			'fixed_dates'		=> __('Fixed dates', true),
			'elapsed_time'		=> __('A fixed duration', true),
			'event'				=> __('Duration of the event', true),
			'never'				=> __('Never expires', true),
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
		'runtime' => __('Run-time determination', true),
		'game' => __('Determined by game outcomes', true),
		'team' => __('Determined by roster status', true),
		'registration' => __('Determined by registrations', true),
		'aggregate' => __('Aggregates multiple badges, e.g. "5x"', true),
		'nominated' => __('Nominated by anyone (must be approved)', true),
		'assigned' => __('Assigned by an admin', true),
	),

	'visibility' => array(
		BADGE_VISIBILITY_ADMIN => __('Admin only (same locations as high)', true),
		BADGE_VISIBILITY_HIGH => __('High (profile, pop-ups, team rosters)', true),
		BADGE_VISIBILITY_LOW => __('Low (profile only)', true),
	),

	/**
	 * The following options are for components that change the elements
	 * found on certain view and edit pages. Each group must have a base
	 * component class, and each item must have a derived component class.
	 * You can remove (comment out) any options in here that you don't
	 * want available for your leagues, but you can't just add things here
	 * without also adding the implementation to support it.
	 * 
	 * The "competition" schedule type is not available by default, as it
	 * applies to a small subset of sports. It is used for anything where
	 * several teams are given a score based on their own performance,
	 * unrelated to anything that the other teams do. Teams may compete at
	 * the same time or not. The winner is the team with the highest (or
	 * lowest) score. Examples include many track & field events, golf, etc.
	 * The "manual" rating calculator is for use with competition divisions.
	 * To enable this type, either uncomment the lines below, or make use of
	 * the options_custom.php file to re-define the list of scheduling types
	 * and rating calculators with these included.
	 */

	// List of available scheduling types
	'schedule_type' => array(
		'none' => __('None', true),
		'roundrobin' => __('Round Robin', true),
		'ratings_ladder' => __('Ratings Ladder', true),
		//'competition' => __('Competition', true),
		'tournament' => __('Tournament', true),
	),

	// List of available rating calculators
	'rating_calculator' => array(
		'none' => __('None', true),
		'wager' => __('Wager System', true),
		'usau_college' => __('USA Ultimate College', true),
		'rri' => __('RRI', true),
		'krach' => __('KRACH', true),
		'rpi' => __('RPI', true),
		'modified_elo' => __('Modified Elo', true),
		//'manual' => __('Manual', true),
	),

	// List of available spirit questionnaires
	'spirit_questions' => array(
		'none' => __('No spirit questionnaire', true),
		'wfdf' => __('WFDF standard', true),
		'wfdf2' => __('WFDF standard 2014 version', true),
		'modified_wfdf' => __('Modified WFDF', true),
		'modified_bula' => __('Modified BULA', true),
		'team' => __('Leaguerunner original', true),
		'ocua_team' => __('Modified Leaguerunner', true),
		'suzuki' => __('Sushi Suzuki\'s Alternate', true),
	),

	// List of available payment providers
	'payment_provider' => array(
		'chase' => __('Chase Paymentech', true),
		'moneris' => __('Moneris', true),
		'paypal' => __('Paypal', true),
	),

	// List of available invoice outputs
	'invoice' => array(
		'invoice' => __('Standard', true),
	),
);

$config['options']['round'] = make_options (range (1, 5));
$config['options']['games_before_repeat'] = range (0, 9);

$year = date('Y');
$config['options']['year'] = array(
	'started' => array('min' => 1986, 'max' => $year),
	'born' => array('min' => $year - 75, 'max' => $year - 3),
	'event' => array('min' => $year - 1, 'max' => $year + 1),
	'gameslot' => array('min' => $year, 'max' => $year + 1),
);

if (file_exists(CONFIGS . 'options_custom.php')) {
	include(CONFIGS . 'options_custom.php');
}

?>
