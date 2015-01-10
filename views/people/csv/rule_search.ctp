<?php
$fp = fopen('php://output','w+');
$header = array(
		__('User ID', true),
		__('First Name', true),
		__('Last Name', true),
		__('Email Address', true),
		__('Address', true),
		__('City', true),
		__('Province', true),
		__('Postal Code', true),
		__('Home Phone', true),
		__('Work Phone', true),
		__('Work Ext', true),
		__('Mobile Phone', true),
		__('Gender', true),
		__('Birthdate', true),
		__('Height', true),
		__('Shirt Size', true),
);
fputcsv($fp, $header);

foreach ($people as $person) {
	$data = array(
			$person['Person']['id'],
			$person['Person']['first_name'],
			$person['Person']['last_name'],
			$person['Person']['email'],
			$person['Person']['addr_street'],
			$person['Person']['addr_city'],
			$person['Person']['addr_prov'],
			$person['Person']['addr_postalcode'],
			$person['Person']['home_phone'],
			$person['Person']['work_phone'],
			$person['Person']['work_ext'],
			$person['Person']['mobile_phone'],
			$person['Person']['gender'],
			$person['Person']['birthdate'],
			$person['Person']['height'],
			$person['Person']['shirt_size'],
	);

	// Output the data row
	fputcsv($fp, $data);
}

fclose($fp);
?>
