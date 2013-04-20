<?php
// To avoid abuses, whether intentional or accidental, we limit the permissions
// of admins when managing teams they are on.
$effective_admin = false;
if ($is_admin) {
	$on_team = $this->Session->check('Zuluru.TeamIDs') && in_array ($roster['team_id'], $this->Session->read('Zuluru.TeamIDs'));
	if (!$on_team) {
		$effective_admin = true;
	}
}

$permission = ($effective_admin ||
	(!Division::rosterDeadlinePassed($division) && (
		(isset ($is_coordinator) && $is_coordinator) ||
		(isset ($my_id) && $roster['person_id'] == $my_id) ||
		in_array ($roster['team_id'], $this->Session->read('Zuluru.OwnedTeamIDs')))
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

	echo $this->Html->link(__(Configure::read("sport.positions.{$roster['position']}"), true), $url, array(
		'onClick' => "return roster_position('$url_string', $option_string, jQuery(this), '{$roster['position']}');",
	));
} else {
	__(Configure::read("sport.positions.{$roster['position']}"));
}
?>
