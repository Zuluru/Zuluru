<?php
// To avoid abuses, whether intentional or accidental, we limit the permissions
// of admins when managing teams they are on.
$effective_admin = $effective_coordinator = false;
if ($is_admin) {
	$on_team = in_array($roster['team_id'], $this->UserCache->read('TeamIDs'));
	if (!$on_team) {
		$effective_admin = true;
	}
}
if (isset($is_coordinator) && $is_coordinator) {
	$on_team = in_array($roster['team_id'], $this->UserCache->read('TeamIDs'));
	if (!$on_team) {
		$effective_coordinator = true;
	}
}

$permission = ($effective_admin ||
	(!Division::rosterDeadlinePassed($division) && (
		(isset ($is_manager) && $is_manager) ||
		$effective_coordinator ||
		(isset ($my_id) && $roster['person_id'] == $my_id) ||
		in_array($roster['person_id'], $this->UserCache->read('RelativeIDs')) ||
		in_array($roster['team_id'], $this->UserCache->read('OwnedTeamIDs')))
	)
);

if ($permission) {
	$url = array(
		'controller' => 'teams',
		'action' => 'roster_position',
		'team' => $roster['team_id'],
		'person' => $roster['person_id'],
		'return' => true,
	);
	$url_string = Router::url($url);

	$options = Configure::read('sport.positions');
	$option_strings = array();
	foreach ($options as $key => $value) {
		$option_strings[] = "$key: '$value'";
	}
	$option_string = '{' . implode(', ', $option_strings) . '}';

	echo $this->Html->link(__(Configure::read("sport.positions.{$roster['position']}"), true) . $this->ZuluruHtml->icon('dropdown.png'),
		$url, array(
			'onClick' => "return roster_position('$url_string', $option_string, jQuery(this), '{$roster['position']}');",
			'escape' => false,
		)
	);
} else {
	__(Configure::read("sport.positions.{$roster['position']}"));
}
?>
