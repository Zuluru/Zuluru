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
		(in_array ($roster['team_id'], $this->Session->read('Zuluru.OwnedTeamIDs')))
	)
));

$approved = ($roster['status'] == ROSTER_APPROVED);

if ($permission && $approved) {
	$url = array(
		'controller' => 'teams',
		'action' => 'roster_role',
		'team' => $roster['team_id'],
		'person' => $roster['person_id'],
		'return' => true,
	);
	$url_string = Router::url($url);

	$options = Configure::read('options.roster_role');
	$option_strings = array();
	foreach ($options as $key => $value) {
		$option_strings[] = "$key: '$value'";
	}
	$option_string = '{' . implode(', ', $option_strings) . '}';

	echo $this->Html->link(__(Configure::read("options.roster_role.{$roster['role']}"), true), $url, array(
		'onClick' => "return roster_role('$url_string', $option_string, jQuery(this), '{$roster['role']}');",
	));
} else {
	__(Configure::read("options.roster_role.{$roster['role']}"));
}

if (!$approved) {
	echo ' [';
	switch ($roster['status']) {
		case ROSTER_INVITED:
			__('invited');
			if ($permission) {
				if (isset ($is_captain) && $is_captain) {
					// Captains can only remove invitations that they sent
					$remove = true;
				}
			}
			$type = __('invitation', true);
			break;

		case ROSTER_REQUESTED:
			__('requested');
			if ($permission) {
				if (isset ($my_id) && $roster['person_id'] == $my_id) {
					// Players can only remove requests that they sent
					$remove = true;
				}
			}
			$type = __('request', true);
			break;
	}

	if (isset($remove)) {
		echo ': ' . $this->Html->link (__('remove', true), array(
			'controller' => 'teams',
			'action' => 'roster_decline',
			'team' => $roster['team_id'],
			'person' => $roster['person_id'],
		), null, sprintf(__('Are you sure you want to %s this %s?', true), __('remove', true), $type));
	} else if ($permission) {
		echo ': ' . $this->Html->link (__('accept', true), array(
			'controller' => 'teams',
			'action' => 'roster_accept',
			'team' => $roster['team_id'],
			'person' => $roster['person_id'],
		), null, sprintf(__('Are you sure you want to %s this %s?', true), __('accept', true), $type)) .
		' or ' .
		$this->Html->link (__('decline', true), array(
			'controller' => 'teams',
			'action' => 'roster_decline',
			'team' => $roster['team_id'],
			'person' => $roster['person_id'],
		), null, sprintf(__('Are you sure you want to %s this %s?', true), __('decline', true), $type));
	}

	echo ']';
}
?>
