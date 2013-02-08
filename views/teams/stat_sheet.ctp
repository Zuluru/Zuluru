<?php
$style = 'width:' . floor(80 / count($team['Division']['League']['StatType'])) . '%;';
?>

<div class="stat_sheet">
<h2><?php __('Stat Entry Sheet'); ?></h2>
<?php // Seems that dompdf doesn't deal well with DLs that use floats ?>
<table>
	<tr>
		<td>Date &amp; time:</td>
		<td></td>
	</tr>
	<tr>
		<td>Team:</td>
		<td><?php echo $team['Team']['name']; ?></td>
	</tr>
	<tr>
		<td>Opponent:</td>
		<td></td>
	</tr>
	<tr>
		<td>Location:</td>
		<td></td>
	</tr>
	<tr>
		<td>Final score:</td>
		<td><?php echo $team['Team']['name']; ?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Opponent: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<?php // TODO: Make these sport-independent ?>
		<td>Timeouts taken:</td>
		<td><?php echo $team['Team']['name']; ?>: [&nbsp;] [&nbsp;] [&nbsp;]  Opponent: [&nbsp;] [&nbsp;] [&nbsp;]</td>
	</tr>
	<tr>
		<td>Initial pull:</td>
		<td><?php echo $team['Team']['name']; ?>: [&nbsp;]  Opponent: [&nbsp;]  From:</td>
	</tr>
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
