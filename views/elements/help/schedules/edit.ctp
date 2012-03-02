<p>Schedules are edited on a day-by-day basis with the "Edit Day" link from the league schedule. This link is only available for days that have at least one game that is not yet finalized.</p>
<p>Editing the schedule for a day is a simple matter of selecting which teams will play in each game, and in which game slot.</p>
<?php
echo $this->element('help/topics', array(
		'section' => 'games',
		'topics' => array(
			'edit/publish' => 'Publish',
			'edit/double_header' => 'Double-headers',
		),
		'compact' => true,
));
echo $this->element('help/topics', array(
		'section' => 'divisions',
		'topics' => array(
			'fields' => 'Field Distribution Report',
		),
));
?>
