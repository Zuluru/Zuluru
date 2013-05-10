<?php

/**
 * Derived class for implementing functionality for spirit scoring by the WFDF questionnaire.
 */

class SpiritWfdfComponent extends SpiritComponent
{
	var $questions = array(
		'q1' => array(
			'name' => 'Rules Knowledge',
			'text' => 'Rules Knowledge and Use',
			'desc' => 'For example: They did not make unjustified calls. They did not purposefully misinterpret the rules. They kept to time limits. They were willing to teach and/or learn the rules.',
			'type' => 'radio',
			'options' => array(
				'PoorRules' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodRules' => array(
					'text' => 'not so good',
					'value' => 1,
				),
				'GoodRules' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodRules' => array(
					'text' => 'very good',
					'value' => 3,
				),
				'ExcellentRules' => array(
					'text' => 'excellent',
					'value' => 4,
				),
			),
		),
		'q2' => array(
			'name' => 'Fouls',
			'text' => 'Fouls and Body Contact',
			'desc' => 'For example: They avoided fouling, contact, and dangerous plays.',
			'type' => 'radio',
			'options' => array(
				'PoorFouls' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodFouls' => array(
					'text' => 'not so good',
					'value' => 1,
				),
				'GoodFouls' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodFouls' => array(
					'text' => 'very good',
					'value' => 3,
				),
				'ExcellentFouls' => array(
					'text' => 'excellent',
					'value' => 4,
				),
			),
		),
		'q3' => array(
			'name' => 'Fair-Mindedness',
			'text' => 'Fair-Mindedness',
			'desc' => 'For example: They apologized for their own fouls. They informed teammates when they made wrong or unnecessary calls. They were willing to admit that we were right and retracted their call.',
			'type' => 'radio',
			'options' => array(
				'PoorFairMindedness' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodFairMindedness' => array(
					'text' => 'not so good',
					'value' => 1,
				),
				'GoodFairMindedness' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodFairMindedness' => array(
					'text' => 'very good',
					'value' => 3,
				),
				'ExcellentFairMindedness' => array(
					'text' => 'excellent',
					'value' => 4,
				),
			),
		),
		'q4' => array(
			'name' => 'Attitude',
			'text' => 'Positive Attitude and Self-Control',
			'desc' => 'For example: They introduced themselves. They communicated without derogatory or aggressive language. They complimented us on our good plays. They left an overall positive impression during and after the game, e.g. during the Spirit circle.',
			'type' => 'radio',
			'options' => array(
				'PoorAttitude' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodAttitude' => array(
					'text' => 'not so good',
					'value' => 1,
				),
				'GoodAttitude' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodAttitude' => array(
					'text' => 'very good',
					'value' => 3,
				),
				'ExcellentAttitude' => array(
					'text' => 'excellent',
					'value' => 4,
				),
			),
		),
		'q5' => array(
			'name' => 'Comparison',
			'text' => 'Our Spirit compared to theirs',
			'desc' => 'How did our team compare to theirs with regards to rules knowledge, body contact, fair-mindedness, positive attitude and self-control?',
			'type' => 'radio',
			'options' => array(
				'PoorSpirit' => array(
					'text' => 'Our spirit was much better',
					'value' => 0,
				),
				'NotGoodSpirit' => array(
					'text' => 'Our spirit was slightly better',
					'value' => 1,
				),
				'GoodSpirit' => array(
					'text' => 'Our spirit was the same',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodSpirit' => array(
					'text' => 'Our spirit was slightly worse',
					'value' => 3,
				),
				'ExcellentSpirit' => array(
					'text' => 'Our spirit was much worse',
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
			'q1' => 3,
			'q2' => 3,
			'q3' => 3,
			'q4' => 3,
			'q5' => 3,
		);
	}
}

?>
