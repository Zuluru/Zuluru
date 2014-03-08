<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Created Date', true),
		__('Order ID', true),
		__('Event ID', true),
		__('Event', true),
		__('Price Point', true),
		__('User ID', true),
		__('First Name', true),
		__('Last Name', true),
		__('Payment Status', true),
		__('Total Amount', true),
		__('Amount Paid', true),
);
if (Configure::read('registration.online_payments')) {
	$header[] = __('Transaction ID', true);
}
$header[] = __('Notes', true);
if (count($affiliates) > 1 && empty($affiliate)) {
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
			$registration['Price']['name'],
			$registration['Person']['id'],
			$registration['Person']['first_name'],
			$registration['Person']['last_name'],
			$registration['Registration']['payment'],
			$registration['Registration']['total_amount'],
			array_sum(Set::extract('/Payment/payment_amount', $registration)),
	);
	if (Configure::read('registration.online_payments')) {
		$data[] = implode(';', array_unique(Set::extract('/Payment/RegistrationAudit/transaction_id', $registration)));
	}
	$data[] = $registration['Registration']['notes'];
	if (count($affiliates) > 1 && empty($affiliate)) {
		array_unshift($data, $registration['Event']['Affiliate']['name']);
	}

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
