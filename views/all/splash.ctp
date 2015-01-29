<?php
$this->Html->addCrumb (__('Home', true));
$this->Html->addCrumb ($this->UserCache->read('Person.full_name'));
?>

<div class="all splash">
<?php
echo $this->element('layout/announcement');

if ($is_admin) {
	echo $this->element('version_check');
	if (isset($new_accounts)) {
		echo $this->Html->para(null, sprintf(__('There are %d new %s.', true), $new_accounts,
			$this->Html->link(__('accounts to approve', true), array('controller' => 'people', 'action' => 'list_new'))));
	}
	if (isset($new_photos)) {
		echo $this->Html->para(null, sprintf(__('There are %d new %s.', true), $new_photos,
			$this->Html->link(__('profile photos to approve', true), array('controller' => 'people', 'action' => 'approve_photos'))));
	}
	if (isset($new_documents)) {
		echo $this->Html->para(null, sprintf(__('There are %d new %s.', true), $new_documents,
			$this->Html->link(__('uploaded documents to approve', true), array('controller' => 'people', 'action' => 'approve_documents'))));
	}
	if (isset($new_nominations)) {
		echo $this->Html->para(null, sprintf(__('There are %d new %s.', true), $new_nominations,
			$this->Html->link(__('badge nominations to approve', true), array('controller' => 'people', 'action' => 'approve_badges'))));
	}
}

$id = $this->UserCache->read('Person.id');
$statuses = Configure::read('attendance');

if (Configure::read('feature.affiliates')) {
	$affiliates = $this->requestAction(array('controller' => 'affiliates', 'action' => 'index'));
	AppModel::_reindexOuter($affiliates, 'Affiliate', 'id');
} else {
	$affiliates = array();
}

$relatives = $this->UserCache->read('Relatives');
$approved_relatives = Set::extract('/PeoplePerson[approved=1]/..', $relatives);

$unpaid = $this->UserCache->read('RegistrationsUnpaid');
$relative_unpaid = array();
foreach ($approved_relatives as $relative) {
	$relative_unpaid[$relative['Relative']['id']] = $this->UserCache->read('RegistrationsUnpaid', $relative['Relative']['id']);
}

// TODO
$count = count($unpaid); // + array_sum(array_map('count', $relative_unpaid));
if ($count) {
	echo $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
			$count,
			__n('registration', 'registrations', $count, true),
			$this->Html->link (__('Click here', true), array('controller' => 'registrations', 'action' => 'checkout'))
	));
}

$teams = $this->UserCache->read('Teams');
$team_ids = $this->UserCache->read('TeamIDs');
$past_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'past_count'), array('named' => array('person' => $id)));
$divisions = $this->UserCache->read('Divisions');
if (Configure::read('feature.tasks')) {
	$tasks = $this->UserCache->read('Tasks');
}
$events = array_merge (
		$this->requestAction(array('controller' => 'team_events', 'action' => 'past'), array('named' => array('person' => $id))),
		$this->requestAction(array('controller' => 'team_events', 'action' => 'future'), array('named' => array('person' => $id)))
);
AppModel::_reindexOuter($events, 'TeamEvent', 'id');
AppModel::_reindexInner($events, 'Attendance', 'person_id');

$tabs = array();
$people_with_schedules = 0;

$tab = $this->element('teams/splash', array('teams' => $teams, 'past_teams' => $past_teams, 'name' => __('My', true)));
$tab .= $this->element('all/kickstart', array('id' => $id, 'affiliates' => $affiliates, 'empty' => (empty($teams) && empty($divisions) && empty($tasks))));
$tab .= $this->element('divisions/splash', array('divisions' => $divisions));

$games = $items = array_merge (
		$this->requestAction(array('controller' => 'games', 'action' => 'past'), array('named' => array('person' => $id))),
		$this->requestAction(array('controller' => 'games', 'action' => 'future'), array('named' => array('person' => $id)))
);
$items = $games;
if (!empty($tasks)) {
	$items = array_merge($items, $tasks);
}
if (!empty($events)) {
	$items = array_merge($items, $events);
}
$tab .= $this->element('games/splash', compact('items', 'teams', 'team_ids'));
if (!empty($items)) {
	++ $people_with_schedules;
}
AppModel::_reindexOuter($games, 'Game', 'id');
AppModel::_reindexInner($games, 'Attendance', 'person_id');
$tab .= $this->element('people/ical_links', compact('id'));

if (!empty($tab)) {
	$tabs["tab-$id"] = array('name' => $this->UserCache->read('Person.full_name'), 'content' => $tab);
}

foreach ($approved_relatives as $relative) {
	$tab = '';

	$unpaid = $this->UserCache->read('RegistrationsUnpaid', $relative['Relative']['id']);
	$count = count($unpaid);
	if ($count) {
		$tab .= $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
				$count,
				__n('registration', 'registrations', $count, true),
				$this->Html->link (__('Click here', true), array('controller' => 'registrations', 'action' => 'checkout', 'act_as' => $relative['Relative']['id']))
		));
	}

	$relative_teams = $this->UserCache->read('Teams', $relative['Relative']['id']);
	$relative_team_ids = $this->UserCache->read('TeamIDs', $relative['Relative']['id']);
	$relative_past_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'past_count'), array('named' => array('person' => $relative['Relative']['id'])));
	$tab .= $this->element('teams/splash', array('teams' => $relative_teams, 'past_teams' => $relative_past_teams, 'name' => "{$relative['Relative']['first_name']}'s"));

	$relative_games = array_merge (
			$this->requestAction(array('controller' => 'games', 'action' => 'past'), array('named' => array('person' => $relative['Relative']['id']))),
			$this->requestAction(array('controller' => 'games', 'action' => 'future'), array('named' => array('person' => $relative['Relative']['id'])))
	);

	if (Configure::read('feature.tasks')) {
		$relative_tasks = $this->UserCache->read('Tasks', $relative['Relative']['id']);
		$tasks = array_merge($tasks, $relative_tasks);
	}

	$relative_events = array_merge (
			$this->requestAction(array('controller' => 'team_events', 'action' => 'past'), array('named' => array('person' => $relative['Relative']['id']))),
			$this->requestAction(array('controller' => 'team_events', 'action' => 'future'), array('named' => array('person' => $relative['Relative']['id'])))
	);

	$relative_items = $relative_games;
	if (!empty($relative_tasks)) {
		$relative_items = array_merge($relative_items, $relative_tasks);
	}
	if (!empty($relative_events)) {
		$relative_items = array_merge($relative_items, $relative_events);
	}
	$tab .= $this->element('games/splash', array('items' => $relative_items, 'teams' => $relative_teams, 'team_ids' => $relative_team_ids, 'person_id' => $relative['Relative']['id']));
	if (!empty($relative_items)) {
		++ $people_with_schedules;
	}

	// Add in this relative's details to the consolidated list
	foreach ($relative_games as $game) {
		if (array_key_exists($game['Game']['id'], $games)) {
			// Just merge the attendance records, if they exist (might not, if tracking is disabled)
			if (!empty($game['Attendance'])) {
				$games[$game['Game']['id']]['Attendance'][$game['Attendance'][0]['person_id']] = $game['Attendance'][0];
			}
		} else {
			AppModel::_reindexInner($game, 'Attendance', 'person_id');
			$games[$game['Game']['id']] = $game;
		}
	}

	foreach ($relative_events as $event) {
		if (array_key_exists($event['TeamEvent']['id'], $events)) {
			// Just merge the attendance records, if they exist (might not, if tracking is disabled)
			if (!empty($event['Attendance'])) {
				$events[$event['TeamEvent']['id']]['Attendance'][$event['Attendance'][0]['person_id']] = $event['Attendance'][0];
			}
		} else {
			AppModel::_reindexInner($event, 'Attendance', 'person_id');
			$events[$event['TeamEvent']['id']] = $event;
		}
	}

	$teams = array_merge($teams, $relative_teams);
	$team_ids = array_merge($team_ids, $relative_team_ids);

	$tab .= $this->element('all/kickstart', array(
			'id' => $relative['Relative']['id'],
			'is_admin' => false,
			'is_manager' => false,
			'is_player' => in_array(GROUP_PLAYER, $this->UserCache->read('GroupIDs', $relative['Relative']['id'])),
			'empty' => (empty($relative_teams) && empty($relative_tasks))
	));
	if (!empty($tab)) {
		$tabs["tab-{$relative['Relative']['id']}"] = array('name' => $relative['Relative']['full_name'], 'content' => $tab);
	}
}

if ($people_with_schedules > 1) {
	$items = $games;
	if (!empty($tasks)) {
		$items = array_merge($items, $tasks);
	}
	if (!empty($events)) {
		$items = array_merge($items, $events);
	}

	usort($items, array('Game', 'compareDateAndField'));
	$tabs['consolidated'] = array(
		'name' => 'Consolidated Schedule',
		'content' => $this->element('games/consolidated_schedule', compact('id', 'items', 'teams', 'team_ids', 'approved_relatives')),
	);
}

if (count($tabs) > 1):
?>
	<div id="tabs">
	<ul>
		<?php foreach ($tabs as $tab_id => $tab): ?>
		<li><a href="#<?php echo $tab_id; ?>"><?php echo $tab['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php foreach ($tabs as $tab_id => $tab): ?>
	<div id="<?php echo $tab_id; ?>">
		<?php echo $tab['content']; ?>
	</div>
	<?php endforeach; ?>
<?php
	$this->Js->buffer('jQuery("#tabs").tabs();');
else:
	$tab = reset($tabs);
	echo $this->Html->tag('h2', $tab['name']);
	echo $tab['content'];
endif;
?>

<?php if (Configure::read('feature.affiliates') && count($affiliates) > 1): ?>
<div id="affiliate_select">
<?php
	if ($this->Session->check('Zuluru.CurrentAffiliate')) {
		echo $this->Html->para(null, sprintf(__('You are currently browsing the %s affiliate. You might want to %s or %s.', true),
			$affiliates[$this->Session->read('Zuluru.CurrentAffiliate')]['Affiliate']['name'],
			$this->Html->link(__('remove this restriction', true), array('controller' => 'affiliates', 'action' => 'view_all')),
			$this->Html->link(__('select a different affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
	} else if (count($this->UserCache->read('AffiliateIDs')) != count($affiliates)) {
		if ($is_admin) {
			echo $this->Html->para(null, sprintf(__('This site has multiple affiliates. You might want to %s.', true),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		} else if (Configure::read('feature.multiple_affiliates')) {
			echo $this->Html->para(null, sprintf(__('This site has affiliates that you are not a member of. You might want to %s or %s.', true),
				$this->Html->link(__('join other affiliates', true), array('controller' => 'people', 'action' => 'edit')),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		} else {
			echo $this->Html->para(null, sprintf(__('This site has affiliates that you are not a member of. You might want to %s or %s.', true),
				$this->Html->link(__('change which affiliate you are a member of', true), array('controller' => 'people', 'action' => 'edit')),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		}
	} else {
		echo $this->Html->para(null, sprintf(__('You are a member of all affiliates on this site. You might want to %s or %s.', true),
			$this->Html->link(__('reduce your affiliations', true), array('controller' => 'people', 'action' => 'edit')),
			$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
	}
?>
</div>
<?php endif; ?>

</div>

<?php echo $this->element('games/attendance_div'); ?>
<?php if (!empty($teams)) echo $this->element('people/roster_div'); ?>
