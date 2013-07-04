<?php
$field = 'pitch';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'womens'=> 16,
		'mens'	=> 16,
		'co-ed'	=> 16,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'bowler' => 'Bowler',
		'batter' => 'Batter',
		'wicketkeeper' => 'Wicketkeeper',
		'allrounder' => 'All Rounder',
	),

	'score_options' => array(
		// TODO
	),

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
