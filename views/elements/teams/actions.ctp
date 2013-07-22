<?php
if (!isset($is_captain)) {
	$is_captain = false;
}
if (!isset($is_manager)) {
	$is_manager = false;
}
if (!isset($is_coordinator)) {
	$is_coordinator = false;
}
if (!isset($format)) {
	$format = 'links';
}
if (!isset($size)) {
	$size = ($format == 'links' ? 24 : 32);
}

$links = array();

if ($this->params['controller'] != 'teams' || $this->params['action'] != 'view') {
	$links[] = $this->ZuluruHtml->iconLink("view_{$size}.png",
		array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']),
		array('alt' => __('View', true), 'title' => __('View', true)));
}

if ($team['division_id']) {
	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'schedule') {
		$links[] = $this->ZuluruHtml->iconLink("schedule_{$size}.png",
			array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['id']),
			array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'standings') {
		$links[] = $this->ZuluruHtml->iconLink("standings_{$size}.png",
			array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id'], 'team' => $team['id']),
			array('alt' => __('Standings', true), 'title' => __('Standings', true)));
	}
	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'stats') {
		if (($is_logged_in || Configure::read('feature.public')) && Configure::read('scoring.stat_tracking') && isset($league) && League::hasStats($league)) {
			$links[] = $this->ZuluruHtml->iconLink("summary_{$size}.png",
				array('controller' => 'teams', 'action' => 'stats', 'team' => $team['id']),
				array('alt' => __('Stats', true), 'title' => __('View Team Stats', true)));
		}
	}
}
if (Configure::read('feature.attendance') && $team['track_attendance']) {
	if ($is_captain) {
		$links[] = $this->ZuluruHtml->iconLink("team_event_add_{$size}.png",
			array('controller' => 'team_events', 'action' => 'add', 'team' => $team['id']),
			array('alt' => __('Team Event', true), 'title' => __('Add a Team Event', true)));
	}

	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'attendance') {
		if (in_array($team['id'], $this->Session->read('Zuluru.TeamIDs'))) {
			$links[] = $this->ZuluruHtml->iconLink("attendance_{$size}.png",
				array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['id']),
				array('alt' => __('Attendance', true), 'title' => __('View Season Attendance Report', true)));
		}
	}
}
if ($is_logged_in && $team['open_roster'] && !Division::rosterDeadlinePassed($division) &&
	!in_array($team['id'], $this->Session->read('Zuluru.TeamIDs')))
{
	$links[] = $this->ZuluruHtml->iconLink("roster_add_{$size}.png",
		array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['id']),
		array('alt' => __('Join Team', true), 'title' => __('Join Team', true)));
}
if ($is_admin || $is_manager || $is_captain) {
	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'edit') {
		$links[] = $this->ZuluruHtml->iconLink("edit_{$size}.png",
			array('controller' => 'teams', 'action' => 'edit', 'team' => $team['id'], 'return' => true),
			array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true)));
	}
	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'emails') {
		$links[] = $this->ZuluruHtml->iconLink("email_{$size}.png",
			array('controller' => 'teams', 'action' => 'emails', 'team' => $team['id']),
			array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true)));
	}
}
if ($is_admin || $is_manager || (($is_captain || $is_coordinator) && !Division::rosterDeadlinePassed($division))) {
	if ($this->params['controller'] != 'teams' || $this->params['action'] != 'add_player') {
		$links[] = $this->ZuluruHtml->iconLink("roster_add_{$size}.png",
			array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['id']),
			array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
	}
}
if (($is_admin || $is_manager || $is_coordinator) && isset($league) && League::hasSpirit($league)) {
	$links[] = $this->ZuluruHtml->iconLink("spirit_{$size}.png",
		array('controller' => 'teams', 'action' => 'spirit', 'team' => $team['id']),
		array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true)));
}
if ($is_admin || $is_manager) {
	$links[] = $this->ZuluruHtml->iconLink("move_{$size}.png",
		array('controller' => 'teams', 'action' => 'move', 'team' => $team['id']),
		array('alt' => __('Move Team', true), 'title' => __('Move Team', true)));
	$links[] = $this->ZuluruHtml->iconLink("delete_{$size}.png",
		array('controller' => 'teams', 'action' => 'delete', 'team' => $team['id'], 'return' => true),
		array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
		array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['id'])));
}
if ($is_logged_in && Configure::read('feature.annotations')) {
	if (!empty($team['Note'])) {
		$links[] = $this->Html->link(__('Delete Note', true), array('controller' => 'teams', 'action' => 'delete_note', 'team' => $team['id'], 'return' => true));
		$link = 'Edit Note';
	} else {
		$link = 'Add Note';
	}
	$links[] = $this->Html->link(__($link, true), array('controller' => 'teams', 'action' => 'note', 'team' => $team['id'], 'return' => true));
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$links = array_merge($links, $extra);
	} else {
		$links[] = $extra;
	}
}

if ($format == 'links') {
	echo implode('', $links);
} else {
	echo $this->ZuluruHtml->nestedList($links);
}

?>
