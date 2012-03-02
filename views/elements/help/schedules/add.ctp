<p>To add games to a schedule, you select the type of schedule to create, whether to publish games, and whether double-headers are allowed.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'schedules',
		'topics' => array(
			'add/schedule_type' => 'Schedule Type',
		),
));
echo $this->element('help/topics', array(
		'section' => 'divisions',
		'topics' => array(
			'edit/exclude_teams' => 'Exclude Teams',
		),
));
echo $this->element('help/topics', array(
		'section' => 'games',
		'topics' => array(
			'edit/start_date' => 'Start Date',
		),
));
?>
