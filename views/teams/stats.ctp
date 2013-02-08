<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb ($team['Team']['name']);
$this->Html->addCrumb (__('Stats', true));
?>

<div class="teams stats">
<h2><?php echo $team['Team']['name'];?></h2>
</div>
<div class="actions">
	<?php echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'format' => 'list')); ?>
</div>

<div class="related">
	<table class="list tablesorter">
	<thead>
	<tr>
		<th><?php __('Name'); ?></th>
		<th><?php __('Gender'); ?></th>
<?php foreach ($team['Division']['League']['StatType'] as $stat_type): ?>
		<th class="<?php echo $stat_type['class']; ?>"><?php echo $this->Html->tag('span', __($stat_type['abbr'], true), array('title' => $stat_type['name'])); ?></th>
<?php endforeach; ?>
	</tr>
	</thead>
	<tbody>
	<?php
		$totals = array_fill_keys(Set::extract('/Division/League/StatType[type=season_total]/id', $team), 0);
		foreach ($team['Person'] as $person):
	?>
	<tr>
		<td><?php echo $this->element('people/block', compact('person')); ?></td>
		<td><?php __($person['gender']); ?></td>
<?php foreach ($team['Division']['League']['StatType'] as $stat_type): ?>
		<td class="<?php echo $stat_type['class']; ?>"><?php
			if (!empty($team['Calculated'][$person['id']][$stat_type['id']])) {
				echo $team['Calculated'][$person['id']][$stat_type['id']];
				if (array_key_exists($stat_type['id'], $totals)) {
					$totals[$stat_type['id']] += $team['Calculated'][$person['id']][$stat_type['id']];
				}
			} else {
				if ($stat_type['type'] == 'season_calc') {
					__('N/A');
				} else {
					echo 0;
				}
			}
		?></td>
<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
	</tbody>
	<tfoot>
	<tr>
		<td><?php __('Total'); ?></td>
		<td></td>
<?php foreach ($team['Division']['League']['StatType'] as $stat_type): ?>
		<td><?php
			if (array_key_exists($stat_type['id'], $totals)) {
				if (!empty($totals[$stat_type['id']])) {
					echo $totals[$stat_type['id']];
				} else {
					echo 0;
				}
			}
		?></td>
<?php endforeach; ?>
	</tr>
	</tfoot>
</table>

</div>

<?php
// Make the table sortable
$this->ZuluruHtml->script (array('jquery.tablesorter.min'), array('inline' => false));
$this->ZuluruHtml->css('jquery.tablesorter', null, array('inline' => false));
$this->Js->buffer ("
	jQuery('.tablesorter').tablesorter({sortInitialOrder: 'desc'});
");
?>
