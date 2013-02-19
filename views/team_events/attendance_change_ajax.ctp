<?php
$args = array(
	'team' => $team,
	'person_id' => $person['id'],
	'role' => $person['Team'][0]['TeamsPerson']['role'],
	'status' => $status,
	'comment' => $comment,
	'event_id' => $event['TeamEvent']['id'],
	'date' => $event['TeamEvent']['date'],
	'time' => $event['TeamEvent']['start'],
);
echo $this->element('team_events/attendance_change', $args);
?>