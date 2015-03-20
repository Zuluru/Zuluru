<?php

/**
 * Derived class for implementing functionality for spirit scoring by Sushi Suzuki's Alternate questionnaire.
 */

class SpiritSuzukiComponent extends SpiritComponent
{
	var $description = 'Sushi Suzuki\'s <a href="http://www.sushi-suzuki.com/sushilog/2014/12/the-alternate-spirit-of-the-game-score-sheet/">alternate spirit survey</a>, intended "for tournaments where the official WFDF SOTG score sheet may feel \"too serious.\"';

	var $questions = array(
		'q1' => array(
			'name' => 'Fair Play',
			'text' => 'Fair Play',
			'desc' => 'The team tried to win fair and square, no cheap calls or taking advantage of the rules.',
			'type' => 'radio',
			'options' => array(
				'PoorFairPlay' => array(
					'text' => '0: Full of cheaters',
					'value' => 0,
				),
				'NotGoodFairPlay' => array(
					'text' => '1',
					'value' => 1,
				),
				'GoodFairPlay' => array(
					'text' => '2',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodFairPlay' => array(
					'text' => '3',
					'value' => 3,
				),
				'ExcellentFairPlay' => array(
					'text' => '4: Full of angels',
					'value' => 4,
				),
			),
		),
		'q2' => array(
			'name' => 'Intensity',
			'text' => 'Intensity',
			'desc' => 'Full sprints, hard cuts, layouts, etc. How intense was the team?',
			'type' => 'radio',
			'options' => array(
				'PoorIntensity' => array(
					'text' => '0: Sloth-like',
					'value' => 0,
				),
				'NotGoodIntensity' => array(
					'text' => '1',
					'value' => 1,
				),
				'GoodIntensity' => array(
					'text' => '2',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodIntensity' => array(
					'text' => '3',
					'value' => 3,
				),
				'ExcellentIntensity' => array(
					'text' => '4: Like the blinding sun',
					'value' => 4,
				),
			),
		),
		'q3' => array(
			'name' => 'Daringness',
			'text' => 'Daringness',
			'desc' => 'Hucks, hammers, scoobers, "Wow! did he/she really do that?"',
			'type' => 'radio',
			'options' => array(
				'PoorDaringness' => array(
					'text' => '0: Snore bore',
					'value' => 0,
				),
				'NotGoodDaringness' => array(
					'text' => '1',
					'value' => 1,
				),
				'GoodDaringness' => array(
					'text' => '2',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodDaringness' => array(
					'text' => '3',
					'value' => 3,
				),
				'ExcellentDaringness' => array(
					'text' => '4: OMG WTF',
					'value' => 4,
				),
			),
		),
		'q4' => array(
			'name' => 'Spirit Speech / Sense of Humor',
			'text' => ' Spirit Speech',
			'desc' => 'How much laughter did the other team induce during the match and in the spirit speech?',
			'type' => 'radio',
			'options' => array(
				'PoorSpiritSpeech' => array(
					'text' => '0: Somber as a funeral',
					'value' => 0,
				),
				'NotGoodSpiritSpeech' => array(
					'text' => '1',
					'value' => 1,
				),
				'GoodSpiritSpeech' => array(
					'text' => '2',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodSpiritSpeech' => array(
					'text' => '3',
					'value' => 3,
				),
				'ExcellentSpiritSpeech' => array(
					'text' => '4: Better than many comedians',
					'value' => 4,
				),
			),
		),
		'q5' => array(
			'name' => 'Fun',
			'text' => 'Fun',
			'desc' => 'How entertaining was the match? Would you do this again?',
			'type' => 'radio',
			'options' => array(
				'PoorFun' => array(
					'text' => '0: Never ever again',
					'value' => 0,
				),
				'NotGoodFun' => array(
					'text' => '1',
					'value' => 1,
				),
				'GoodFun' => array(
					'text' => '2',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodFun' => array(
					'text' => '3',
					'value' => 3,
				),
				'ExcellentFun' => array(
					'text' => '4: I wish every game was like this',
					'value' => 4,
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

	var $ratios = array(
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	);

	function expected() {
		return array(
			'entered_sotg' => 15,
			'assigned_sotg' => 15,
			'score_entry_penalty' => 0,
			'q1' => 3,
			'q2' => 3,
			'q3' => 3,
			'q4' => 3,
			'q5' => 3,
		);
	}
}

?>
