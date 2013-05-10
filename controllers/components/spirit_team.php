<?php

/**
 * Derived class for implementing functionality for spirit scoring by the original team questionnaire.
 */

class SpiritTeamComponent extends SpiritComponent
{
	var $questions = array(
		'q1' => array(
			'name' => 'Timeliness',
			'text' => 'Our opponents had a full line and were ready to play...',
			'type' => 'radio',
			'options' => array(
				'OnTime' => array(
					'text' => 'early, or at the official start time',
					'value' => 3,
					'default' => true,
				),
				'FiveOrLess' => array(
					'text' => 'less than five minutes late',
					'value' => 2,
				),
				'LessThanTen' => array(
					'text' => 'less than ten minutes late',
					'value' => 1,
				),
				'MoreThanTen' => array(
					'text' => 'more than ten minutes late',
					'value' => 0,
				),
			),
		),
		'q2' => array(
			'name' => 'Rules Knowledge',
			'text' => 'Our opponents\' rules knowledge was...',
			'type' => 'radio',
			'options' => array(
				'ExcellentRules' => array(
					'text' => 'excellent',
					'value' => 3,
					'default' => true,
				),
				'AcceptableRules' => array(
					'text' => 'acceptable',
					'value' => 2,
				),
				'PoorRules' => array(
					'text' => 'poor',
					'value' => 1,
				),
				'NonexistantRules' => array(
					'text' => 'nonexistant',
					'value' => 0,
				),
			),
		),
		'q3' => array(
			'name' => 'Sportsmanship',
			'text' => 'Our opponents\' sportsmanship was...',
			'type' => 'radio',
			'options' => array(
				'ExcellentSportsmanship' => array(
					'text' => 'excellent',
					'value' => 3,
					'default' => true,
				),
				'AcceptableSportsmanship' => array(
					'text' => 'acceptable',
					'value' => 2,
				),
				'PoorSportsmanship' => array(
					'text' => 'poor',
					'value' => 1,
				),
				'NonexistantSportsmanship' => array(
					'text' => 'nonexistant',
					'value' => 0,
				),
			),
		),
		'q4' => array(
			'name' => 'Enjoyment',
			'text' => 'Ignoring the score and based on the opponents\' spirit of the game, did your team enjoy this game?',
			'type' => 'radio',
			'options' => array(
				'AllEnjoyed' => array(
					'text' => 'all or most of my players did',
					'value' => 1,
					'default' => true,
				),
				/*
				'MostEnjoyed' => array(
					'text' => 'most of my players did',
					'value' => 0,
				),
				'SomeEnjoyed' => array(
					'text' => 'some of my players did',
					'value' => 0,
				),
				*/
				'NoneEnjoyed' => array(
					'text' => 'some or none of my players did',
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
				'rule' => array('inclusive_range', 0, 3),
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
				'rule' => array('inclusive_range', 0, 1),
				'message' => 'Select one of the given options',
			),
		),
	);
}

?>
