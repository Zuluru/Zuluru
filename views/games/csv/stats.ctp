<?php
$fp = fopen('php://output','w+');
if (isset($team_id)) {
	$header = array();
	$teams = array($team);
} else {
	$header = array(__('Team', true));
	$teams = array($team, $opponent);
}
$header[] = __('Name', true);
$header[] = __('Gender', true);

foreach ($game['Division']['League']['StatType'] as $stat_type) {
	$header[] = $stat_type['name'];
}
fputcsv($fp, $header);

foreach ($teams as $team) {
	foreach ($team['Person'] as $person) {
		$data = array();
		if (!isset($team_id)) {
			$data[] = $team['name'];
		}

		$person_stats = Set::extract("/Stat[person_id={$person['Person']['id']}][team_id={$team['id']}]", $game);

		$data[] = $person['Person']['full_name'];
		$data[] = $person['Person']['gender'];

		foreach ($game['Division']['League']['StatType'] as $stat_type) {
			$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
			if (!empty($value)) {
				$data[] = $value[0];
			} else {
				$data[] = 0;
			}
		}

		// Output the data row
		fputcsv($fp, $data);
	}

	$data = array();
	if (!isset($team_id)) {
		$data[] = $team['name'];
	}

	$person_stats = Set::extract("/Stat[person_id=0][team_id={$team['id']}]", $game);

	$data[] = __('Subs', true);
	$data[] = '';

	foreach ($game['Division']['League']['StatType'] as $stat_type) {
		$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $person_stats);
		if (!empty($value)) {
			$data[] = $value[0];
		} else {
			$data[] = 0;
		}
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
