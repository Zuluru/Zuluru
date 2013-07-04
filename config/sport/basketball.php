<?php
$field = 'court';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'womens'=> 8,
		'mens'	=> 8,
		'co-ed'	=> 8,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'Guard' => 'Guard',
		'Forward' => 'Forward',
		'Center' => 'Center',
		'Point Guard' => 'Point Guard',
		'Shooting Guard' => 'Shooting Guard',
		'Small Forward' => 'Small Forward',
		'Power Forward' => 'Power Forward',
	),

	'score_options' => array(
		'Field goal' => 2,
		'3 pointer' => 3,
		'Free throw' => 1,
	),

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
