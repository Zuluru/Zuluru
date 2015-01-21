<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Team', true),
		__('User ID', true),
		__('Role', true),
);

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
	'gender' => 'Gender',
	'birthdate' => 'Birthdate',
	'height' => 'Height',
	'skill_level' => array('name' => 'Skill Level', 'model' => 'Skill'),
	'shirt_size' => 'Shirt Size',
	'alternate_first_name' => 'Alternate First Name',
	'alternate_last_name' => 'Alternate Last Name',
	'alternate_work_phone' => 'Alternate Work Phone',
	'alternate_work_ext' => 'Alternate Work Ext',
	'alternate_mobile_phone' => 'Alternate Mobile Phone',
);
// Skip fields that are all blank or disabled
$player_fields = $fields;
foreach ($player_fields as $field => $name) {
	$short_field = str_replace('alternate_', '', $field);
	if ($short_field == 'email') {
		$include = true;
	} else if ($short_field == 'work_ext') {
		$include = Configure::read('profile.work_phone');
	} else {
		$include = Configure::read("profile.$short_field");
	}
	if ($include) {
		if (is_array($name)) {
			$values = array_unique(Set::extract("/Division/Team/Person/{$name['model']}/$field", $league));
		} else {
			$values = array_unique(Set::extract("/Division/Team/Person/$field", $league));
		}
		if (count($values) > 1 || !empty($values[0])) {
			if (is_array($name)) {
				$name = $name['name'];
			}
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
$header[] = __('Added', true);

if (count($league['Division']) > 1) {
	array_unshift($header, __('Division', true));
}

// Check if we need to include relative contact info
$relatives = 0;
$contact_fields = $fields;
foreach (array('gender', 'birthdate', 'height', 'skill_level', 'shirt_size') as $field) {
	unset($contact_fields[$field]);
}
$contact_fields_required = array();
foreach ($league['Division'] as $division) {
	foreach ($division['Team'] as $team) {
		foreach ($team['Person'] as $person) {
			if (empty($person['user_id']) || AppController::_isChild($person['birthdate'])) {
				$relatives = max($relatives, count($person['Related']));
				foreach ($person['Related'] as $i => $relative) {
					foreach (array_keys($contact_fields) as $field) {
						if (!empty($relative[$field])) {
							$contact_fields_required[$i][$field] = true;
						}
					}
				}
			}
		}
	}
}
if ($relatives > 0) {
	$header1 = array_fill(0, count($header), '');
	for ($i = 0; $i < $relatives; ++ $i) {
		foreach ($contact_fields as $field => $name) {
			if (!empty($contact_fields_required[$i][$field])) {
				if (is_array($name)) {
					$name = $name['name'];
				}
				$header[] = __($name, true);
			}
		}

		$header1[] = sprintf(__('Contact %s', true), $i + 1);
		$header1 = array_merge($header1, array_fill(0, array_sum($contact_fields_required[$i]) - 1, ''));
	}

	fputcsv($fp, $header1);
}

fputcsv($fp, $header);

foreach ($league['Division'] as $division) {
	foreach ($division['Team'] as $team) {
		usort ($team['Person'], array('Team', 'compareRoster'));
		foreach ($team['Person'] as $person) {
			$role = __(Configure::read("options.roster_role.{$person['TeamsPerson']['role']}"), true);
			switch ($person['TeamsPerson']['status']) {
				case ROSTER_INVITED:
					$role .= ' (' . __('invited', true) . ')';
					break;

				case ROSTER_REQUESTED:
					$role .= ' (' . __('requested', true) . ')';
					break;
			}

			$row = array(
				$team['name'],
				$person['id'],
				$role,
			);
			foreach ($player_fields as $field => $name) {
				if (is_array($name)) {
					if (array_key_exists($field, $person[$name['model']])) {
						$row[] = $person[$name['model']][$field];
					} else if (array_key_exists($field, $person[$name['model']][0])) {
						$row[] = $person[$name['model']][0][$field];
					} else {
						$row[] = '';
					}
				} else {
					if (array_key_exists($field, $person)) {
						$row[] = $person[$field];
					} else {
						$row[] = '';
					}
				}
			}
			$row[] = $person['TeamsPerson']['created'];
			if (count($league['Division']) > 1) {
				array_unshift($row, $division['name']);
			}

			if ($relatives > 0 && (empty($person['user_id']) || AppController::_isChild($person['birthdate']))) {
				foreach ($person['Related'] as $i => $relative) {
					foreach (array_keys($contact_fields) as $field) {
						if (!empty($contact_fields_required[$i][$field])) {
							if (array_key_exists($field, $relative)) {
								$row[] = $relative[$field];
							} else {
								$row[] = '';
							}
						}
					}
				}
			}

			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
?>
