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
if (Configure::read('scoring.most_spirited') && $division['Division']['most_spirited'] != 'never') {
	$header[] = __('Most Spirited', true);
}
fputcsv($fp, $header);

$teams = Set::extract('/Team/id', $division);

$defaulted_entry = $spirit_obj->defaulted();
$automatic_entry = $spirit_obj->expected();

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

			$spirit_entry = null;
			if (Configure::read('scoring.spirit_default')) {
				if (strpos ($game['Game']['status'], 'default') !== false) {
					$spirit_entry = $defaulted_entry;
				} else {
					$spirit_entry = $automatic_entry;
				}
			}

			foreach ($game['SpiritEntry'] as $entry) {
				if ($entry['team_id'] == $id) {
					$spirit_entry = $entry;
					$spirit_entry['assigned_sotg'] = $spirit_obj->calculate ($spirit_entry);
				}
			}

			if ($division['League']['numeric_sotg']) {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry['entered_sotg'];
				} else {
					$team_results[$id][] = '';
				}
			}
			if ($division['League']['sotg_questions'] != 'none') {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry['assigned_sotg'];
				} else {
					$team_results[$id][] = '';
				}
			}
			foreach ($spirit_obj->questions as $question => $detail) {
				if ($spirit_entry) {
					$team_results[$id][] = $spirit_entry[$question];
				} else {
					$team_results[$id][] = '';
				}
			}
			if (Configure::read('scoring.most_spirited') && $division['Division']['most_spirited'] != 'never') {
				if (!empty($spirit_entry['most_spirited'])) {
					$team_results[$id][] = $spirit_entry['MostSpirited']['full_name'];
				} else {
					$team_results[$id][] = '';
				}
			}
		}
	}
}

foreach($team_results as $row) {
	fputcsv($fp, $row);
}

fclose($fp);

?>
