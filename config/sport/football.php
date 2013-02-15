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

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
