<?php
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
if (!isset($collapse)) {
	$collapse = false;
}
if (!isset($return)) {
	$return = false;
}
if (!isset($from_league_actions)) {
	$from_league_actions = false;
}
if ($from_league_actions) {
	$links = $league_actions;
} else {
	$links = array();
}

if (!$collapse) {
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'view') {
		$links[] = $this->ZuluruHtml->iconLink("view_$size.png",
			array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']),
			array('alt' => __('Details', true), 'title' => __('View Division Details', true)));
	}
}

if ($division['schedule_type'] != 'none') {
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'schedule') {
		$links[] = $this->ZuluruHtml->iconLink("schedule_$size.png",
			array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['id']),
			array('alt' => __('Schedule', true), 'title' => __('Schedule', true)));
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'standings') {
		$links[] = $this->ZuluruHtml->iconLink("standings_$size.png",
			array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id']),
			array('alt' => __('Standings', true), 'title' => __('Standings', true)));
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'stats') {
		if (($is_logged_in || Configure::read('feature.public')) && Configure::read('scoring.stat_tracking') && League::hasStats($league)) {
			$links[] = $this->ZuluruHtml->iconLink("stats_$size.png",
				array('controller' => 'divisions', 'action' => 'stats', 'division' => $division['id']),
				array('alt' => __('Stats', true), 'title' => __('Stats', true)));
		}
	}
}
if ($is_admin || $is_manager || $is_coordinator) {
	if (!$collapse && ($this->params['controller'] != 'divisions' || $this->params['action'] != 'edit')) {
		$links[] = $this->ZuluruHtml->iconLink("edit_$size.png",
			array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['id'], 'return' => true),
			array('alt' => __('Edit', true), 'title' => __('Edit Division', true)));
	}
	if (!empty($division['is_playoff']) && ($this->params['controller'] != 'divisions' || $this->params['action'] != 'initialize_ratings')) {
		$links[] = $this->ZuluruHtml->iconLink("initialize_$size.png",
			array('controller' => 'divisions', 'action' => 'initialize_ratings', 'division' => $division['id'], 'return' => $return),
			array('alt' => __('Initialize', true), 'title' => __('Initialize Ratings', true)));
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'emails') {
		$links[] = $this->ZuluruHtml->iconLink("email_$size.png",
			array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['id']),
			array('alt' => __('Captain Emails', true), 'title' => __('Captain Emails', true)));
	}
	if ($division['schedule_type'] != 'none') {
		if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'approve_scores') {
			$links[] = $this->ZuluruHtml->iconLink("score_approve_$size.png",
				array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['id'], 'return' => $return),
				array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
		}
		if ($this->params['controller'] != 'schedules' || $this->params['action'] != 'add') {
			$links[] = $this->ZuluruHtml->iconLink("schedule_add_$size.png",
				array('controller' => 'schedules', 'action' => 'add', 'division' => $division['id'], 'return' => $return),
				array('alt' => __('Add Games', true), 'title' => __('Add Games', true)));
		}
		if (League::hasSpirit($league) && ($this->params['controller'] != 'divisions' || $this->params['action'] != 'spirit')) {
			$links[] = $this->ZuluruHtml->iconLink("spirit_$size.png",
				array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id']),
				array('alt' => __('Spirit', true), 'title' => __('See Division Spirit Report', true)));
		}
		if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'fields') {
			$links[] = $this->ZuluruHtml->iconLink("field_report_$size.png",
				array('controller' => 'divisions', 'action' => 'fields', 'division' => $division['id']),
				array('alt' => sprintf(__('%s Distribution', true), Configure::read('sport.field_cap')), 'title' => sprintf(__('%s Distribution Report', true), Configure::read('sport.field_cap'))));
		}
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'add_teams') {
		$links[] = $this->ZuluruHtml->iconLink("team_add_$size.png",
			array('controller' => 'divisions', 'action' => 'add_teams', 'division' => $division['id'], 'return' => $return),
			array('alt' => __('Add Teams', true), 'title' => __('Add Teams', true)));
	}
	// TODO: More links to reports, etc.
}
if ($is_admin || $is_manager) {
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'add_coordinator') {
		$links[] = $this->ZuluruHtml->iconLink("coordinator_add_$size.png",
			array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['id'], 'return' => $return),
			array('alt' => __('Add Coordinator', true), 'title' => __('Add Coordinator', true)));
	}
	if ($this->params['controller'] != 'divisions' || $this->params['action'] != 'add') {
		$links[] = $this->ZuluruHtml->iconLink("division_clone_$size.png",
			array('controller' => 'divisions', 'action' => 'add', 'league' => $division['league_id'], 'division' => $division['id'], 'return' => $return),
			array('alt' => __('Clone Division', true), 'title' => __('Clone Division', true)));
	}
	if ($division['schedule_type'] != 'none') {
		if ($division['allstars'] != 'never' && ($this->params['controller'] != 'divisions' || $this->params['action'] != 'allstars')) {
			$links[] = $this->Html->link(__('Allstars', true), array('controller' => 'divisions', 'action' => 'allstars', 'division' => $division['id']));
		}
	}
	if (!$collapse && ($this->params['controller'] != 'divisions' || $this->params['action'] != 'delete')) {
		$links[] = $this->ZuluruHtml->iconLink("delete_$size.png",
			array('controller' => 'divisions', 'action' => 'delete', 'division' => $division['id'], 'return' => $return),
			array('alt' => __('Delete', true), 'title' => __('Delete Division', true)),
			array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $division['id'])));
	}
}

if (!empty($extra)) {
	if (is_array($extra)) {
		$links = array_merge($links, $extra);
	} else {
		$links[] = $extra;
	}
}

if ($collapse && !$from_league_actions) {
	echo $this->element('leagues/actions', array_merge(
		compact('league', 'is_manager', 'format', 'size', 'collapse', 'return'),
		array(
			'from_division_actions' => true,
			'extra' => $links,
		)
	));
} else if ($format == 'links') {
	echo implode('', $links);
} else {
	echo $this->ZuluruHtml->nestedList($links);
}
?>
