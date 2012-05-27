<p>To add games to a schedule, you select the type of schedule to create, whether to publish games, and whether double-headers are allowed.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'schedules/add',
		'topics' => array(
			'schedule_type',
		),
		'compact' => true,
));
echo $this->element('help/topics', array(
		'section' => 'games/edit',
		'topics' => array(
			'publish',
			'double_header' => 'Double-headers',
		),
		'compact' => true,
));
echo $this->element('help/topics', array(
		'section' => 'divisions/edit',
		'topics' => array(
			'exclude_teams',
		),
		'compact' => true,
));
echo $this->element('help/topics', array(
		'section' => 'games/edit',
		'topics' => array(
			'start_date',
		),
		'compact' => true,
));
?>
