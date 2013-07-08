<?php
$class = null;
if (count ($classes)) {
	$class = ' class="' . implode (' ', $classes). '"';
}
?>
<tr<?php echo $class;?>>
	<td><?php echo $seed; ?></td>
	<td><?php
	echo $this->element('teams/block', array('team' => $team));
	?></td>
	<td><?php echo $team['rating']; ?></td>
	<td><?php echo $team['Season']['W']; ?></td>
	<td><?php echo $team['Season']['L']; ?></td>
	<td><?php echo $team['Season']['T']; ?></td>
	<td><?php echo $team['Season']['def']; ?></td>
	<td><?php echo $team['Season']['gf']; ?></td>
	<td><?php echo $team['Season']['ga']; ?></td>
	<td><?php echo $team['Season']['gf'] - $team['Season']['ga']; ?></td>
	<td><?php
	if ($team['Season']['str'] > 1) {
		echo $team['Season']['str'] . __($team['Season']['str_type'], true);
	} else {
		echo '-';
	}
	?></td>
	<?php if (League::hasSpirit($division)): ?>
	<td><?php
	if ($team['Season']['games'] == 0) {
		$spirit = null;
	} else {
		$spirit = $team['Season']['spirit'] / $team['Season']['games'];
	}
	echo $this->element ('spirit/symbol', array(
			'spirit_obj' => $spirit_obj,
			'league' => $division['League'],
			'is_coordinator' => $is_coordinator,
			'value' => $spirit,
	));
	?></td>
	<?php endif; ?>
</tr>
