<?php
if (!$attendance['Team']['track_attendance'] && Configure::read('feature.attendance')) {
	echo $this->Html->para('warning-message', sprintf(__('Because this team does not have attendance tracking enabled, the list below assumes that all regular players are attending and all subs are unknown. To enable attendance tracking, %s.', true),
			$this->Html->link(__('edit the team record', true), array('controller' => 'teams', 'action' => 'edit', 'team' => $attendance['Team']['id']))));
}

$style = 'width:' . floor(80 / count($game['Division']['League']['StatType'])) . '%;';
?>
<table class="list" id="team_<?php echo $attendance['Team']['id']; ?>">
<thead>
<tr>
	<th><?php __('Player'); ?></th>
	<?php if (Configure::read('feature.attendance')): ?>
	<th class="attendance_column"><?php __('Attendance'); ?></th>
	<?php endif; ?>
<?php
foreach ($stats as $stat) {
	echo $this->Html->tag('th', __($stat['name'], true), compact('style'));
}
?>
</tr>
</thead>
<tbody>
<?php
foreach ($attendance['Person'] as $person):
	if (!empty($person['Attendance'])):
		$record = $person['Attendance'][0];
?>
<tr class="status_<?php echo $record['status']; ?>">
<td><?php echo $this->element('people/block', compact('person')); ?></td>
<?php if (Configure::read('feature.attendance')): ?>
<td class="attendance_column"><?php
		echo $this->element('games/attendance_change', array(
			'team' => $attendance['Team'],
			'game_id' => $game['Game']['id'],
			'game_date' => $game['GameSlot']['game_date'],
			'game_time' => $game['GameSlot']['game_start'],
			'person_id' => $person['id'],
			'role' => $person['TeamsPerson']['role'],
			'status' => $record['status'],
			'comment' => $record['comment'],
			'dedicated' => true,
			// Only captains and conveners have permission to enter stats, and they can also update attendance
			'is_captain' => true,
			// We need to display this even if teams have attendance tracking off
			'force' => true,
		));
?></td>
<?php endif; ?>
<?php foreach ($stats as $stat): ?>
<td style="<?php echo $style; ?>">
<?php
			$i = fake_id();
			$stat_record = Set::extract("/Stat[team_id={$attendance['Team']['id']}][person_id={$person['id']}][stat_type_id={$stat['id']}]/.", $this->data);
			if (!empty($stat_record)) {
				$stat_record = array_shift($stat_record);
				if (!empty($stat_record['id'])) {
					echo $this->ZuluruForm->hidden("Stat.$i.id", array('value' => $stat_record['id']));
				}
			} else {
				$stat_record = array(
					'game_id' => $game['Game']['id'],
					'team_id' => $attendance['Team']['id'],
					'person_id' => $person['id'],
					'stat_type_id' => $stat['id'],
					'value' => null,
				);
			}
			echo $this->ZuluruForm->hidden("Stat.$i.game_id", array('value' => $stat_record['game_id']));
			echo $this->ZuluruForm->hidden("Stat.$i.team_id", array('value' => $stat_record['team_id']));
			echo $this->ZuluruForm->hidden("Stat.$i.person_id", array('value' => $stat_record['person_id']));
			echo $this->ZuluruForm->hidden("Stat.$i.stat_type_id", array('value' => $stat_record['stat_type_id']));

			if (empty($person['TeamsPerson']['position']) || Stat::applicable($stat, $person['TeamsPerson']['position']) || !empty($stat_record['value'])) {
				$class = '';
			} else {
				$class = ' unapplicable';
			}
			echo $this->ZuluruForm->input("Stat.$i.value", array('div' => false, 'label' => false, 'size' => 3, 'type' => 'number', 'class' => "stat_{$stat['id']}$class", 'value' => $stat_record['value']));
?>
</td>
<?php endforeach; ?>
</tr>
<?php
	endif;
endforeach;
?>

<?php if (0 && $attendance['Team']['track_attendance'] && Configure::read('feature.attendance')): ?>
<tr id="add_row_<?php echo $attendance['Team']['id']; ?>">
<td colspan="<?php echo 2 + count($stats); ?>"><?php
echo $this->Html->link(__('Add a sub', true),
		array('controller' => 'games', 'action' => 'add_sub', 'game' => $game['Game']['id'], 'team' => $attendance['Team']['id']),
		array('onclick' => "add_sub({$game['Game']['id']}, {$attendance['Team']['id']}, 'stats', 'add_row_{$attendance['Team']['id']}'); return false;"));
?></td>
</tr>
<?php endif; ?>

<tr id="sub_row">
	<td><?php __('Unlisted Subs'); ?></td>
<?php if (Configure::read('feature.attendance')): ?>
	<td class="attendance_column"></td>
<?php endif; ?>
<?php foreach ($stats as $stat): ?>
<td>
<?php
	$i = fake_id();
	$stat_record = Set::extract("/Stat[team_id={$attendance['Team']['id']}][person_id=0][stat_type_id={$stat['id']}]/.", $this->data);
	if (!empty($stat_record[0]['id'])) {
		$stat_record = array_shift($stat_record);
		echo $this->ZuluruForm->hidden("Stat.$i.id", array('value' => $stat_record['id']));
	} else {
		$stat_record = array(
			'game_id' => $game['Game']['id'],
			'team_id' => $attendance['Team']['id'],
			'person_id' => 0,
			'stat_type_id' => $stat['id'],
			'value' => null,
		);
	}
	echo $this->ZuluruForm->hidden("Stat.$i.game_id", array('value' => $stat_record['game_id']));
	echo $this->ZuluruForm->hidden("Stat.$i.team_id", array('value' => $stat_record['team_id']));
	echo $this->ZuluruForm->hidden("Stat.$i.person_id", array('value' => $stat_record['person_id']));
	echo $this->ZuluruForm->hidden("Stat.$i.stat_type_id", array('value' => $stat_record['stat_type_id']));
	echo $this->ZuluruForm->input("Stat.$i.value", array('div' => false, 'label' => false, 'size' => 3, 'type' => 'number', 'class' => "stat_{$stat['id']}", 'value' => $stat_record['value']));
?>
</td>
<?php endforeach; ?>
</tr>
</tbody>
<tfoot>
<tr>
	<th><?php __('Total'); ?></th>
<?php if (Configure::read('feature.attendance')): ?>
	<th class="attendance_column"></th>
<?php endif; ?>
<?php
foreach ($stats as $stat) {
	echo $this->Html->tag('th', 0, array('class' => "stat_{$stat['id']}", 'data-handler' => $stat['sum_function'], 'data-formatter' => $stat['formatter_function']));
}
?>
</tr>
</tfoot>
</table>
