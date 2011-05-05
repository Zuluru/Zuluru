<?php
$this->Html->addCrumb (__('Home', true));
$this->Html->addCrumb ($name);
?>

<div class="all splash">
<?php echo $this->Html->tag('h2', $name); ?>

<?php
$empty = true;

if (!empty($unpaid)) {
	$empty = false;
	echo $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
			count($unpaid),
			__(count($unpaid) > 1 ? 'registrations' : 'registration', true),
			$this->Html->link (__('Click here', true), array('controller' => 'registrations', 'action' => 'checkout'))
	));
}
?>

<table cellpadding="0" cellspacing="0">
<tr>
	<th colspan="2"><?php
	__('My Teams');
	echo $this->ZuluruHtml->help(array('action' => 'teams', 'my_teams'));
	?></th>
</tr>
<?php
$empty = false;
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			echo $this->element('team/block', array('team' => $team['Team'])) .
				' (' . $this->element('people/roster', array('roster' => $team['TeamsPerson'], 'league' => $team['League'])) . ')';
		?></td>
		<td class="actions splash_action">
			<?php
			if ($team['League']['roster_deadline'] >= date('Y-m-d') &&
				in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs')))
			{
				echo $this->ZuluruHtml->iconLink('roster_add_24.png',
					array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']),
					array('alt' => __('Add Player', true), 'title' => __('Add Player', true)));
			}
			if ($team['Team']['track_attendance']) {
				echo $this->ZuluruHtml->iconLink('attendance_24.png',
					array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['Team']['id']),
					array('alt' => __('Attendance', true), 'title' => __('View Season Attendance Report', true)));
			}
			echo $this->ZuluruHtml->iconLink('schedule_24.png',
				array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id']),
				array('alt' => __('Schedule', true), 'title' => __('View Team Schedule', true)));
			echo $this->ZuluruHtml->iconLink('standings_24.png',
				array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']),
				array('alt' => __('Standings', true), 'title' => __('View Team Standings', true)));
			if ($is_admin || in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				echo $this->ZuluruHtml->iconLink('edit_24.png',
					array('controller' => 'teams', 'action' => 'edit', 'team' => $team['Team']['id']),
					array('alt' => __('Edit', true), 'title' => __('Edit Team', true)));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams')); ?> </li>
	</ul>
</div>

<?php if (!empty ($leagues)) : ?>
<table cellpadding="0" cellspacing="0">
<tr>
	<th colspan="2"><?php __('Leagues Coordinated');?></th>
</tr>
<?php
$empty = false;
$i = 0;
foreach ($leagues as $league):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			echo $this->ZuluruHtml->link($league['League']['name'],
					array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']),
					array('max_length' => 32));
		?></td>
		<td class="actions splash_action">
			<?php
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'leagues', 'action' => 'edit', 'league' => $league['League']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit League', true)));
			if ($league['League']['schedule_type'] != 'none') {
				echo $this->ZuluruHtml->iconLink('schedule_24.png',
					array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league['League']['id']),
					array('alt' => __('Schedule', true), 'title' => __('View League Schedule', true)));
				echo $this->ZuluruHtml->iconLink('standings_24.png',
					array('controller' => 'leagues', 'action' => 'standings', 'league' => $league['League']['id']),
					array('alt' => __('Standings', true), 'title' => __('View League Standings', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_24.png',
					array('controller' => 'leagues', 'action' => 'approve_scores', 'league' => $league['League']['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_24.png',
					array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id']),
					array('alt' => __('Add games', true), 'title' => __('Add games', true)));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<table cellpadding="0" cellspacing="0">
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
			echo $this->element('team/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16))) .
				' (' . __('home', true) . ') ' .
				__('vs.', true) . ' ' .
				$this->element('team/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16))) .
				' (' . __('away', true) . ') ' .
				__('at', true) . ' ' .
				$this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
					array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']),
					array('title' => "{$game['GameSlot']['Field']['name']} {$game['GameSlot']['Field']['num']}"));
		?></td>
		<td class="actions splash_action"><?php
		if (in_array ($game['HomeTeam']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
			$team = $game['HomeTeam'];
		} else {
			$team = $game['AwayTeam'];
		}
		if ($team['track_attendance']) {
			$position = Set::extract("/TeamsPerson[team_id={$team['id']}]/position", $teams);
			echo $this->element('game/attendance_change', array(
				'team' => $team,
				'game_id' => $game['Game']['id'],
				'game_date' => $game['GameSlot']['game_date'],
				'game_time' => $game['GameSlot']['game_start'],
				'position' => $position[0],
				'status' => (array_key_exists (0, $game['Attendance']) ? $game['Attendance'][0]['status'] : ATTENDANCE_UNKNOWN),
				'future_only' => true,
			));
			if ($game['GameSlot']['game_date'] >= date('Y-m-d')) {
				echo $this->ZuluruHtml->iconLink('attendance_24.png',
					array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']),
					array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));
			}
		}

		echo $this->ZuluruGame->displayScore ($game);
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
		array('target' => '_blank'));
} else {
	echo $this->Html->link (__('Edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'));
	__(' to enable your personal iCal feed');
}
?>. <?php echo $this->ZuluruHtml->help(array('action' => 'games', 'personal_feed')); ?></p>

<?php
if ($empty) {
	echo $this->Html->para (null, sprintf (__('You are not yet on any teams. Perhaps you would like to %s, %s or %s.', true),
		$this->Html->link ('register for membership or an event', array('controller' => 'events', 'action' => 'wizard')),
		$this->Html->link ('look for a team to join', array('controller' => 'teams')),
		$this->Html->link ('check out the leagues we are currently offering', array('controller' => 'leagues'))
	));
}
?>

</div>

<?php echo $this->element('game/attendance_div'); ?>
