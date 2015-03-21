<?php
$classes[] = 'center';
$cols = 11 + League::hasSpirit($league) + League::hasCarbonFlip($league) * 4;
?>
<tr>
	<td colspan="<?php echo $cols; ?>" class="<?php echo implode (' ', $classes); ?>"><?php echo $this->Html->link('... ... ...', array('action' => 'standings', 'division' => $division['id'], 'team' => $teamid, 'full' => 1)); ?></td>
</tr>
