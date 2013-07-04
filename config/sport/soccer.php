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
		'goalkeeper' => 'Goalkeeper',
		'fullback' => 'Fullback',
		'midfielder' => 'Midfielder',
		'attacker' => 'Attacker',
		'sweeper' => 'Sweeper',
		'centerfullback' => 'Center Fullback',
		'leftfullback' => 'Left Fullback',
		'rightfullback' => 'Right Fullback',
		'leftwingback' => 'Left Wingback',
		'wingback' => 'Wingback',
		'rightwingback' => 'Right Wingback',
		'leftmidfielder' => 'Left Midfielder',
		'defensivemidfielder' => 'Defensive Midfielder',
		'attackingmidfielder' => 'Attacking Midfielder',
		'rightmidfielder' => 'Right Midfielder',
		'leftwinger' => 'Left Winger',
		'striker' => 'Striker',
		'secondstriker' => 'Second Striker',
		'centerforward' => 'Center Forward',
		'rightwinger' => 'Right Winger',
	),

	'score_options' => array(
		'Goal' => 1,
	),

	'rating_questions' => false,
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
