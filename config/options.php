<?php

/**
 * Configure various select options. These are used to populate various
 * SELECT inputs, and for validating those inputs.
 *
 * Changing the language of any of these should be done through the
 * internationalization methods at output, leaving English in the
 * database. This way, a single site can support multiple languages.
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
	'ratio'					=> make_options(array(
		'4/3',
		'5/2',
		'3/3',
		'4/2',
		'3/2',
		'womens',
		'mens',
		'open',
	)),
	'sotg_display'			=> make_human_options(array(
		'coordinator_only',
		'symbols_only',
		'numeric',
		'all',
	)),
	'allstar'				=> make_options(array(
		'never',
		'optional',
		'always',
	)),
	'payment'				=> make_options(array(
		'Unpaid',
		'Pending',
		'Paid',
		'Refunded',
	)),
	'incident_types'		=> make_options(array(
		'Field condition',
		'Injury',
		'Rules disagreement',
		'Illegal Substitution',
		'Escalated incident',
		'Other',
	)),

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
		'10'				=> '10: High calibre competitive player (team was top 4 at nationals)',
		'9'					=> '9: Medium calibre competitive player',
		'8'					=> '8: Lower calibre competitive player',
		'7'					=> '7: Top tier Mon/Wed league player, minimal/no comp experience',
		'6'					=> '6: Mid to Upper tier Mon/Wed OR Top Tier Tue/Thu league player',
		'5'					=> '5: Mid tier league player',
		'4'					=> '4: Key player lower tier team, minimal/no higher tier experience',
		'3'					=> '3: Lower tier league player',
		'2'					=> '2: Beginner, minimal experience but athletic with sports background',
		'1'					=> '1: Absolute Beginner',
	),

	'roster_position' => array(
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

	'league_position' => array(
		'coordinator'		=> 'Coordinator',
	),

	'game_status' => array(
		'normal'			=> 'Normal',
		'home_default'		=> 'Home Default',
		'away_default'		=> 'Away Default',
		'rescheduled'		=> 'Rescheduled',
		'cancelled'			=> 'Cancelled',
		'forfeit'			=> 'Forfeit',
	),

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

	'waiver_types' => array(
		'none'				=> 'None',
		'membership'		=> 'Membership',
		'event'				=> 'Event',
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
		'ratings_wager_ladder' => 'Ratings Wager Ladder',
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

$config['options']['tier'] = range (0, 10);
$config['options']['round'] = make_options (range (1, 5));
$config['options']['games_before_repeat'] = range (0, 9);

$year = date('Y');
$config['options']['year'] = array(
	'started' => array('min' => 1986, 'max' => $year),
	'born' => array('min' => $year - 75, 'max' => $year - 5),
	'event' => array('min' => $year - 1, 'max' => $year + 1),
	'gameslot' => array('min' => $year, 'max' => $year + 1),
);

?>
