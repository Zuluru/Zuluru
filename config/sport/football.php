<?php
$field = 'field';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'3/3'	=> 10,
		'4/2'	=> 10,
		'womens 6s'=> 10,
		'mens 6s'	=> 10,
		'co-ed 6s'	=> 10,
		'womens 11s'=> 16,
		'mens 11s'	=> 16,
		'co-ed 11s'	=> 16,
		'womens 12s'=> 18,
		'mens 12s'	=> 18,
		'co-ed 12s'	=> 18,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'quarterback' => 'Quarterback',
		'center' => 'Center',
		'tackle' => 'Tackle',
		'guard' => 'Guard',
		'tightend' => 'Tight End',
		'halfback' => 'Halfback',
		'fullback' => 'Fullback',
		'runningback' => 'Running Back',
		'widereceiver' => 'Wide Receiver',
		'linebacker' => 'Linebacker',
		'middlelinebacker' => 'Middle Linebacker',
		'outsidelinebacker' => 'Outside Linebacker',
		'end' => 'End',
		'cornerback' => 'Cornerback',
		'safety' => 'Safety',
	),

	'score_options' => array(
		'Touchdown' => 6,
		'Conversion' => 1,
		'Two-point conversion' => 2,
		'Field goal' => 3,
		'Safety' => 2,
		'Single' => 1,
		'Rouge' => 1,
	),


	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
