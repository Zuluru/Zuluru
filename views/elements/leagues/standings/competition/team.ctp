<?php
$class = null;
if (count ($classes)) {
	$class = ' class="' . implode (' ', $classes). '"';
}
?>
<tr<?php echo $class;?>>
	<td><?php
	echo $this->element('teams/block', array('team' => $team));
	?></td>
	<td><?php echo $team['rating']; ?></td>
	<?php if (League::hasSpirit($division)): ?>
	<td><?php
	if (!array_key_exists('Season', $team) || $team['Season']['games'] == 0) {
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
