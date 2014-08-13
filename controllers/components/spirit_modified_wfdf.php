<?php

/**
 * Derived class for implementing functionality for spirit scoring by the WODS questionnaire.
 */

class SpiritModifiedWfdfComponent extends SpiritComponent
{
	var $description = 'The Modified WFDF spirit survey was developed by the Waterloo Organization of Disc Sports to reflect league play rather than tournaments; some WFDF questions have been split, for example, and answers have been simplified from five to three. The survey answers are intended to reward good spirit rather than penalizing bad.';

	var $questions = array(
		'q1' => array(
			'name' => 'Respect',
			'text' => 'Respect',
			'desc' => 'They communicated objectively and without aggressive language. They were willing to believe calls were made in good faith. Were on time. Kept to time limits for discussions, time-outs, between points, etc.',
			'type' => 'radio',
			'options' => array(
				'poorRespect' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageRespect' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalRespect' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q2' => array(
			'name' => 'Fair-Mindedness',
			'text' => 'Fair-Mindedness',
			'desc' => 'Players pointed out their own fouls. They corrected their own team player calls. In an important situation they admitted that the opponent was probably right. Avoided frequently calling non-obvious travels and picks.',
			'type' => 'radio',
			'options' => array(
				'poorFairMindedness' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageFairMindedness' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalFairMindedness' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q3' => array(
			'name' => 'Attitude',
			'text' => 'Positive Attitude',
			'desc' => 'They introduced themselves to the opponent. They complimented the opponent for good plays.  Left a positive impression in an after-the-game Spirit Circle, etc.',
			'type' => 'radio',
			'options' => array(
				'poorPositiveAttitude' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averagePositiveAttitude' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalPositiveAttitude' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q4' => array(
			'name' => 'Emotional Management',
			'text' => 'Emotional Management',
			'desc' => 'Their reaction towards disagreements, successes, and mistakes was appropriately mature.',
			'type' => 'radio',
			'options' => array(
				'poorEmotionalManagement' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageEmotionalManagement' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalEmotionalManagement' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q5' => array(
			'name' => 'Body Contact',
			'text' => 'Avoiding Body Contact',
			'desc' => 'They were aware of other players\' body location and movement and avoided dangerous plays.',
			'type' => 'radio',
			'options' => array(
				'poorAvoidingBodyContact' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageAvoidingBodyContact' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalAvoidingBodyContact' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q6' => array(
			'name' => 'Fouls',
			'text' => 'Avoiding Violations and Fouls',
			'desc' => 'They tried to avoid fouls and violations. Their marks were legal. They did not commit off side violations, etc.',
			'type' => 'radio',
			'options' => array(
				'poorAvoidingViolationsandFouls' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageAvoidingViolationsandFouls' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalAvoidingViolationsandFouls' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q7' => array(
			'name' => 'Rules Knowledge',
			'text' => 'Knowledge of the Rules',
			'desc' => 'They knew the rules and/or had the willingness to learn and teach them. They did not make unjustified calls.',
			'type' => 'radio',
			'options' => array(
				'poorKnowledgeoftheRules' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageKnowledgeoftheRules' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalKnowledgeoftheRules' => array(
					'text' => 'Above Average',
					'value' => 2,
				),
			),
		),
		'q8' => array(
			'name' => 'Enjoyment',
			'text' => 'Encouraging Enjoyment of the Game',
			'desc' => 'They played the game in a way that made the game enjoyable for all those involved.',
			'type' => 'radio',
			'options' => array(
				'poorEncouragingEnjoymentoftheGame' => array(
					'text' => 'Below Average',
					'value' => 0,
				),
				'averageEncouragingEnjoymentoftheGame' => array(
					'text' => 'Average',
					'value' => 1,
					'default' => true,
				),
				'exceptionalEncouragingEnjoymentoftheGame' => array(
					'text' => 'Above Average',
					'value' => 2,
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
			'entered_sotg' => 8,
			'assigned_sotg' => 8,
			'score_entry_penalty' => 0,
			'q1' => 1,
			'q2' => 1,
			'q3' => 1,
			'q4' => 1,
			'q5' => 1,
			'q6' => 1,
			'q7' => 1,
			'q8' => 1,
		);
	}
}

?>
