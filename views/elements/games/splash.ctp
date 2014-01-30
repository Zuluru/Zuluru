<?php if (!empty($items)): ?>
<table class="list">
<tr>
	<th colspan="3"><?php
	__('Recent and Upcoming Schedule');
	echo $this->ZuluruHtml->help(array('action' => 'games', 'recent_and_upcoming'));
	?></th>
</tr>
<?php
usort($items, array('Game', 'compareDateAndField'));
if (!isset($person_id)) {
	$person_id = null;
}
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
			}
			if ($item['Division']['schedule_type'] != 'competition') {
				__(' vs. ');
				if ($item['Game']['away_team'] === null) {
					echo $item['Game']['away_dependency'];
				} else {
					echo $this->element('teams/block', array('team' => $item['AwayTeam'], 'options' => array('max_length' => 16))) .
						' (' . __('away', true) . ')';
				}
			}
			__(' at ');
			echo $this->element('fields/block', array('field' => $item['GameSlot']['Field']));
		?></td>
		<td class="actions splash_action"><?php
		if (in_array ($item['HomeTeam']['id'], $team_ids) && in_array ($item['AwayTeam']['id'], $team_ids)) {
			// This person is on both teams; pick the one they're more important on...
			// TODO: Better handling of this, as well as deal with game notes in such cases
			$home_role = array_pop(Set::extract("/TeamsPerson[team_id={$item['HomeTeam']['id']}]/role", $teams));
			$away_role = array_pop(Set::extract("/TeamsPerson[team_id={$item['AwayTeam']['id']}]/role", $teams));
			$importance = array_flip(array_reverse(array_keys(Configure::read('options.roster_role'))));
			if ($importance[$home_role] >= $importance[$away_role]) {
				$team = $item['HomeTeam'];
			} else {
				$team = $item['AwayTeam'];
			}
		} else if (in_array ($item['HomeTeam']['id'], $team_ids)) {
			$team = $item['HomeTeam'];
		} else {
			$team = $item['AwayTeam'];
		}
		if ($team['track_attendance']) {
			$roster = reset(Set::extract("/TeamsPerson[team_id={$team['id']}]/.", $teams));
			if ($roster['status'] == ROSTER_APPROVED) {
				$is_captain = in_array($team['id'], $this->UserCache->read('OwnedTeamIDs'));
				echo $this->element('games/attendance_change', array(
					'team' => $team,
					'game_id' => $item['Game']['id'],
					'game_date' => $item['GameSlot']['game_date'],
					'game_time' => $item['GameSlot']['game_start'],
					'person_id' => $person_id,
					'role' => $roster['role'],
					'status' => (array_key_exists (0, $item['Attendance']) ? $item['Attendance'][0]['status'] : ATTENDANCE_UNKNOWN),
					'comment' => (array_key_exists (0, $item['Attendance']) ? $item['Attendance'][0]['comment'] : null),
					'is_captain' => $is_captain,
					'future_only' => true,
				));
				if ($item['GameSlot']['game_date'] >= date('Y-m-d')) {
					echo $this->ZuluruHtml->iconLink('attendance_24.png',
						array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $item['Game']['id']),
						array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));

					if ($is_captain && Configure::read('scoring.stat_tracking') && League::hasStats($item['Division']['League'])) {
						echo $this->ZuluruHtml->iconLink('pdf_24.png',
								array('controller' => 'games', 'action' => 'stat_sheet', 'team' => $team['id'], 'game' => $item['Game']['id']),
								array('alt' => __('Stat Sheet', true), 'title' => __('Stat Sheet', true)),
								array('confirm' => __('This stat sheet will only include players who have indicated that they are playing, plus a couple of blank lines.\n\nFor a stat sheet with your full roster, use the link from the team view page.', true)));
					}
				}
			}
		}

		echo $this->ZuluruGame->displayScore ($item, $item['Division'], $item['Division']['League']);

		if (Configure::read('feature.annotations')) {
			echo $this->Html->link(__('Add Note', true), array('controller' => 'games', 'action' => 'note', 'game' => $item['Game']['id']));
		}
		?></td>
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
		<td class="actions splash_action"><?php
			if ($item['Team']['track_attendance']) {
				$roster = Set::extract("/TeamsPerson[team_id={$item['Team']['id']}]/.", $teams);
				if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED) {
					echo $this->element('team_events/attendance_change', array(
						'team' => $item['Team'],
						'event_id' => $item['TeamEvent']['id'],
						'date' => $item['TeamEvent']['date'],
						'time' => $item['TeamEvent']['start'],
						'person_id' => $person_id,
						'role' => $roster[0]['role'],
						'status' => (array_key_exists (0, $item['Attendance']) ? $item['Attendance'][0]['status'] : ATTENDANCE_UNKNOWN),
						'comment' => (array_key_exists (0, $item['Attendance']) ? $item['Attendance'][0]['comment'] : null),
						'is_captain' => in_array($item['Team']['id'], $this->UserCache->read('OwnedTeamIDs')),
					));
				}

				if ($item['TeamEvent']['date'] >= date('Y-m-d')) {
					echo $this->ZuluruHtml->iconLink('attendance_24.png',
						array('controller' => 'team_events', 'action' => 'view', 'event' => $item['TeamEvent']['id']),
						array('alt' => __('Attendance', true), 'title' => __('View Event Attendance', true)));
				}
			}
		?></td>
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
		<td class="actions splash_action"><?php
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
