<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Season Attendance', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams">
<h2><?php  __('Season Attendance'); ?></h2>
<table>
	<thead>
	<tr>
		<th></th>
		<?php
		$all_games = array();
		foreach ($dates as $date) {
			$games_on_date = Set::extract("/GameSlot[game_date=$date]/..", $games);
			if (!empty($games_on_date)) {
				foreach ($games_on_date as $game) {
					echo $this->Html->tag('th', $this->element('game/block', array('game' => $game)));
					$all_games[] = array('date' => $date, 'time' => $game['GameSlot']['game_start'], 'condition' => "game_id={$game['Game']['id']}");
				}
			} else {
				echo $this->Html->tag('th', $this->ZuluruTime->date($date));
				$all_games[] = array('date' => $date, 'time' => '00:00:00', 'condition' => "game_date=$date");
			}
		}
		?>
		<th><?php __('Total'); ?></th>
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
				$out = $this->element('game/attendance_change', array(
					'team' => $team['Team'],
					'game_id' => $record[0]['game_id'],
					'game_date' => $details['date'],
					'game_time' => $details['time'],
					'person_id' => $person['id'],
					'position' => $person['TeamsPerson']['position'],
					'status' => $status,
				));
			}
		?>
		<td class="<?php echo low($statuses[$status]);?>"><?php echo $out; ?></td>
		<?php endforeach; ?>
		<td><?php echo $total; ?></td>
	</tr>
	<?php endforeach; ?>

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
			$icon = $this->ZuluruHtml->icon("attendance_{$low}_24.png");
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
	</tr>
	<?php
		endif;
	endforeach;
	?>
	</tbody>

</table>

<?php echo $this->element('game/attendance_div'); ?>
