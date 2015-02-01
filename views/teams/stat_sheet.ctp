<?php
$style = 'width:' . floor(80 / count($team['Division']['League']['StatType'])) . '%;';
?>

<div class="stat_sheet">
<h2><?php __('Stat Entry Sheet'); ?></h2>
<?php // Seems that dompdf doesn't deal well with DLs that use floats ?>
<table>
	<tr>
		<td><?php __('Date &amp; time'); ?>:</td>
		<td></td>
	</tr>
	<tr>
		<td><?php __('Team'); ?>:</td>
		<td><?php echo $team['Team']['name'] . ' (' . __('home/away', true) . ')'; ?></td>
	</tr>
	<tr>
		<td><?php __('Opponent'); ?>:</td>
		<td></td>
	</tr>
	<tr>
		<td><?php __('Location'); ?>:</td>
		<td></td>
	</tr>
	<tr>
		<td><?php __('Final score'); ?>:</td>
		<td><?php echo $team['Team']['name']; ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php __('Opponent'); ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td><?php __('Timeouts taken'); ?>:</td>
		<td><?php echo $team['Team']['name']; ?>: [&nbsp;] [&nbsp;] [&nbsp;]  <?php __('Opponent'); ?>: [&nbsp;] [&nbsp;] [&nbsp;]</td>
	</tr>
	<?php if (Configure::read('sport.start.stat_sheet')): ?>
	<tr>
		<td><?php __(Configure::read('sport.start.stat_sheet')); ?>:</td>
		<td><?php echo $team['Team']['name']; ?>: [&nbsp;]  <?php __('Opponent'); ?>: [&nbsp;]<?php
		if (Configure::read('sport.start.stat_sheet_direction')):?>  <?php __('End'); ?>:<?php endif; ?></td>
	</tr>
	<?php elseif(Configure::read('sport.start.stat_sheet_direction')): ?>
	<tr>
		<td><?php __('Starting end'); ?>:</td>
		<td><?php echo $team['Team']['name']; ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <?php __('Opponent'); ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<?php endif; ?>
</table>

<table>
<thead>
<tr>
	<th><?php __('Player'); ?></th>
<?php
foreach ($team['Division']['League']['StatType'] as $stat) {
	echo $this->Html->tag('th', __($stat['name'], true), compact('style'));
}
?>
</tr>
</thead>
<tbody>
<?php
foreach ($team['Person'] as $person):
?>
<tr>
<td><?php echo $person['full_name']; ?></td>
<?php foreach ($team['Division']['League']['StatType'] as $stat): ?>
<td style="<?php echo $style; ?>">&nbsp;</td>
<?php endforeach; ?>
</tr>
<?php
endforeach;
?>

<tr>
	<td><?php __('Unlisted Subs'); ?></td>
<?php foreach ($team['Division']['League']['StatType'] as $stat): ?>
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
