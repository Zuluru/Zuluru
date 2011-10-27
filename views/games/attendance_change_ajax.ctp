<?php
$args = array(
	'team' => $team,
	'person_id' => $person['id'],
	'position' => $person['Team'][0]['TeamsPerson']['position'],
	'status' => $status,
	'comment' => $comment,
);
if (isset ($game)) {
	$args['game_id'] = $game['Game']['id'];
	$args['game_date'] = $game['GameSlot']['game_date'];
	$args['game_time'] = $game['GameSlot']['game_start'];
} else {
	$args['game_date'] = $date;
}
echo $this->element('game/attendance_change', $args);
?>