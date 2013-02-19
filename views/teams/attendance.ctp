<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Season Attendance', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams">
<h2><?php  __('Season Attendance'); ?></h2>
<?php
$all_games = array();

if (count($days) > 1) {
	$prefix = __('Week of', true) . ' ';
} else {
	$prefix = null;
}

foreach ($dates as $date) {
	$games_on_date = array();
	$match_dates = Game::_matchDates($date, $days);
	foreach ($match_dates as $match_date) {
		$games_on_date = array_merge($games_on_date, Set::extract("/GameSlot[game_date=$match_date]/..", $games));
	}
	if (!empty($games_on_date)) {
		foreach ($games_on_date as $game) {
			$all_games[] = array(
				'date' => $game['GameSlot']['game_date'], 'time' => $game['GameSlot']['game_start'],
				'condition' => "game_id={$game['Game']['id']}",
				'header' => $this->element('games/block', array('game' => $game)),
			);
		}
	} else {
		$all_games[] = array(
			'date' => $date, 'time' => '00:00:00',
			'condition' => "game_date=$date",
			'header' => $prefix . $this->ZuluruTime->date($date),
		);
	}
}

foreach ($event_attendance as $event) {
	$all_games[] = array(
		'date' => $event['TeamEvent']['date'], 'time' => $event['TeamEvent']['start'],
		'event' => $event,
		'header' => $this->ZuluruHtml->link ($event['TeamEvent']['name'],
				array('controller' => 'team_events', 'action' => 'view', 'event' => $event['TeamEvent']['id']),
				array('title' => $this->ZuluruTime->datetime("{$event['TeamEvent']['date']} {$event['TeamEvent']['start']}"))
		),
	);
}

function compareDateAndTime($a, $b) {
	if ($a['date'] > $b['date']) {
		return 1;
	} else if ($a['date'] < $b['date']) {
		return -1;
	}
	if ($a['time'] > $b['time']) {
		return 1;
	} else if ($a['time'] < $b['time']) {
		return -1;
	}
	return 0;
}
usort ($all_games, 'compareDateAndTime');

?>
<table class="list">
	<thead>
	<tr>
		<th></th>
		<?php
		foreach ($all_games as $game) {
			echo $this->Html->tag('th', $game['header']);
		}
		?>
		<th><?php __('Total'); ?></th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 1;
	$statuses = Configure::read('attendance');
	$count = array_fill_keys(array_keys($statuses), array_fill_keys(array_keys($all_games), array('Male' => 0, 'Female' => 0)));
	foreach ($attendance['Person'] as $person):
		$class = $td_class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<?php
		$total = 0;
		foreach ($all_games as $key => $details):
			if (array_key_exists('event', $details)) {
				$record = Set::extract("/Attendance[person_id={$person['id']}]/.", $details['event']);
				if (empty ($record)) {
					$out = __('N/A', true);
					$status = ATTENDANCE_UNKNOWN;
				} else {
					$status = $record[0]['status'];
					++$count[$status][$key][$person['gender']];
					$out = $this->element('team_events/attendance_change', array(
						'team' => $team['Team'],
						'event_id' => $details['event']['TeamEvent']['id'],
						'date' => $details['date'],
						'time' => $details['time'],
						'person_id' => $person['id'],
						'role' => $person['TeamsPerson']['role'],
						'status' => $status,
						'comment' => $record[0]['comment'],
						'dedicated' => true,
					));
				}
			} else {
				$record = Set::extract("/Attendance[{$details['condition']}]/.", $person);
				if (empty ($record)) {
					$out = __('N/A', true);
					$status = ATTENDANCE_UNKNOWN;
				} else {
					$status = $record[0]['status'];
					if ($status == ATTENDANCE_ATTENDING) {
						++$total;
					}
					++$count[$status][$key][$person['gender']];
					$out = $this->element('games/attendance_change', array(
						'team' => $team['Team'],
						'game_id' => $record[0]['game_id'],
						'game_date' => $details['date'],
						'game_time' => $details['time'],
						'person_id' => $person['id'],
						'role' => $person['TeamsPerson']['role'],
						'status' => $status,
						'comment' => $record[0]['comment'],
						'dedicated' => true,
					));
				}
			}
		?>
		<td class="<?php echo low($statuses[$status]);?>"><?php echo $out; ?></td>
		<?php endforeach; ?>
		<td><?php echo $total; ?></td>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
	</tr>
	<?php endforeach; ?>

	<tr>
		<th></th>
		<?php
		foreach ($all_games as $game) {
			echo $this->Html->tag('th', $game['header']);
		}
		?>
		<th><?php __('Total'); ?></th>
		<th></th>
	</tr>

	<?php
	foreach ($statuses as $status => $description):
		$counts = array();
		foreach (array_keys($all_games) as $key) {
			foreach (array('Male', 'Female') as $gender) {
				if ($count[$status][$key][$gender]) {
					$counts[$key][] = $count[$status][$key][$gender] . substr (__($gender, true), 0, 1);
				}
			}
		}
		if (!empty ($counts)):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}

			$low = Inflector::slug(low($description));
			$icon = $this->ZuluruHtml->icon("attendance_{$low}_dedicated_24.png");
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $icon . '&nbsp;' . __($description, true); ?></td>
		<?php foreach (array_keys($all_games) as $key): ?>
		<td><?php
		if (array_key_exists($key, $counts)) {
			echo implode(' / ', $counts[$key]);
		}
		?></td>
		<?php endforeach; ?>
		<td></td>
		<td></td>
	</tr>
	<?php
		endif;
	endforeach;
	?>
	</tbody>

</table>
</div>

<div class="actions">
	<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list')); ?>
</div>

<?php echo $this->element('games/attendance_div'); ?>
