<?php
$this->Html->addCrumb (__('Home', true));
$this->Html->addCrumb ($name);
?>

<div class="all splash">
<?php echo $this->Html->tag('h2', $name); ?>

<?php
if ($is_admin) {
	echo $this->element('version_check');
}

if (!$is_admin && empty($unpaid) && empty($teams) && empty($divisions)):
?>
<div id="kick_start">
<h3><?php __('You are not yet on any teams.'); ?></h3>
<?php
	$actions = array();

	if (Configure::read('feature.registration')) {
		$options = array();
		if ($membership_events) {
			$options[] = 'membership';
		}
		if ($non_membership_events) {
			$options[] = 'an event';
		}
		if (!empty($options)) {
			$actions[] = $this->Html->link ('Register for ' . implode(' or ', $options), array('controller' => 'events', 'action' => 'wizard'));
		}
	}

	if ($open_teams) {
		$actions[] = $this->Html->link ('Join an existing team', array('controller' => 'teams', 'action' => 'join'));
	}

	if ($open_leagues) {
		$actions[] = $this->Html->link ('Check out the leagues we are currently offering', array('controller' => 'leagues'));
	}

	if (!empty($actions)) {
		echo $this->Html->tag('div', $this->Html->nestedList($actions), array('class' => 'actions'));
	}
?>
</div>

<?php
endif;

if (!empty($unpaid)) {
	echo $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
			count($unpaid),
			__(count($unpaid) > 1 ? 'registrations' : 'registration', true),
			$this->Html->link (__('Click here', true), array('controller' => 'registrations', 'action' => 'checkout'))
	));
}
?>

<?php if (!empty($teams) || $past_teams > 0): ?>
<table class="list">
<tr>
	<th colspan="2"><?php
	__('My Teams');
	echo $this->ZuluruHtml->help(array('action' => 'teams', 'my_teams'));
	?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
		echo $this->element('teams/block', array('team' => $team['Team'])) .
				' (' . $this->Html->link ($team['Division']['league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Division']['id'])) . ')' .
				' (' . $this->element('people/roster', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) . ')';
		?></td>
		<td class="actions splash_action">
			<?php
			$is_captain = in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
			if (!Division::rosterDeadlinePassed($team['Division']) && $is_captain) {
				echo $this->ZuluruHtml->iconLink('roster_add_24.png',
					array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']),
					array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
			}
			if ($team['Team']['track_attendance']) {
				if ($is_captain) {
					echo $this->ZuluruHtml->iconLink('team_event_add_24.png',
						array('controller' => 'team_events', 'action' => 'add', 'team' => $team['Team']['id']),
						array('alt' => __('Team Event', true), 'title' => __('Add a Team Event', true)));
				}
				echo $this->ZuluruHtml->iconLink('attendance_24.png',
					array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['Team']['id']),
					array('alt' => __('Attendance', true), 'title' => __('View Season Attendance Report', true)));
			}
			echo $this->ZuluruHtml->iconLink('schedule_24.png',
				array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id']),
				array('alt' => __('Schedule', true), 'title' => __('View Team Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_24.png',
				array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Division']['id'], 'team' => $team['Team']['id']),
				array('alt' => __('Standings', true), 'title' => __('View Team Standings', true)));
			if ($is_admin || $is_captain) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('controller' => 'teams', 'action' => 'edit', 'team' => $team['Team']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit Team', true)));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php if ($past_teams > 0): ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams')); ?> </li>
	</ul>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty ($divisions)) : ?>
<table class="list">
<tr>
	<th colspan="2"><?php __('Divisions Coordinated');?></th>
</tr>
<?php
$i = 0;
foreach ($divisions as $division):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			echo $this->ZuluruHtml->link($division['Division']['long_league_name'],
					array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']));
		?></td>
		<td class="actions splash_action">
			<?php
			if ($division['Division']['schedule_type'] != 'none') {
				echo $this->ZuluruHtml->iconLink('schedule_24.png',
					array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['Division']['id']),
					array('alt' => __('Schedule', true), 'title' => __('View Division Schedule', true)));
				echo $this->ZuluruHtml->iconLink('standings_24.png',
					array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['Division']['id']),
					array('alt' => __('Standings', true), 'title' => __('View Division Standings', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_24.png',
					array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['Division']['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_24.png',
					array('controller' => 'schedules', 'action' => 'add', 'division' => $division['Division']['id']),
					array('alt' => __('Add games', true), 'title' => __('Add games', true)));
			}
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['Division']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Division', true)));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (!empty($games)): ?>
<table class="list">
<tr>
	<th colspan="3"><?php
	__('Recent and Upcoming Games');
	echo $this->ZuluruHtml->help(array('action' => 'games', 'recent_and_upcoming'));
	?></th>
</tr>
<?php
$i = 0;
foreach ($games as $game):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			$time = $this->ZuluruTime->day($game['GameSlot']['game_date']) . ', ' .
					$this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
					$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
			echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
		?></td>
		<td class="splash_item"><?php
			Game::_readDependencies($game['Game']);
			if ($game['Game']['home_team'] === null) {
				echo $game['Game']['home_dependency'];
			} else {
				echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16))) .
					' (' . __('home', true) . ')';
			}
			__(' vs. ');
			if ($game['Game']['away_team'] === null) {
				echo $game['Game']['away_dependency'];
			} else {
				echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16))) .
					' (' . __('away', true) . ')';
			}
			__(' at ');
			echo $this->element('fields/block', array('field' => $game['GameSlot']['Field']));
		?></td>
		<td class="actions splash_action"><?php
		if (in_array ($game['HomeTeam']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
			$team = $game['HomeTeam'];
		} else {
			$team = $game['AwayTeam'];
		}
		if ($team['track_attendance']) {
			$position = Set::extract("/TeamsPerson[team_id={$team['id']}]/position", $teams);
			echo $this->element('games/attendance_change', array(
				'team' => $team,
				'game_id' => $game['Game']['id'],
				'game_date' => $game['GameSlot']['game_date'],
				'game_time' => $game['GameSlot']['game_start'],
				'position' => $position[0],
				'status' => (array_key_exists (0, $game['Attendance']) ? $game['Attendance'][0]['status'] : ATTENDANCE_UNKNOWN),
				'comment' => (array_key_exists (0, $game['Attendance']) ? $game['Attendance'][0]['comment'] : null),
				'future_only' => true,
			));
			if ($game['GameSlot']['game_date'] >= date('Y-m-d')) {
				echo $this->ZuluruHtml->iconLink('attendance_24.png',
					array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']),
					array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));
			}
		}

		echo $this->ZuluruGame->displayScore ($game);

		if (Configure::read('feature.annotations')) {
			echo $this->Html->link(__('Add Note', true), array('controller' => 'games', 'action' => 'note', 'game' => $game['Game']['id']));
		}
		?></td>
	</tr>
<?php endforeach; ?>
</table>

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
<?php endif; ?>

</div>

<?php echo $this->element('games/attendance_div'); ?>
