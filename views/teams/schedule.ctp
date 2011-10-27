<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Schedule', true));
?>

<?php
$display_spirit = $is_admin || $is_coordinator || $team['League']['display_sotg'] != 'coordinator_only';
?>
<div class="teams schedule">
<h2><?php  echo __('Team Schedule', true) . ': ' . $team['Team']['name'];?></h2>
<?php if (!empty($team['Game'])):?>
	<table class="list">
		<tr>
			<th><?php __('Date'); ?></th>
			<th><?php __('Time'); ?></th>
			<th><?php __('Field'); ?></th>
			<th><?php __('Opponent'); ?></th>
			<th><?php __('Score'); ?></th>
			<?php if ($display_spirit): ?>
			<th><?php __('Spirit'); ?></th>
			<?php endif; ?>
			<?php if ($display_attendance): ?>
			<th><?php __('Attendance'); ?></th>
			<?php endif; ?>
		</tr>
	<?php
	$i = 0;
	foreach ($team['Game'] as $game):
		if (! ($game['Game']['published'] || $is_admin || $is_coordinator)) {
			continue;
		}
		$classes = array();
		if ($i++ % 2 == 0) {
			$classes[] = 'altrow';
		}
		if (!$game['Game']['published']) {
			$classes[] = 'unpublished';
		}
		Game::_adjustEntryIndices ($game);
		if (array_key_exists ($team['Team']['id'], $game['SpiritEntry'])) {
			$value = $game['SpiritEntry'][$team['Team']['id']]['entered_sotg'];
		} else {
			$value = null;
		}
	?>
		<tr<?php if (!empty ($classes)) echo ' class="' . implode (' ', $classes) . '"'; ?>>
			<td><?php echo $this->ZuluruTime->fulldate($game['GameSlot']['game_date']); ?></td>
			<td><?php
			$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
					$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
			echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
			?></td>
			<td><?php echo $this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
					array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), array('title' => "{$game['GameSlot']['Field']['name']} {$game['GameSlot']['Field']['num']}")); ?></td>
			<td><?php
			if ($team['Team']['id'] == $game['Game']['home_team']) {
				echo $this->element('team/block', array('team' => $game['AwayTeam']));
			} else {
				echo $this->element('team/block', array('team' => $game['HomeTeam']));
			}
			?></td>
			<td class="actions"><?php echo $this->ZuluruGame->displayScore ($game, $team['Team']['id']); ?></td>
			<?php if ($display_spirit): ?>
			<td><?php echo $this->element ('spirit/symbol', array(
					'spirit_obj' => $spirit_obj,
					'type' => $team['League']['display_sotg'],
					'is_coordinator' => $is_coordinator,
					'value' => $value,
			)); ?></td>
			<?php endif; ?>
			<?php if ($display_attendance): ?>
			<td class="actions"><?php
			echo $this->Html->link(__('View', true), array('controller' => 'games', 'action' => 'attendance', 'team' => $team['Team']['id'], 'game' => $game['Game']['id']));
			$counts = array();
			foreach (array('Male', 'Female') as $gender) {
				$count = count(Set::extract("/Person[gender=$gender]", $game['Attendance']));
				if ($count) {
					$counts[] = $count . substr (__($gender, true), 0, 1);
				}
			}
			echo implode (' / ', $counts);
			?></td>
			<?php endif; ?>
		</tr>
	<?php
	endforeach;
	?>
	</table>
<?php endif; ?>
<p>Get your team schedule in <?php // TODO: Better image locations, alt text
echo $this->ZuluruHtml->iconLink ('ical.gif', array('action' => 'ical', $team['Team']['id'], 'team.ics'), array('alt' => 'iCal'));
?> format or <?php
echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif', 'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('action' => 'ical', $team['Team']['id']), true), array('alt' => 'add to Google Calendar'), array('target' => '_blank'));
?>.</p>
</div>

<div class="actions">
	<ul>
		<?php
		if ($team['League']['id']) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('view_32.png',
				array('action' => 'view', 'team' => $team['Team']['id']),
				array('alt' => __('View', true), 'title' => __('View Team Details and Roster', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('standings_32.png',
				array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']),
				array('alt' => __('Standings', true), 'title' => __('View Team Standings', true))));
		}
		if ($team['Team']['track_attendance'] &&
			in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('attendance_32.png',
				array('action' => 'attendance', 'team' => $team['Team']['id']),
				array('alt' => __('Attendance', true), 'title' => __('View Season Attendance Report', true))));
		}
		if ($is_logged_in && $team['Team']['open_roster'] && $team['League']['roster_deadline'] >= date('Y-m-d') &&
			!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('roster_add_32.png',
				array('action' => 'roster_request', 'team' => $team['Team']['id']),
				array('alt' => __('Join Team', true), 'title' => __('Join Team', true))));
		}
		if ($is_admin || $is_captain) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('edit_32.png',
				array('action' => 'edit', 'team' => $team['Team']['id']),
				array('alt' => __('Edit Team', true), 'title' => __('Edit Team', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('email_32.png',
				array('action' => 'emails', 'team' => $team['Team']['id']),
				array('alt' => __('Player Emails', true), 'title' => __('Player Emails', true))));
		}
		if ($is_admin || (($is_captain || $is_coordinator) && $team['League']['roster_deadline'] >= date('Y-m-d'))) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('roster_add_32.png',
				array('action' => 'add_player', 'team' => $team['Team']['id']),
				array('alt' => __('Add Player', true), 'title' => __('Add Player', true))));
		}
		if ($is_admin || $is_coordinator) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('spirit_32.png',
				array('action' => 'spirit', 'team' => $team['Team']['id']),
				array('alt' => __('Spirit', true), 'title' => __('See Team Spirit Report', true))));
		}
		if ($is_admin) {
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('move_32.png',
				array('action' => 'move', 'team' => $team['Team']['id']),
				array('alt' => __('Move Team', true), 'title' => __('Move Team', true))));
			echo $this->Html->tag ('li', $this->ZuluruHtml->iconLink('delete_32.png',
				array('action' => 'delete', 'team' => $team['Team']['id']),
				array('alt' => __('Delete', true), 'title' => __('Delete Team', true)),
				array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id']))));
		}
		?>
	</ul>
</div>
