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
	<td><?php echo $team['results']['W']; ?></td>
	<td><?php echo $team['results']['L']; ?></td>
	<td><?php echo $team['results']['T']; ?></td>
	<td><?php echo $team['results']['def']; ?></td>
	<td><?php echo $team['results']['gf']; ?></td>
	<td><?php echo $team['results']['ga']; ?></td>
	<td><?php echo $team['results']['gf'] - $team['results']['ga']; ?></td>
	<td><?php
	if ($team['results']['str'] > 1) {
		echo $team['results']['str'] . __($team['results']['str_type'], true);
	} else {
		echo '-';
	}
	?></td>
	<?php if ($division['League']['numeric_sotg'] || $division['League']['sotg_questions'] != 'none'): ?>
	<td><?php
	if ($team['results']['games'] == 0) {
		$spirit = null;
	} else {
		$spirit = $team['results']['spirit'] / $team['results']['games'];
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
