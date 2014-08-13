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

$id = $this->requestAction(array('controller' => 'users', 'action' => 'id'));
$statuses = Configure::read('attendance');

if (Configure::read('feature.affiliates')) {
	$affiliates = $this->requestAction(array('controller' => 'affiliates', 'action' => 'index'));
	AppModel::_reindexOuter($affiliates, 'Affiliate', 'id');
} else {
	$affiliates = array();
}

$relatives = $this->UserCache->read('Relatives');
$approved_relatives = Set::extract('/PeoplePerson[approved=1]/..', $relatives);
if (empty($approved_relatives)) {
	echo $this->Html->tag('h2', $this->UserCache->read('Person.full_name'));
}

$unpaid = $this->UserCache->read('RegistrationsUnpaid');
$relative_unpaid = array();
foreach ($approved_relatives as $relative) {
	$relative_unpaid[$relative['Relative']['id']] = $this->UserCache->read('RegistrationsUnpaid', $relative['Relative']['id']);
}

$count = count($unpaid) + array_sum(array_map('count', $relative_unpaid));
if ($count) {
	echo $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
			$count,
			__n('registration', 'registration', $count, true),
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

if (!empty($approved_relatives)):
	// Set up the tab structure for everyone
?>
	<div id="tabs">
		<ul>
			<li><a href="#tab-<?php echo $this->UserCache->read('Person.id'); ?>"><?php echo $this->UserCache->read('Person.full_name'); ?></a></li>
			<?php foreach ($approved_relatives as $relative): ?>
			<li><a href="#tab-<?php echo $relative['Relative']['id']; ?>"><?php echo $relative['Relative']['full_name']; ?></a></li>
			<?php endforeach; ?>
			<li><a href="#consolidated"><?php __('Consolidated Schedule'); ?></a></li>
		</ul>
		<div id="tab-<?php echo $this->UserCache->read('Person.id'); ?>">
<?php
endif;

// This is all of the content for the current user, regardless of whether it's in a tab or not
echo $this->element('teams/splash', array('teams' => $teams, 'past_teams' => $past_teams, 'name' => __('My', true)));
echo $this->element('all/kickstart', array('affiliates' => $affiliates, 'empty' => (empty($teams) && empty($divisions) && empty($unpaid) && empty($tasks))));
?>

<?php if (!empty ($divisions)) : ?>
<table class="list">
<tr>
	<th colspan="2"><?php __('Divisions Coordinated');?></th>
</tr>
<?php
$i = 0;
$coordinated_divisions = $this->UserCache->read('DivisionIDs');
foreach ($divisions as $division):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}

	$is_coordinator = in_array($division['Division']['id'], $coordinated_divisions);
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $this->element('divisions/block', array('division' => $division['Division'], 'field' => 'long_league_name')); ?></td>
		<td class="actions splash_action"><?php echo $this->element('divisions/actions', array('league' => $division['League'], 'division' => $division['Division'], 'is_coordinator' => $is_coordinator)); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php
endif;

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
echo $this->element('games/splash', compact('items', 'teams', 'team_ids'));
AppModel::_reindexOuter($games, 'Game', 'id');
AppModel::_reindexInner($games, 'Attendance', 'person_id');
?>

<p><?php
if (Configure::read('personal.enable_ical')) {
	__('Get your personal schedule in ');
	// TODOIMG: Better image locations, alt text
	echo $this->ZuluruHtml->iconLink ('ical.gif',
		array('controller' => 'people', 'action' => 'ical', $id, 'player.ics'),
		array('alt' => 'iCal'));
	__(' format or ');
	echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif',
		'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('controller' => 'people', 'action' => 'ical', $id), true),
		array('alt' => 'add to Google Calendar'),
		array('target' => 'google'));
} else {
	echo $this->Html->link (__('Edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'));
	__(' to enable your personal iCal feed');
}
?>. <?php echo $this->ZuluruHtml->help(array('action' => 'games', 'personal_feed')); ?></p>

<?php
// If there are relatives, we now close out the current user's details and add the other tabs
if (!empty($relatives)):
?>
		</div>
		<?php foreach ($approved_relatives as $relative): ?>
		<div id="tab-<?php echo $relative['Relative']['id']; ?>">
			<?php
			$relative_teams = $this->UserCache->read('Teams', $relative['Relative']['id']);
			$relative_team_ids = $this->UserCache->read('TeamIDs', $relative['Relative']['id']);
			$relative_past_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'past_count'), array('named' => array('person' => $relative['Relative']['id'])));
			echo $this->element('teams/splash', array('teams' => $relative_teams, 'past_teams' => $relative_past_teams, 'name' => "{$relative['Relative']['first_name']}'s"));

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
			echo $this->element('games/splash', array('items' => $relative_items, 'teams' => $relative_teams, 'team_ids' => $relative_team_ids, 'person_id' => $relative['Relative']['id']));

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

			echo $this->element('all/kickstart', array('is_admin' => false, 'is_manager' => false, 'empty' => (empty($relative_teams) && empty($relative_unpaid[$relative['Relative']['id']]) && empty($relative_tasks))));
			?>
		</div>
		<?php endforeach; ?>
		<div id="consolidated">
		<?php
		$items = $games;
		if (!empty($tasks)) {
			$items = array_merge($items, $tasks);
		}
		if (!empty($events)) {
			$items = array_merge($items, $events);
		}

		if (!empty($items)):
			usort($items, array('Game', 'compareDateAndField'));
		?>
			<table class="list">
				<tr>
					<th></th>
					<th></th>
					<th><?php echo $this->UserCache->read('Person.full_name'); ?></th>
					<?php foreach ($approved_relatives as $relative): ?>
					<th><?php echo $relative['Relative']['full_name']; ?></th>
					<?php endforeach; ?>
					<th></th>
				</tr>
			<?php
			$i = 0;
			foreach ($items as $item):
				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}
			?>
				<tr<?php echo $class;?>>
				<?php if (array_key_exists('Game', $item)): ?>
					<td class="splash_item"><?php
						$time = $this->ZuluruTime->day($item['GameSlot']['game_date']) . ', ' .
								$this->ZuluruTime->time($item['GameSlot']['game_start']) . '-' .
								$this->ZuluruTime->time($item['GameSlot']['display_game_end']);
						echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $item['Game']['id']));
					?></td>
					<td class="splash_item"><?php
						Game::_readDependencies($item);
						Configure::load("sport/{$item['Division']['League']['sport']}");
						if ($item['Game']['home_team'] === null) {
							echo $item['Game']['home_dependency'];
						} else {
							echo $this->element('teams/block', array('team' => $item['HomeTeam'], 'options' => array('max_length' => 16)));
							if ($item['Division']['schedule_type'] != 'competition') {
								echo ' (' . __('home', true) . ')';
							}
							if (in_array($item['HomeTeam']['id'], $team_ids) && $item['HomeTeam']['track_attendance'] && $item['GameSlot']['game_date'] >= date('Y-m-d')) {
								echo $this->ZuluruHtml->iconLink('attendance_24.png',
									array('controller' => 'games', 'action' => 'attendance', 'team' => $item['HomeTeam']['id'], 'game' => $item['Game']['id']),
									array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));
							}
						}
						if ($item['Division']['schedule_type'] != 'competition') {
							__(' vs. ');
							if ($item['Game']['away_team'] === null) {
								echo $item['Game']['away_dependency'];
							} else {
								echo $this->element('teams/block', array('team' => $item['AwayTeam'], 'options' => array('max_length' => 16))) .
									' (' . __('away', true) . ')';
								if (in_array($item['AwayTeam']['id'], $team_ids) && $item['AwayTeam']['track_attendance'] && $item['GameSlot']['game_date'] >= date('Y-m-d')) {
									echo $this->ZuluruHtml->iconLink('attendance_24.png',
										array('controller' => 'games', 'action' => 'attendance', 'team' => $item['AwayTeam']['id'], 'game' => $item['Game']['id']),
										array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));
								}
							}
						}
						__(' at ');
						echo $this->element('fields/block', array('field' => $item['GameSlot']['Field']));
					?></td>
					<td class="actions splash_item"><?php
					if (in_array($item['HomeTeam']['id'], $team_ids) && $item['HomeTeam']['track_attendance']) {
						$roster = Set::extract("/TeamsPerson[person_id=$id][team_id={$item['HomeTeam']['id']}]/.", $teams);
						if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
							echo $this->element('games/attendance_change', array(
								'team' => $item['HomeTeam'],
								'game_id' => $item['Game']['id'],
								'game_date' => $item['GameSlot']['game_date'],
								'game_time' => $item['GameSlot']['game_start'],
								'role' => $roster[0]['role'],
								'status' => (array_key_exists ($id, $item['Attendance']) ? $item['Attendance'][$id]['status'] : ATTENDANCE_UNKNOWN),
								'comment' => (array_key_exists ($id, $item['Attendance']) ? $item['Attendance'][$id]['comment'] : null),
								'future_only' => false,
								'dedicated' => true,
							));
						}
					} else if (in_array($item['AwayTeam']['id'], $team_ids) && $item['AwayTeam']['track_attendance']) {
						$roster = Set::extract("/TeamsPerson[person_id=$id][team_id={$item['AwayTeam']['id']}]/.", $teams);
						if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
							echo $this->element('games/attendance_change', array(
								'team' => $item['AwayTeam'],
								'game_id' => $item['Game']['id'],
								'game_date' => $item['GameSlot']['game_date'],
								'game_time' => $item['GameSlot']['game_start'],
								'role' => $roster[0]['role'],
								'status' => (array_key_exists ($id, $item['Attendance']) ? $item['Attendance'][$id]['status'] : ATTENDANCE_UNKNOWN),
								'comment' => (array_key_exists ($id, $item['Attendance']) ? $item['Attendance'][$id]['comment'] : null),
								'future_only' => false,
								'dedicated' => true,
							));
						}
					}
					?></td>
					<?php foreach ($approved_relatives as $relative): ?>
					<td class="actions splash_item"><?php
					if (in_array($item['HomeTeam']['id'], $team_ids) && $item['HomeTeam']['track_attendance']) {
						$roster = Set::extract("/TeamsPerson[person_id={$relative['Relative']['id']}][team_id={$item['HomeTeam']['id']}]/.", $teams);
						if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
							echo $this->element('games/attendance_change', array(
								'team' => $item['HomeTeam'],
								'game_id' => $item['Game']['id'],
								'game_date' => $item['GameSlot']['game_date'],
								'game_time' => $item['GameSlot']['game_start'],
								'person_id' => $relative['Relative']['id'],
								'role' => $roster[0]['role'],
								'status' => (array_key_exists ($relative['Relative']['id'], $item['Attendance']) ? $item['Attendance'][$relative['Relative']['id']]['status'] : ATTENDANCE_UNKNOWN),
								'comment' => (array_key_exists ($relative['Relative']['id'], $item['Attendance']) ? $item['Attendance'][$relative['Relative']['id']]['comment'] : null),
								'is_captain' => in_array($item['HomeTeam']['id'], $this->UserCache->read('OwnedTeamIDs')),
								'future_only' => false,
								'dedicated' => true,
							));
						}
					} else if (in_array($item['AwayTeam']['id'], $team_ids) && $item['AwayTeam']['track_attendance']) {
						$roster = Set::extract("/TeamsPerson[person_id={$relative['Relative']['id']}][team_id={$item['AwayTeam']['id']}]/.", $teams);
						if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
							echo $this->element('games/attendance_change', array(
								'team' => $item['AwayTeam'],
								'game_id' => $item['Game']['id'],
								'game_date' => $item['GameSlot']['game_date'],
								'game_time' => $item['GameSlot']['game_start'],
								'person_id' => $relative['Relative']['id'],
								'role' => $roster[0]['role'],
								'status' => (array_key_exists ($relative['Relative']['id'], $item['Attendance']) ? $item['Attendance'][$relative['Relative']['id']]['status'] : ATTENDANCE_UNKNOWN),
								'comment' => (array_key_exists ($relative['Relative']['id'], $item['Attendance']) ? $item['Attendance'][$relative['Relative']['id']]['comment'] : null),
								'is_captain' => in_array($item['AwayTeam']['id'], $this->UserCache->read('OwnedTeamIDs')),
								'future_only' => true,
								'dedicated' => true,
							));
						}
					}
					?></td>
					<?php endforeach; ?>
					<td><?php echo $this->ZuluruGame->displayScore ($item, $item['Division'], $item['Division']['League']); ?></td>
				<?php elseif (!empty($item['TeamEvent'])): ?>
					<td class="splash_item"><?php
						$time = $this->ZuluruTime->day($item['TeamEvent']['date']) . ', ' .
								$this->ZuluruTime->time($item['TeamEvent']['start']) . '-' .
								$this->ZuluruTime->time($item['TeamEvent']['end']);
						echo $this->Html->link($time, array('controller' => 'team_events', 'action' => 'view', 'event' => $item['TeamEvent']['id']));
					?></td>
					<td class="splash_item"><?php
						echo $this->element('teams/block', array('team' => $item['Team'], 'show_shirt' => false)) . ' ' .
								__('event', true) . ': ';
						if (!empty($item['TeamEvent']['website'])) {
							echo $this->Html->link($item['TeamEvent']['name'], $item['TeamEvent']['website']);
						} else {
							echo $item['TeamEvent']['name'];
						}
						echo ' ' . __('at', true) . ' ';
						$address = "{$item['TeamEvent']['location_street']}, {$item['TeamEvent']['location_city']}, {$item['TeamEvent']['location_province']}";
						$link_address = strtr ($address, ' ', '+');
						echo $this->Html->link($item['TeamEvent']['location_name'], "http://maps.google.com/maps?q=$link_address");
					?></td>
					<td class="actions splash_item"><?php
						if ($item['Team']['track_attendance'] && array_key_exists ($id, $item['Attendance'])) {
							$roster = Set::extract("/TeamsPerson[person_id=$id][team_id={$item['Team']['id']}]/.", $teams);
							if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
								echo $this->element('team_events/attendance_change', array(
									'team' => $item['Team'],
									'event_id' => $item['TeamEvent']['id'],
									'date' => $item['TeamEvent']['date'],
									'time' => $item['TeamEvent']['start'],
									'person_id' => $id,
									'role' => $roster[0]['role'],
									'status' => $item['Attendance'][$id]['status'],
									'comment' => $item['Attendance'][$id]['comment'],
									'is_captain' => in_array($item['Team']['id'], $this->UserCache->read('OwnedTeamIDs')),
									'dedicated' => true,
								));
							}
						}
					?></td>
					<?php foreach ($approved_relatives as $relative): ?>
					<td class="actions splash_item"><?php
						if ($item['Team']['track_attendance'] && array_key_exists ($relative['Relative']['id'], $item['Attendance'])) {
							$roster = Set::extract("/TeamsPerson[person_id={$relative['Relative']['id']}][team_id={$item['Team']['id']}]/.", $teams);
							if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
								echo $this->element('team_events/attendance_change', array(
									'team' => $item['Team'],
									'event_id' => $item['TeamEvent']['id'],
									'date' => $item['TeamEvent']['date'],
									'time' => $item['TeamEvent']['start'],
									'person_id' => $relative['Relative']['id'],
									'role' => $roster[0]['role'],
									'status' => $item['Attendance'][$relative['Relative']['id']]['status'],
									'comment' => $item['Attendance'][$relative['Relative']['id']]['comment'],
									'is_captain' => in_array($item['Team']['id'], $this->UserCache->read('OwnedTeamIDs')),
									'dedicated' => true,
								));
							}
						}
					?></td>
					<?php endforeach; ?>
					<td></td>
				<?php elseif (!empty($item['TaskSlot'])): ?>
					<td class="splash_item"><?php
						$time = $this->ZuluruTime->day($item['TaskSlot']['task_date']) . ', ' .
								$this->ZuluruTime->time($item['TaskSlot']['task_start']) . '-' .
								$this->ZuluruTime->time($item['TaskSlot']['task_end']);
						echo $this->Html->link($time, array('controller' => 'tasks', 'action' => 'view', 'task' => $item['Task']['id']));
					?></td>
					<td class="splash_item"><?php
						echo $this->Html->link($item['Task']['name'], array('controller' => 'tasks', 'action' => 'view', 'task' => $item['Task']['id'])) .
								' (' . __('report to', true) . ' ' . $this->element('people/block', array('person' => $item['Task']['Person'])) . ')';
					?></td>
					<td class="splash_item"><?php
					if ($item['TaskSlot']['person_id'] == $id) {
						echo $this->ZuluruHtml->icon('attendance_attending_dedicated_24.png');
					}
					?></td>
					<?php foreach ($approved_relatives as $relative): ?>
					<td class="splash_item"><?php
					if ($item['TaskSlot']['person_id'] == $relative['Relative']['id']) {
						echo $this->ZuluruHtml->icon('attendance_attending_dedicated_24.png');
					}
					?></td>
					<?php endforeach; ?>
					<td class="actions"><?php
					echo $this->Html->link(
							__('iCal', true),
							array('controller' => 'task_slots', 'action' => 'ical', $item['TaskSlot']['id'], 'task.ics'));
					?></td>
				<?php else: pr($item); ?>
				<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</table>
		<?php endif; ?>
		</div>
	</div>
<?php
	$this->Js->buffer('jQuery("#tabs").tabs();');
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
