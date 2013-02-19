<?php
$fp = fopen('php://output','w+');
$header = array(
		__('Division', true),
		__('Team', true),
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
		__('Role', true),
		__('Added', true),
);
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
				$division['name'],
				$team['name'],
				$person['id'],
				$person['first_name'],
				$person['last_name'],
				$person['email'],
				$person['addr_street'],
				$person['addr_city'],
				$person['addr_prov'],
				$person['addr_postalcode'],
				$person['home_phone'],
				$person['work_phone'],
				$person['work_ext'],
				$person['mobile_phone'],
				$person['gender'],
				$person['birthdate'],
				$person['height'],
				$person['skill_level'],
				$person['shirt_size'],
				$role,
				$person['TeamsPerson']['created'],
			);
			fputcsv($fp, $row);
		}
	}
}

fclose($fp);
?>
