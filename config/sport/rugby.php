<?php
$field = 'pitch';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'womens'=> 18,
		'mens'	=> 18,
		'co-ed'	=> 18,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'looseheadprop' => 'Loosehead Prop',
		'hooker' => 'Hooker',
		'tightheadprop' => 'Tighthead Prop',
		'secondrower' => 'Second Rower',
		'blindsideflanker' => 'Blindside Flanker',
		'opensideflanker' => 'Openside Flanker',
		'number8' => 'Number 8',
		'scrumhalf' => 'Scrumhalf',
		'flyhalf' => 'Flyhalf',
		'weaksidewinger' => 'Weak Side Winger',
		'insidecenter' => 'Inside Center',
		'outsidecenter' => 'Outside Center',
		'strongsidewinger' => 'Strong Side Winger',
		'fullback' => 'Fullback',
	),

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
