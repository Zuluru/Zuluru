<?php
$classes[] = 'center';
$cols = 11 + League::hasSpirit($division);
if ($division['Division']['current_round'] != 1) {
	$cols += 8;
}
?>
<tr>
	<td colspan="<?php echo $cols; ?>" class="<?php echo implode (' ', $classes); ?>"><?php echo $this->Html->link('... ... ...', array('action' => 'standings', 'division' => $division['Division']['id'], 'team' => $teamid, 'full' => 1)); ?></td>
</tr>
