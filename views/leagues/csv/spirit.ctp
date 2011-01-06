<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Team', true),
		__('TeamID', true),
		__('Opponent', true),
		__('Team Score', true),
		__('Opp Score', true),
		__('Spirit', true),
		__('Calc Spirit', true),
);
foreach ($spirit_obj->questions as $question => $detail) {
	$header[] = __($detail['name'], true);
}
fputcsv($fp, $header);

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

$team_results = array();
foreach ($league['Game'] as $game) {
	foreach (array('HomeTeam' => 'AwayTeam', 'AwayTeam' => 'HomeTeam') as $team => $opp) {
		$id = $game[$team]['id'];
		if (!array_key_exists ($id, $team_results)) {
			$team_results[$id] = array(
				$game[$team]['name'],
				$game[$team]['id'],
			);
		}
		$team_results[$id][] = $game[$opp]['name'];
		$team_results[$id][] = ($team == 'HomeTeam' ? $game['home_score'] : $game['away_score']);
		$team_results[$id][] = ($team == 'HomeTeam' ? $game['away_score'] : $game['home_score']);

		if (strpos ($game['status'], 'default') !== false) {
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
		$team_results[$id][] = $spirit_entry['entered_sotg'];
		$team_results[$id][] = $spirit_entry['assigned_sotg'];
		foreach ($spirit_obj->questions as $question => $detail) {
			$team_results[$id][] = $spirit_entry[$question];
		}
	}
}

foreach($team_results as $row) {
	fputcsv($fp, $row);
}

fclose($fp);

?>
