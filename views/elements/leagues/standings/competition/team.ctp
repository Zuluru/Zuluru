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
	<?php if (League::hasSpirit($league)): ?>
	<td><?php
	if (!array_key_exists('Season', $team) || $team['Season']['spirit_games'] == 0) {
		$spirit = null;
	} else {
		$spirit = $team['Season']['spirit'] / $team['Season']['spirit_games'];
	}
	echo $this->element ('spirit/symbol', array(
			'spirit_obj' => $spirit_obj,
			'league' => $league,
			'is_coordinator' => $is_coordinator,
			'value' => $spirit,
	));
	?></td>
	<?php endif; ?>
</tr>
