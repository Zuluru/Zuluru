<?php
$fp = fopen('php://output','w+');

// The list of events across the top should not include the first one, as it's an empty column
$short_event_list = $event_list;
array_shift($short_event_list);

$header = array(
		__('Event ID', true),
		__('Membership Registration', true),
);
foreach ($short_event_list as $event) {
	$header[] = $event['Event']['name'];
}
fputcsv($fp, $header);

foreach ($past_events as $past_id => $counts) {
	$event = array_shift(Set::extract("/Event[id=$past_id]", $event_list));

	$data = array(
			$event['Event']['id'],
			$event['Event']['name'],
	);
	foreach ($short_event_list as $event) {
		if (array_key_exists($event['Event']['id'], $counts)) {
			$data[] = $counts[$event['Event']['id']];
		} else {
			$data[] = '';
		}
	}

	// Output the data row
	fputcsv($fp, $data);
}

$data = array(
	'',
	__('Total Prior', true),
);
foreach ($short_event_list as $event) {
	$data[] = $event['total'];
}
fputcsv($fp, $data);

$data = array(
	'',
	__('Total Registered', true),
);
foreach ($short_event_list as $event) {
	$data[] = $event['count'];
}
fputcsv($fp, $data);

$data = array(
	'',
	__('% Prior', true),
);
foreach ($short_event_list as $event) {
	$data[] = sprintf('%2.1f', $event['total'] * 100 / $event['count']);
}
fputcsv($fp, $data);

fclose($fp);
?>
