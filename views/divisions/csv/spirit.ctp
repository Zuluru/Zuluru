<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Team', true),
		__('TeamID', true),
		__('Opponent', true),
		__('Team Score', true),
		__('Opp Score', true),
);
if ($division['League']['numeric_sotg']) {
	$header[] = __('Spirit', true);
}
if ($division['League']['sotg_questions'] != 'none') {
	$header[] = __('Calc Spirit', true);
}
foreach ($spirit_obj->questions as $question => $detail) {
	$header[] = __($detail['name'], true);
}
fputcsv($fp, $header);

$teams = Set::extract('/Team/id', $division);
$defaulted_entry = array(
	'entered_sotg' => '',
	'assigned_sotg' => '',
);
$automatic_entry = array(
	'entered_sotg' => $spirit_obj->max(),
	'assigned_sotg' => $spirit_obj->max(),
);
foreach ($spirit_obj->questions as $question => $detail) {
	$defaulted_entry[$question] = '';
	if ($detail['type'] != 'text') {
		$automatic_entry[$question] = $spirit_obj->max($question);
	} else {
		$automatic_entry[$question] = '--';
	}
}

$teams = Set::extract('/Team/id', $division);
$team_results = array();
foreach ($division['Game'] as $game) {
	foreach (array('HomeTeam' => 'AwayTeam', 'AwayTeam' => 'HomeTeam') as $team => $opp) {
		if (Game::_is_finalized($game)) {
			$id = $game[$team]['id'];
			if (!in_array($id, $teams)) {
				continue;
			}
			if (!array_key_exists ($id, $team_results)) {
				$team_results[$id] = array(
					$game[$team]['name'],
					$game[$team]['id'],
				);
			}
			$team_results[$id][] = $game[$opp]['name'];
			$team_results[$id][] = ($team == 'HomeTeam' ? $game['Game']['home_score'] : $game['Game']['away_score']);
			$team_results[$id][] = ($team == 'HomeTeam' ? $game['Game']['away_score'] : $game['Game']['home_score']);

			if (strpos ($game['Game']['status'], 'default') !== false) {
				$spirit_entry = $defaulted_entry;
			} else {
				$spirit_entry = $automatic_entry;
			}

			foreach ($game['SpiritEntry'] as $entry) {
				if ($entry['team_id'] == $id) {
					$spirit_entry = $entry;
					$spirit_entry['assigned_sotg'] = $spirit_obj->calculate ($spirit_entry);
				}
			}
			if ($division['League']['numeric_sotg']) {
				$team_results[$id][] = $spirit_entry['entered_sotg'];
			}
			if ($division['League']['sotg_questions'] != 'none') {
				$team_results[$id][] = $spirit_entry['assigned_sotg'];
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				$team_results[$id][] = $spirit_entry[$question];
			}
		}
	}
}

foreach($team_results as $row) {
	fputcsv($fp, $row);
}

fclose($fp);

?>
