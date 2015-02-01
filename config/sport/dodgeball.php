<?php
$field = 'court';

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
		'6 (min 2 women)'       => 6,
	),

	'positions' => array(
	),

	'score_options' => array(
		// TODO
	),

	'other_options' => array(
		// TODO
	),

	'rating_questions' => false,
);

if (file_exists(CONFIGS . 'sport/dodgeball_custom.php')) {
	include(CONFIGS . 'sport/dodgeball_custom.php');
}

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
