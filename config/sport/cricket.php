<?php
$field = 'pitch';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'start' => array(
		'stat_sheet' => 'First bats',
		'stat_sheet_direction' => false,
		'live_score' => 'Batting team',
		'box_score' => '%s batting',
		'twitter' => '%s batting',
	),

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

	'other_options' => array(
		// TODO
	),

	'rating_questions' => false,
);

if (file_exists(CONFIGS . 'sport/cricket_custom.php')) {
	include(CONFIGS . 'sport/cricket_custom.php');
}

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
