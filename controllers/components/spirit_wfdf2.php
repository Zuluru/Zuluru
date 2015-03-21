<?php

/**
 * Derived class for implementing functionality for spirit scoring by the updated WFDF questionnaire.
 */

class SpiritWfdf2Component extends SpiritComponent
{
	var $description = 'The 2014 WFDF standard spirit survey.';

	var $questions = array(
		'q1' => array(
			'name' => 'Rules Knowledge',
			'text' => 'Rules Knowledge and Use',
			'desc' => 'Examples: They did not purposefully misinterpret the rules. They kept to time limits. When they didn\'t know the rules they showed a real willingness to learn.',
			'type' => 'radio',
			'options' => array(
				'PoorRules' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodRules' => array(
					'text' => 'not good',
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
			'desc' => 'Examples: They avoided fouling, contact, and dangerous plays.',
			'type' => 'radio',
			'options' => array(
				'PoorFouls' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodFouls' => array(
					'text' => 'not good',
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
			'desc' => 'Examples: They apologized in situations where it was appropriate, informed teammates about wrong/unnecessary calls. Only called significant breaches.',
			'type' => 'radio',
			'options' => array(
				'PoorFairMindedness' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodFairMindedness' => array(
					'text' => 'not good',
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
			'desc' => 'Examples: They were polite. They played with appropriate intensity irrespective of the score. They left an overall positive impression during and after the game.',
			'type' => 'radio',
			'options' => array(
				'PoorAttitude' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodAttitude' => array(
					'text' => 'not good',
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
			'name' => 'Communication',
			'text' => 'Communication',
			'desc' => 'Examples: They communicated respectfully. They listened. They kept to discussion time limits.',
			'type' => 'radio',
			'options' => array(
				'PoorCommunication' => array(
					'text' => 'poor',
					'value' => 0,
				),
				'NotGoodCommunication' => array(
					'text' => 'not good',
					'value' => 1,
				),
				'GoodCommunication' => array(
					'text' => 'good',
					'value' => 2,
					'default' => true,
				),
				'VeryGoodCommunication' => array(
					'text' => 'very good',
					'value' => 3,
				),
				'ExcellentCommunication' => array(
					'text' => 'excellent',
					'value' => 4,
				),
			),
		),
		'comments' => array(
			'name' => 'Comments',
			'text' => 'If you have selected Poor in any category, please explain in few words what happened. Negative feedback will be passed to the teams in the appropriate manner.',
			'type' => 'text',
			'restricted' => true,
		),
		'highlights' => array(
			'name' => 'Highlights',
			'text' => 'If you have selected Excellent in any category, please explain in few words what happened. Compliments will be passed to the teams in the appropriate manner.',
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
