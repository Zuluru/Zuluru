<?php
$fp = fopen('php://output','w+');
$header = array(
		__('User ID', true),
		__('First Name', true),
		__('Last Name', true),
		__('Gender', true),
		__('Skill Level', true),
		__('Birthdate', true),
		__('Year Started', true),
		__('City', true),
);
for ($year = $this->data['start']; $year <= $this->data['end']; ++ $year) {
	foreach ($seasons_found as $name => $season) {
		if ($season['season']) {
			$header[] = $year . ' ' . __($name, true) . ' ' . __('captain', true);
			$header[] = $year . ' ' . __($name, true) . ' ' . __('player', true);
		}
		if ($season['tournament']) {
			$header[] = $year . ' ' . __($name, true) . ' ' . __('tournament', true) . ' ' . __('captain', true);
			$header[] = $year . ' ' . __($name, true) . ' ' . __('tournament', true) . ' ' . __('player', true);
		}
	}
}
foreach ($event_names as $event) {
	$header[] = $event;
}
fputcsv($fp, $header);

foreach ($participation as $person) {
	$data = array(
			$person['Person']['id'],
			$person['Person']['first_name'],
			$person['Person']['last_name'],
			$person['Person']['gender'],
			$person['Person']['skill_level'],
			$person['Person']['birthdate'],
			$person['Person']['year_started'],
			$person['Person']['addr_city'],
	);
	for ($year = $this->data['start']; $year <= $this->data['end']; ++ $year) {
		foreach ($seasons_found as $name => $season) {
			if ($season['season']) {
				$data[] = $person['Division'][$year][$name]['season']['captain'];
				$data[] = $person['Division'][$year][$name]['season']['player'];
			}
			if ($season['tournament']) {
				$data[] = $person['Division'][$year][$name]['tournament']['captain'];
				$data[] = $person['Division'][$year][$name]['tournament']['player'];
			}
		}
	}
	foreach (array_keys($event_names) as $event) {
		$data[] = array_key_exists($event, $person['Event']) ? 1 : '';
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
