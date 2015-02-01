<?php
$field = 'pitch';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'start' => array(
		'stat_sheet' => null,
		'stat_sheet_direction' => true,
		'live_score' => null,
		'box_score' => null,
		'twitter' => null,
	),

	'roster_requirements' => array(
		'womens'=> 18,
		'mens'	=> 18,
		'co-ed'	=> 18,
		'womens sevens'=> 10,
		'mens sevens'	=> 10,
		'co-ed sevens'	=> 10,
		'womens tens'=> 13,
		'mens tens'	=> 13,
		'co-ed tens'	=> 13,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'prop' => 'Prop',
		'looseheadprop' => 'Loosehead Prop',
		'hooker' => 'Hooker',
		'tightheadprop' => 'Tighthead Prop',
		'secondrower' => 'Second Rower',
		'blindsideflanker' => 'Blindside Flanker',
		'opensideflanker' => 'Openside Flanker',
		'number8' => 'Number 8',
		'scrumhalf' => 'Scrumhalf',
		'flyhalf' => 'Flyhalf',
		'winger' => 'Winger',
		'center' => 'Center',
		'weaksidewinger' => 'Weak Side Winger',
		'insidecenter' => 'Inside Center',
		'outsidecenter' => 'Outside Center',
		'strongsidewinger' => 'Strong Side Winger',
		'fullback' => 'Fullback',
	),

	'score_options' => array(
		// TODO
	),

	'other_options' => array(
		// TODO
	),

	'rating_questions' => false,
);

if (file_exists(CONFIGS . 'sport/rugby_custom.php')) {
	include(CONFIGS . 'sport/rugby_custom.php');
}

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
