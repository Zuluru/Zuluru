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
			if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED && $item['GameSlot']['game_date'] >= $roster[0]['created']) {
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
		}
		if (in_array($item['AwayTeam']['id'], $team_ids) && $item['AwayTeam']['track_attendance']) {
			$roster = Set::extract("/TeamsPerson[person_id=$id][team_id={$item['AwayTeam']['id']}]/.", $teams);
			if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED && $item['GameSlot']['game_date'] >= $roster[0]['created']) {
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
			if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED && $item['GameSlot']['game_date'] >= $roster[0]['created']) {
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
		}
		if (in_array($item['AwayTeam']['id'], $team_ids) && $item['AwayTeam']['track_attendance']) {
			$roster = Set::extract("/TeamsPerson[person_id={$relative['Relative']['id']}][team_id={$item['AwayTeam']['id']}]/.", $teams);
			if (!empty($roster) && $roster[0]['status'] == ROSTER_APPROVED && $item['GameSlot']['game_date'] >= $roster[0]['created']) {
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
					'future_only' => false,
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
						'event_date' => $item['TeamEvent']['date'],
						'event_time' => $item['TeamEvent']['start'],
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
						'event_date' => $item['TeamEvent']['date'],
						'event_time' => $item['TeamEvent']['start'],
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
