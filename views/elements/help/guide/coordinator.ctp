<h2>Coordinator Guide</h2>
<p><?php echo ZULURU; ?> provides a number of tools to make a coordinator's job go smoothly, but the number of options can be overwhelming at first.
This guide will walk you through both the common and infrequent tasks, and help you to ensure that things go smoothly for the captains and players in your divisions.</p>
<p>Division coordinator is a position of power and responsibility, so this guide also suggests guidelines for behaviour.</p>

<?php
echo $this->element('help/topics', array(
		'section' => 'divisions',
		'topics' => array(
			'edit' => array(
				'image' => 'edit_32.png',
			),
			'add_teams' => array(
				'image' => 'team_add_32.png',
			),
			'roster_add' => array(
				'image' => 'roster_add_32.png',
			),
			'emails' => array(
				'image' => 'email_32.png',
			),
		),
));

echo $this->element('help/topics', array(
		'section' => 'schedules',
		'topics' => array(
			'add' => array(
				'title' => 'Add Games',
				'image' => 'schedule_add_32.png',
			),
			'edit' => array(
				'title' => 'Schedule Edit',
				'image' => 'edit_32.png',
			),
			'delete' => array(
				'title' => 'Delete Day',
				'image' => 'delete_32.png',
			),
			'reschedule' => array(
				'image' => 'reschedule_32.png',
			),
		),
));

echo $this->element('help/topics', array(
		'section' => 'divisions',
		'topics' => array(
			'approve_scores' => array(
				'image' => 'score_approve_32.png',
			),
		),
));

echo $this->element('help/topics', array(
		'section' => 'schedules',
		'topics' => array(
			'playoffs',
		),
));

echo $this->element('help/topics', array(
		'section' => 'divisions',
		'topics' => array(
			'spirit' => array(
				'title' => 'Spirit Report',
				'image' => 'spirit_32.png',
			),
		),
));

?>
