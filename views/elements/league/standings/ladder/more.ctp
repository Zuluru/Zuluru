<?php
$classes[] = 'center';
?>
<tr>
	<td colspan="12" class="<?php echo implode (' ', $classes); ?>"><?php echo $this->Html->link('... ... ...', array('action' => 'standings', 'league' => $league['League']['id'], 'team' => $teamid, 'full' => 1)); ?></td>
</tr>
