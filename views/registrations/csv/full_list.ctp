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
		__('Skill Level', true),
		__('Shirt Size', true),
		__('Order ID', true),
		__('Created Date', true),
		__('Modified Date', true),
		__('Payment', true),
		__('Notes', true),
);
if (!$event['Event']['anonymous']) {
	foreach ($event['Questionnaire']['Question'] as $question) {
		if (in_array ($question['type'], array('text', 'textbox', 'radio', 'select', 'checkbox'))) {
			$header[] = $question['question'];
		}
	}
}
fputcsv($fp, $header);

$order_id_format = Configure::read('registration.order_id_format');

foreach($registrations as $registration) {
	$row = array(
		$registration['Person']['id'],
		$registration['Person']['first_name'],
		$registration['Person']['last_name'],
		$registration['Person']['email'],
		$registration['Person']['addr_street'],
		$registration['Person']['addr_city'],
		$registration['Person']['addr_prov'],
		$registration['Person']['addr_postalcode'],
		$registration['Person']['home_phone'],
		$registration['Person']['work_phone'],
		$registration['Person']['work_ext'],
		$registration['Person']['mobile_phone'],
		$registration['Person']['gender'],
		$registration['Person']['birthdate'],
		$registration['Person']['height'],
		$registration['Person']['skill_level'],
		$registration['Person']['shirt_size'],
		sprintf ($order_id_format, $registration['Registration']['id']),
		$registration['Registration']['created'],
		$registration['Registration']['modified'],
		$registration['Registration']['payment'],
		$registration['Registration']['notes'],
	);
	if (!$event['Event']['anonymous']) {
		foreach ($event['Questionnaire']['Question'] as $question) {
			if (in_array ($question['type'], array('text', 'textbox', 'radio', 'select', 'checkbox'))) {
				$answer = array_shift (Set::extract ("/Response[question_id={$question['id']}]/.", $registration));
				if (!empty ($answer['answer_id'])) {
					$answer = array_shift (Set::extract ("/Answer[id={$answer['answer_id']}]/.", $question));
				}
				$row[] = $answer['answer'];
			}
		}
	}
	fputcsv($fp, $row);
}

fclose($fp);

?>
