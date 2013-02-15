<?php
$field = 'diamond';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'womens'=> 12,
		'mens'	=> 12,
		'co-ed'	=> 12,
	),

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
