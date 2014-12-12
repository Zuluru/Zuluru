<?php if (!empty ($divisions)) : ?>
<table class="list">
<tr>
	<th colspan="2"><?php __('Divisions Coordinated');?></th>
</tr>
<?php
$i = 0;
$coordinated_divisions = $this->UserCache->read('DivisionIDs');
foreach ($divisions as $division):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}

	$is_coordinator = in_array($division['Division']['id'], $coordinated_divisions);
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $this->element('divisions/block', array('division' => $division['Division'], 'field' => 'long_league_name')); ?></td>
		<td class="actions splash_action"><?php echo $this->element('divisions/actions', array('league' => $division['League'], 'division' => $division['Division'], 'is_coordinator' => $is_coordinator)); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php
endif;
?>