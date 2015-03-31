<?php
$fp = fopen('php://output','w+');
$header = array(
		__('First Name', true),
		__('Last Name', true),
		__('Role', true),
);

$has_numbers = false;
$numbers = array_unique(Set::extract('/Person/TeamsPerson/number', $team));
if (Configure::read('feature.shirt_numbers') && count($numbers) > 1 && $numbers[0] !== null) {
	$has_numbers = true;
	array_unshift($header, __('Number', true));
}

$positions = Configure::read('sport.positions');
if (!empty($positions)) {
	$header[] = __('Position', true);
}

$header[] = __('Gender', true);
$header[] = __('Date Joined', true);

$fields = array(
	'first_name' => 'First Name',
	'last_name' => 'Last Name',
	'email' => 'Email Address',
	'alternate_email' => 'Alternate Email Address',
	'addr_street' => 'Address',
	'addr_city' => 'City',
	'addr_prov' => 'Province',
	'addr_postalcode' => 'Postal Code',
	'home_phone' => 'Home Phone',
	'work_phone' => 'Work Phone',
	'work_ext' => 'Work Ext',
	'mobile_phone' => 'Mobile Phone',
	'alternate_first_name' => 'Alternate First Name',
	'alternate_last_name' => 'Alternate Last Name',
	'alternate_work_phone' => 'Alternate Work Phone',
	'alternate_work_ext' => 'Alternate Work Ext',
	'alternate_mobile_phone' => 'Alternate Mobile Phone',
);
// Skip fields that are all blank or disabled
$player_fields = $fields;
unset($player_fields['first_name']);
unset($player_fields['last_name']);
foreach ($player_fields as $field => $name) {
	$short_field = str_replace('alternate_', '', $field);
	if (strpos($short_field, 'addr_') !== false && !$is_admin && !$is_manager) {
		$include = false;
	} else if ($short_field == 'email') {
		$include = true;
	} else if ($short_field == 'work_ext') {
		$include = Configure::read('profile.work_phone');
	} else {
		$include = Configure::read("profile.$short_field");
	}
	if ($include) {
		$values = array_unique(Set::extract("/Person/$field", $team));
		if (count($values) > 1 || !empty($values[0])) {
			$header[] = __($name, true);
		} else {
			unset($player_fields[$field]);
		}
	} else {
		// Disabled fields are disabled for players and relatives
		unset($fields[$field]);
		unset($player_fields[$field]);
	}
}

// Check if we need to include relative contact info
$relatives = 0;
foreach ($team['Person'] as $person) {
	if (empty($person['user_id']) || AppController::_isChild($person['birthdate'])) {
		$relatives = max($relatives, count($person['Related']));
	}
}
if ($relatives > 0) {
	$contact_fields = array_fill(0, $relatives, $fields);

	$header1 = array_fill(0, count($header), '');
	$include_header1 = false;
	for ($i = 0; $i < $relatives; ++ $i) {
		foreach ($contact_fields[$i] as $field => $name) {
			// TODO: Why doesn't something like Set::extract("/Related/$i/$field", $team['Person']) work here, when it does elsewhere?
			$values = array();
			foreach ($team['Person'] as $person) {
				if (!empty($person['Related'][$i][$field])) {
					$values[] = $person['Related'][$i][$field];
				}
			}
			$values = array_unique($values);
			if (count($values) > 1 || !empty($values[0])) {
				$header[] = __($name, true);
			} else {
				unset($contact_fields[$i][$field]);
			}
		}

		if (!empty($contact_fields[$i])) {
			$header1[] = sprintf(__('Contact %s', true), $i + 1);
			$header1 = array_merge($header1, array_fill(0, count($contact_fields[$i]) - 1, ''));
			$include_header1 = true;
		}
	}

	if ($include_header1) {
		fputcsv($fp, $header1);
	}
}

fputcsv($fp, $header);

foreach ($team['Person'] as $person) {
	$row = array(
		$person['first_name'],
		$person['last_name'],
		$person['TeamsPerson']['role'],
	);
	if ($has_numbers) {
		array_unshift($row, $person['TeamsPerson']['number']);
	}
	if (!empty($positions)) {
		$row[] = $person['TeamsPerson']['position'];
	}
	$row[] = $person['gender'];
	$row[] = $this->ZuluruTime->date($person['TeamsPerson']['created']);

	foreach ($player_fields as $field => $name) {
		if (array_key_exists($field, $person)) {
			$row[] = $person[$field];
		} else {
			$row[] = '';
		}
	}

	if ($relatives > 0 && (empty($person['user_id']) || AppController::_isChild($person['birthdate']))) {
		foreach ($person['Related'] as $i => $relative) {
			foreach (array_keys($contact_fields[$i]) as $field) {
				if (array_key_exists($field, $relative)) {
					$row[] = $relative[$field];
				} else {
					$row[] = '';
				}
			}
		}
	}

	// Output the data row
	fputcsv($fp, $row);
}

fclose($fp);
?>
