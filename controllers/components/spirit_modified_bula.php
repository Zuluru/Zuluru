<?php

/**
 * Derived class for implementing functionality for spirit scoring by the modified BULA questionnaire.
 */

class SpiritModifiedBulaComponent extends SpiritComponent
{
	var $description = 'The Modified BULA spirit survey was developed by Mile Zero Ultimate, based on the original BULA spirit survey. (BULA now uses the WFDF standard spirit survey, which was also based on BULA\'s original.)';

	var $questions = array(
		'q1' => array(
			'name' => 'Respect',
			'text' => 'They communicated objectively and without aggressive language. They were willing to believe calls were made in good faith.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q2' => array(
			'name' => 'Fair-Mindness',
			'text' => 'Players pointed out their own fouls. They corrected their own player calls. In an important situation they admitted that the opponent was probably right.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q3' => array(
			'name' => 'Positive attitude',
			'text' => 'They introduced themselves to the opponent. They complimented the opponent for good plays. Left a positive impression in an after-the-game Spirit Circle.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q4' => array(
			'name' => 'Emotional Management',
			'text' => 'Their reaction towards disagreements, successes, and mistakes was appropriately mature.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q5' => array(
			'name' => 'Avoiding Body Contact',
			'text' => 'They were aware of other player\'s body location and movement and avoided dangerous plays.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q6' => array(
			'name' => 'Avoid violations and Fouls',
			'text' => 'They tried to avoid fouls and violations. Their marks were legal.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q7' => array(
			'name' => 'Knowledge of the Rules',
			'text' => 'They knew the rules and/or had the willingness to learn and teach them. They did not make unjustified calls.',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
					'value' => 0,
				),
			),
		),
		'q8' => array(
			'name' => 'Their Spirit compared to ours',
			'text' => 'How was their spirit compared to our own spirit?',
			'type' => 'radio',
			'options' => array(
				'2' => array(
					'text' => 'They did more than expected in this category',
					'value' => 2,
				),
				'1' => array(
					'text' => 'They did average in this category',
					'value' => 1,
				),
				'0' => array(
					'text' => 'They did poorly in this category',
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
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q2' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q3' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q4' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q5' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q6' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q7' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
		'q8' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 2),
				'message' => 'Select one of the given options',
			),
		),
	);

	/**
	 * Default mappings from ratios to symbol filenames. These may
	 * be overridden by specific implementations.
	 */
	var $ratios = array(
		'perfect' => 0.75,
		'ok' => 0.5,
		'caution' => 0.25,
		'not_ok' => 0,
	);
}

?>
