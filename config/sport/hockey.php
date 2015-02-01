<?php
$field = 'rink';

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
		'womens'=> 10,
		'mens'	=> 10,
		'co-ed'	=> 10,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'goalie' => 'Goalie',
		'defence' => 'Defence',
		'forward' => 'Forward',
		'leftwinger' => 'Left Winger',
		'center' => 'Center',
		'rightwinger' => 'Right Winger',
	),

	'score_options' => array(
		'Goal' => 1,
	),

	'other_options' => array(
		// TODO
	),

	'rating_questions' => false,
);

if (file_exists(CONFIGS . 'sport/hockey_custom.php')) {
	include(CONFIGS . 'sport/hockey_custom.php');
}

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
