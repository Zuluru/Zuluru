<?php
$class = null;
if (count ($classes)) {
	$class = ' class="' . implode (' ', $classes). '"';
}
?>
<tr<?php echo $class;?>>
	<?php if ($is_admin || $is_manager || $is_coordinator): ?>
	<td><?php echo $team['initial_seed'] ? $team['initial_seed'] : __('TBD', true); ?></td>
	<?php endif; ?>
	<td><?php
	echo $this->element('teams/block', array('team' => $team));
	?></td>
	<?php if ($division['is_playoff']): ?>
	<td><?php if (!empty($team['affiliate_division'])) echo $team['affiliate_division']; ?></td>
	<?php endif; ?>
	<?php if ($is_logged_in): ?>
	<td><?php
	$roster_required = Configure::read("sport.roster_requirements.{$division['ratio']}");
	$count = $team['roster_count'];
	if (($is_admin || $is_manager || $is_coordinator) && $team['roster_count'] < $roster_required && $division['roster_deadline'] !== null) {
		echo $this->Html->tag ('span', $count, array('class' => 'warning-message'));
	} else {
		echo $count;
	}
	?></td>
	<?php if (Configure::read('profile.skill_level')): ?>
	<td><?php echo $team['average_skill']; ?></td>
	<?php endif; ?>
	<?php endif; ?>
	<td class="actions">
	<?php echo $this->element('teams/actions', compact('team', 'division', 'league', 'is_manager')); ?>
	</td>
</tr>
