<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Created Date', true),
		__('Order ID', true),
		__('Event ID', true),
		__('Event', true),
		__('User ID', true),
		__('First Name', true),
		__('Last Name', true),
		__('Payment', true),
		__('Amount', true),
);
if (count($affiliates) && empty($affiliate)) {
	array_unshift($header, __('Affiliate', true));
}
fputcsv($fp, $header);

$order_fmt = Configure::read('registration.order_id_format');

foreach ($registrations as $registration) {
	$order_id = sprintf($order_fmt, $registration['Registration']['id']);

	$data = array(
			$registration['Registration']['created'],
			$order_id,
			$registration['Event']['id'],
			$registration['Event']['name'],
			$registration['Person']['id'],
			$registration['Person']['first_name'],
			$registration['Person']['last_name'],
			$registration['Registration']['payment'],
			$registration['Event']['cost'] + $registration['Event']['tax1'] + $registration['Event']['tax2'],
	);
	if (count($affiliates) && empty($affiliate)) {
		array_unshift($data, $registration['Event']['Affiliate']['name']);
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
