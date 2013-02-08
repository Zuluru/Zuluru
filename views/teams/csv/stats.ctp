<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Name', true),
		__('Gender', true),
);

foreach ($team['Division']['League']['StatType'] as $stat_type) {
	$header[] = $stat_type['name'];
}
fputcsv($fp, $header);

foreach ($team['Person'] as $person) {
	$data = array(
		$person['full_name'],
		$person['gender'],
	);

	foreach ($team['Division']['League']['StatType'] as $stat_type) {
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

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
