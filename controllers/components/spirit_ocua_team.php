<?php

/**
 * Derived class for implementing functionality for spirit scoring by the modified OCUA team questionnaire.
 */

class SpiritOcuaTeamComponent extends SpiritComponent
{
	var $description = 'The modified Leaguerunner spirit survey, developed by the Ottawa Carleton Ultimate Association. Compared to the original Leaguerunner spirit survey, this one emphasizes enjoyment by adding more options there, while decreasing the number of timeliness options.';

	var $questions = array(
		'q1' => array(
			'name' => 'Timeliness',
			'text' => 'Our opponents\' timeliness:',
			'type' => 'radio',
			'options' => array(
				'MetExpectations' => array(
					'text' => 'met expectations',
					'value' => 1,
					'default' => true,
				),
				'DidNotMeet' => array(
					'text' => 'did not meet expectations',
					'value' => 0,
				),
			),
		),
		'q2' => array(
			'name' => 'Rules',
			'text' => 'Our opponents\' rules knowledge was:',
			'type' => 'radio',
			'options' => array(
				'ExceptionalRules' => array(
					'text' => 'exceptional',
					'value' => 3,
				),
				'GoodRules' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'BelowAvgRules' => array(
					'text' => 'below average',
					'value' => 1,
				),
				'BadRules' => array(
					'text' => 'bad',
					'value' => 0,
				),
			),
		),
		'q3' => array(
			'name' => 'Sportsmanship',
			'text' => 'Our opponents\' sportsmanship was:',
			'type' => 'radio',
			'options' => array(
				'ExceptionalSportsmanship' => array(
					'text' => 'exceptional',
					'value' => 3,
				),
				'GoodSportsmanship' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'BelowAvgSportsmanship' => array(
					'text' => 'below average',
					'value' => 1,
				),
				'PoorSportsmanship' => array(
					'text' => 'poor',
					'value' => 0,
				),
			),
		),
		'q4' => array(
			'name' => 'Overall',
			'text' => 'Ignoring the score and based on the opponents\' spirit of the game, what was your overall assessment of the game?',
			'type' => 'radio',
			'options' => array(
				'Exceptional' => array(
					'text' => 'This was an exceptionally great game',
					'value' => 3,
				),
				'Enjoyable' => array(
					'text' => 'This was an enjoyable game',
					'value' => 2,
					'default' => true,
				),
				'Mediocre' => array(
					'text' => 'This was a mediocre game',
					'value' => 1,
				),
				'VeryBad' => array(
					'text' => 'This was a very bad game',
					'value' => 0,
				),
			),
		),
		'comments' => array(
			'name' => 'Comments',
			'text' => 'Do you have any concerns from this game that you would like to bring to the coordinator\'s attention? These will be kept confidential.',
			'type' => 'text',
			'restricted' => true,
		),
		'highlights' => array(
			'name' => 'Highlights',
			'text' => 'Do you have any spirit highlights from this game that you would like to bring to the coordinator\'s attention? These may be published.',
			'type' => 'text',
			'restricted' => true,
		),
	);

	var $validate = array(
		'q1' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 1),
				'message' => 'Select one of the given options',
			),
		),
		'q2' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 3),
				'message' => 'Select one of the given options',
			),
		),
		'q3' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 3),
				'message' => 'Select one of the given options',
			),
		),
		'q4' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 3),
				'message' => 'Select one of the given options',
			),
		),
	);

	var $ratios = array(
		'perfect' => 0.9,
		'ok' => 0.6,
		'caution' => 0.4,
		'not_ok' => 0,
	);
}

?>
