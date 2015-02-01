<?php
$style = 'width:' . floor(80 / count($game['Division']['League']['StatType'])) . '%;';
?>

<div class="stat_sheet">
<h2><?php __('Stat Entry Sheet'); ?></h2>
<?php // Seems that dompdf doesn't deal well with DLs that use floats ?>
<table>
	<tr>
		<td><?php __('Date &amp; time'); ?>:</td>
		<td><?php echo $this->ZuluruTime->fulldatetime($game['GameSlot']['game_date'] . ' ' . $game['GameSlot']['game_start']) . ' - ' . $this->ZuluruTime->time($game['GameSlot']['display_game_end']); ?></td>
	</tr>
	<tr>
		<td><?php __('Team'); ?>:</td>
		<td><?php
		echo $team['name'] . ' (';
		if ($team['id'] == $game['Game']['home_team']) {
			__('home');
		} else {
			__('away');
		}
		echo ')';
		?></td>
	</tr>
	<tr>
		<td><?php __('Opponent'); ?>:</td>
		<td><?php
		echo $opponent['name'] . ' (';
		if ($opponent['id'] == $game['Game']['home_team']) {
			__('home');
		} else {
			__('away');
		}
		echo ')';
		?></td>
	</tr>
	<tr>
		<td><?php __('Location'); ?>:</td>
		<td><?php echo $game['GameSlot']['Field']['long_name']; ?></td>
	</tr>
	<tr>
		<td><?php __('Final score'); ?>:</td>
		<td><?php echo $team['name']; ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php __('Opponent'); ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td><?php __('Timeouts taken'); ?>:</td>
		<td><?php echo $team['name']; ?>: [&nbsp;] [&nbsp;] [&nbsp;]  <?php __('Opponent'); ?>: [&nbsp;] [&nbsp;] [&nbsp;]</td>
	</tr>
	<?php if (Configure::read('sport.start.stat_sheet')): ?>
	<tr>
		<td><?php __(Configure::read('sport.start.stat_sheet')); ?>:</td>
		<td><?php echo $team['name']; ?>: [&nbsp;]  <?php __('Opponent'); ?>: [&nbsp;]<?php
		if (Configure::read('sport.start.stat_sheet_direction')):?>  <?php __('End'); ?>:<?php endif; ?></td>
	</tr>
	<?php elseif(Configure::read('sport.start.stat_sheet_direction')): ?>
	<tr>
		<td><?php __('Starting end'); ?>:</td>
		<td><?php echo $team['name']; ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php __('Opponent'); ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<?php endif; ?>
</table>

<table>
<thead>
<tr>
	<th><?php __('Player'); ?></th>
<?php
foreach ($game['Division']['League']['StatType'] as $stat) {
	echo $this->Html->tag('th', __($stat['name'], true), compact('style'));
}
?>
</tr>
</thead>
<tbody>
<?php
foreach ($attendance['Person'] as $person):
	if (!empty($person['Attendance']) && $person['Attendance'][0]['status'] == ATTENDANCE_ATTENDING):
?>
<tr>
<td><?php echo $person['full_name']; ?></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
<?php
	endif;
endforeach;
?>

<tr>
	<td></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
<tr>
	<td></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
<tr>
	<td></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
<tr>
	<td><?php __('Unlisted Subs'); ?></td>
<?php foreach ($game['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
</tbody>
</table>

<fieldset>
<legend><?php __('Game Notes'); ?></legend>
<p><br /><br /><br /><br /><br /><br /><br /><br /></p>
</fieldset>
</div>
