<?php
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $league['Game']));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
$finalized = true;
foreach ($league['Game'] as $game) {
	if ($date == $game['GameSlot']['game_date']) {
		$finalized &= Game::_is_finalized($game);
	}
}
?>

<tr>
	<th colspan="2"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
	<th colspan="3" class="actions splash_action"><?php
	if (!$finalized && ($is_admin || $is_coordinator)):
	?>
		<?php echo $this->Html->link(__('Fields', true), array('action' => 'slots', 'league' => $league['League']['id'], 'date' => $date)); ?>
		<?php echo $this->Html->link(__('Edit Day', true), array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league['League']['id'], 'edit_date' => $date, '#' => $date)); ?>
		<?php echo $this->Html->link(__('Delete Day', true), array('controller' => 'schedules', 'action' => 'delete', 'league' => $league['League']['id'], 'date' => $date)); ?>
		<?php echo $this->Html->link(__('Reschedule', true), array('controller' => 'schedules', 'action' => 'reschedule', 'league' => $league['League']['id'], 'date' => $date)); ?>
		<?php
		if ($published) {
			echo $this->Html->link(__('Unpublish', true), array('controller' => 'schedules', 'action' => 'unpublish', 'league' => $league['League']['id'], 'date' => $date));
		} else {
			echo $this->Html->link(__('Publish', true), array('controller' => 'schedules', 'action' => 'publish', 'league' => $league['League']['id'], 'date' => $date));
		}
		?>
	<?php
	else:
		echo '&nbsp;';
	endif;
	?></th>
</tr>
<tr>
	<th><?php __('Time'); ?></th>
	<th><?php __('Field'); ?></th>
	<th><?php __('Home'); ?></th>
	<th><?php __('Away'); ?></th>
	<th><?php __('Score'); ?></th>
</tr>

<?php
foreach ($league['Game'] as $game):
	if (! ($game['published'] || $is_admin || $is_coordinator)) {
		continue;
	}
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php
	$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
	echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['id']));
	?></td>
	<td><?php echo $this->Html->link("{$game['GameSlot']['Field']['code']} {$game['GameSlot']['Field']['num']}",
			array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']),
			array('title' => "{$game['GameSlot']['Field']['name']} {$game['GameSlot']['Field']['num']}")); ?></td>
	<td><?php
	if (empty ($game['HomeTeam'])) {
		__('Unassigned');
	} else {
		echo $this->element('team/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td><?php
	if (empty ($game['AwayTeam'])) {
		__('Unassigned');
	} else {
		echo $this->element('team/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td class="actions"><?php echo $this->ZuluruGame->displayScore ($game); ?></td>
</tr>

<?php
endforeach;
?>
