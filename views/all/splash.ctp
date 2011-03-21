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
$roster_descriptions = Configure::read('options.roster_position');
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
				' (' . $this->Html->link(__($roster_descriptions[$team['TeamsPerson']['status']], true),
						array('controller' => 'teams', 'action' => 'roster_status', 'team' => $team['Team']['id'], 'person' => $id)) .
				')';
		?></td>
		<td class="actions splash_action">
			<?php
			if ($team['League']['roster_deadline'] >= date('Y-m-d') &&
				($is_admin || in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'))))
			{
				echo $this->Html->link(__('Add player', true), array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']));
			}
			?>
			<?php echo $this->Html->link(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id'])); ?>
			<?php echo $this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id'])); ?>
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
			<?php echo $this->Html->link(__('Edit', true), array('controller' => 'leagues', 'action' => 'edit', 'league' => $league['League']['id'])); ?>
			<?php if ($league['League']['schedule_type'] != 'none') : ?>
			<?php echo $this->Html->link(__('Schedule', true), array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league['League']['id'])); ?>
			<?php echo $this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $league['League']['id'])); ?>
			<?php echo $this->Html->link(__('Approve scores', true), array('controller' => 'leagues', 'action' => 'approve_scores', 'league' => $league['League']['id'])); ?>
			<?php echo $this->Html->link(__('Add games', true), array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id'])); ?>
			<?php endif; ?>
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
		<td class="actions splash_action"><?php echo $this->ZuluruGame->displayScore ($game); ?></td>
	</tr>
<?php endforeach; ?>
</table>

<p><?php
if (Configure::read('personal.enable_ical')) {
	__('Get your personal schedule in ');
	// TODOIMG: Better image locations, alt text
	echo $this->ZuluruHtml->imageLink ('/img/ical.gif', array('controller' => 'people', 'action' => 'ical', $id, 'player.ics'), array('alt' => 'iCal'));
	__(' format or ');
	echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif', 'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('controller' => 'people', 'action' => 'ical', $id), true), array('alt' => 'add to Google Calendar'), array('target' => '_blank'));
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
