<?php
$field = 'field';

$config['sport'] = array(
	'field' => $field,
	'field_cap' => Inflector::humanize($field),
	'fields' => Inflector::pluralize($field),
	'fields_cap' => Inflector::humanize(Inflector::pluralize($field)),

	'roster_requirements' => array(
		'4/3'	=> 12,
		'5/2'	=> 12,
		'3/3'	=> 10,
		'4/2'	=> 10,
		'3/2'	=> 8,
		'2/2'	=> 7,
		'womens'=> 12,
		'mens'	=> 12,
		'open'	=> 12,
	),

	'positions' => array(
		'unspecified' => 'Unspecified',
		'handler' => 'Handler',
		'cutter' => 'Cutter',
		'striker' => 'Striker',
		'olinehandler' => 'O Line Handler',
		'olinecutter' => 'O Line Cutter',
		'olinestriker' => 'O Line Striker',
		'dlinehandler' => 'D Line Handler',
		'dlinecutter' => 'D Line Cutter',
		'dlinestriker' => 'D Line Striker',
	),

	'score_options' => array(
		'Goal' => 1,
	),

	'other_options' => array(
		'Half' => 'Pull to start second half',
		'Injury' => 'Injury substitution',
	),

	'rating_questions' => array(
		'Skill' => array(
			'Compared to other players of the same sex as you, would you consider yourself:' => array(
				0 => __('One of the slowest', true),
				1 => __('Slower than most', true),
				2 => __('Average speed', true),
				3 => __('Faster than most', true),
				4 => __('One of the fastest', true),
			),

			'How would you describe your throwing skills?' => array(
				0 => __('just learning, only backhand throw, no forehand', true),
				1 => __('can make basic throws, perhaps weaker forehand, some distance and accuracy, nervous when handling the disc', true),
				2 => __('basic throws (backhand and forehand), some distance and accuracy, not very consistent, somewhat intimidated when handling the disc, can handle on most lower-tier teams', true),
				3 => __('good basic throws (backhand and forehand), good distance and accuracy, fairly consistent, relatively comfortable when handling disc, can handle on most lower to mid-tier teams', true), 
				4 => __('very good basic throws, know some other kinds of throws, very good distance and accuracy, usually consistent quality throws, confident when handling the disc, can handle on most mid to upper-tier teams', true),
				5 => __('all kinds of throws, excellent distance and accuracy, not prone to errors of judgment, can handle on most top-tier and lower-competitive teams', true),
				6 => __('all kinds of throws, very rarely make a bad throw, excellent distance, near perfect accuracy, epitome of reliability, can handle on an elite team (mid-highly competitive team)', true),
			),

			'How would you rate your catching skills?' => array(
				0 => __('can make basic catches if they\'re straight to me, still learning to judge the flight path of the disc', true),
				1 => __('can make basic catches, sometimes have difficulty judging the flight path of the disc', true),
				2 => __('can make most catches, good at judging the flight path of the disc, not likely to attempt a layout', true),
				3 => __('can catch almost everything (high, low, to the side), rarely misread the disc, will layout if necessary', true),
				4 => __('catch absolutely everything thrown towards me, and most of the swill that isn\'t', true),
			),

			'With respect to playing defense, you:' => array(
				0 => __('understand some basics, and are learning how to read the play, no/limited experience with defense strategies', true),
				1 => __('know the basics, but you\'re sometimes behind the play, learned a bit about man defense strategies', true),
				2 => __('can stay with the play and sometimes make the D, understand the basics of man & zone style defense strategies ', true),
				3 => __('can read and anticipate the play and get in position to increase the chances of make the D, comfortable with both man/zone style defense strategies', true),
				4 => __('always think ahead of the play and can often make the D, proficient at both man/zone style defense strategies and maybe know a few more', true),
			),

			'With respect to playing offense, you:' => array(
				0 => __('are still learning the basic strategy, not quite sure where to go or when to cut', true),
				1 => __('have the basic idea of where/when/how cuts should be made, starting to be able to do it, basic knowledge of a stack', true),
				2 => __('can make decent cuts, understand the stack, can play at least one of handler/striker/popper/etc, understand the concept of the dump & swing', true),
				3 => __('can make good cuts, can play any of handler/striker/popper/etc, comfortable handling, rarely throw away the disc or get blocked', true),
				4 => __('proficient cutter, experienced handler, can play any position, understand many offensive strategies', true),
			),
		),

		'Experience' => array(

			'For how many years have you been playing Ultimate?' => array(
				0 => __('0 years', true),
				1 => __('1-2 years', true),
				2 => __('3-5 years', true),
				3 => __('6-8 years', true),
				4 => __('9+ years', true),
			),

			'What is the highest level at which you regularly play?' => array(
				0 => __('Recreational League', true),
				1 => __('Intermediate League or Recreational Tournament', true),
				2 => __('Competitive League or Intermediate Tournament', true),
				3 => __('Competitive Tournament (top 8 at a high-caliber tournament or bottom half at Nationals', true),
				4 => __('Elite Tournament (top half at Nationals)', true),
			),

			'Over the past few summers, how many nights during the week did you play Ultimate? (Organized practices and regular pick-up count.)' => array(
				0 => __('0 nights per week', true),
				1 => __('1 night per week', true),
				2 => __('2 nights per week', true),
				3 => __('3 nights per week', true),
				4 => __('more than 3 nights per week', true),
			),

			'Over the past few years, when did you normally play Ultimate?' => array(
				0 => __('The occasional pick-up game', true),
				1 => __('The occasional tournament', true),
				2 => __('1 season (e.g. Summer, Fall or Winter)', true),
				3 => __('2 seasons', true),
				4 => __('Year-round', true),
			),

			'If there was a disagreement on the field about a certain play, the majority of the time you would be able to:' => array(
				0 => __('not do much because you don\'t know all the rules yet ', true),
				1 => __('quote what you think is the rule, and agree with the other player/captain to go with that', true),
				// 2 intentionally omitted to give this question equal weight to the others
				3 => __('use a copy of the rules to find the exact rule that addresses the problem', true),
				4 => __('quote the exact rule from memory that addresses the problem', true),
			),
		),
	),
);

$config['sport']['ratio'] = make_human_options(array_keys($config['sport']['roster_requirements']));

?>
