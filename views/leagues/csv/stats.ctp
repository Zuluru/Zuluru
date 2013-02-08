<?php
$fp = fopen('php://output','w+');
$header1 = array(
		'',
		'',
);

$header2 = array(
		__('Name', true),
		__('Gender', true),
);

foreach ($team['Game'] as $key => $game) {
	$game_name = 'Game ' . ($key + 1) . ': ' . $this->ZuluruTime->datetime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}");
	foreach ($team['Division']['League']['StatType'] as $stat_type) {
		if (in_array($stat_type['type'], Configure::read('stat_types.game'))) {
			$header1[] = $game_name;
			$game_name = '';
			$header2[] = $stat_type['name'];
		}
	}
}

$season = __('Season', true);
foreach ($team['Division']['League']['StatType'] as $stat_type) {
	if (in_array($stat_type['type'], Configure::read('stat_types.team'))) {
		$header1[] = $season;
		$season = '';
		$header2[] = $stat_type['name'];
	}
}

fputcsv($fp, $header1);
fputcsv($fp, $header2);

foreach ($team['Person'] as $person) {
	$data = array(
		$person['full_name'],
		$person['gender'],
	);
	$person_stats = Set::extract("/Stat[person_id={$person['id']}]", $team);

	foreach ($team['Game'] as $key => $game) {
		$game_stats = Set::extract("/Stat[game_id={$game['Game']['id']}]", $person_stats);
		foreach ($team['Division']['League']['StatType'] as $stat_type) {
			if (in_array($stat_type['type'], Configure::read('stat_types.game'))) {
				if (empty($game_stats)) {
					$data[] = '';
				} else {
					$value = Set::extract("/Stat[stat_type_id={$stat_type['id']}]/value", $game_stats);
					if (!empty($value)) {
						$data[] = $value[0];
					} else {
						$data[] = 0;
					}
				}
			}
		}
	}

	foreach ($team['Division']['League']['StatType'] as $stat_type) {
		if (in_array($stat_type['type'], Configure::read('stat_types.team'))) {
			if (!empty($team['Calculated'][$person['id']][$stat_type['id']])) {
				$data[] = $team['Calculated'][$person['id']][$stat_type['id']];
			} else {
				if ($stat_type['type'] == 'season_calc') {
					$data[] = __('N/A', true);
				} else {
					$data[] = 0;
				}
			}
		}
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
