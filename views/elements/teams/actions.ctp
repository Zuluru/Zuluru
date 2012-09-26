<?php
if (!isset($is_captain)) {
	$is_captain = false;
}
if (!isset($is_coordinator)) {
	$is_coordinator = false;
}

if ($team['track_attendance']) {
	if ($is_captain) {
		echo $this->ZuluruHtml->iconLink('team_event_add_24.png',
			array('controller' => 'team_events', 'action' => 'add', 'team' => $team['id']),
			array('alt' => __('Team Event', true), 'title' => __('Add a Team Event', true)));
	}

	if (in_array($team['id'], $this->Session->read('Zuluru.TeamIDs')))
	{
		echo $this->ZuluruHtml->iconLink('attendance_24.png',
			array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['id']),
			array('alt' => __('Attendance', true), 'title' => __('View Season Attendance Report', true)));
	}
}
if ($is_logged_in && $team['open_roster'] && !Division::rosterDeadlinePassed($division['Division']) &&
	!in_array($team['id'], $this->Session->read('Zuluru.TeamIDs')))
{
	echo $this->ZuluruHtml->iconLink('roster_add_24.png',
		array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['id']),
		array('alt' => __('Join Team', true), 'title' => __('Join Team', true)));
}
if ($is_admin || $is_captain) {
	echo $this->ZuluruHtml->iconLink('edit_24.png',
		array('controller' => 'teams', 'action' => 'edit', 'team' => $team['id'], 'return' => true),
		array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true)));
	echo $this->ZuluruHtml->iconLink('email_24.png',
		array('controller' => 'teams', 'action' => 'emails', 'team' => $team['id']),
		array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true)));
}
if ($is_admin || (($is_captain || $is_coordinator) && !Division::rosterDeadlinePassed($division['Division']))) {
	echo $this->ZuluruHtml->iconLink('roster_add_24.png',
		array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['id']),
		array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
}
if (($is_admin || $is_coordinator) && League::hasSpirit($division)) {
	echo $this->ZuluruHtml->iconLink('spirit_24.png',
		array('controller' => 'teams', 'action' => 'spirit', 'team' => $team['id']),
		array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true)));
}
if ($is_admin) {
	echo $this->ZuluruHtml->iconLink('move_24.png',
		array('controller' => 'teams', 'action' => 'move', 'team' => $team['id']),
		array('alt' => __('Move Team', true), 'title' => __('Move Team', true)));
	echo $this->ZuluruHtml->iconLink('delete_24.png',
		array('controller' => 'teams', 'action' => 'delete', 'team' => $team['id'], 'return' => true),
		array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
		array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['id'])));
}
?>
