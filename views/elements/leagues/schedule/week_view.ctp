<?php
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $division['Game']));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
$finalized = true;
$is_tournament = $has_dependent_games = false;
foreach ($division['Game'] as $game) {
	if ($date == $game['GameSlot']['game_date']) {
		$finalized &= Game::_is_finalized($game);
		$is_tournament |= ($game['type'] != SEASON_GAME);
		$has_dependent_games |= (!empty($game['HomePoolTeam']['dependency_type']) || !empty($game['AwaayPoolTeam']['dependency_type']));
	}
}
?>

<tr>
	<th colspan="3"><a name="<?php echo $date; ?>"><?php echo $this->ZuluruTime->fulldate($date); ?></a></th>
	<th colspan="3" class="actions splash_action"><?php
	if (!$finalized && ($is_admin || $is_manager || $is_coordinator)):
	?>
		<?php
		if ($has_dependent_games) {
			echo $this->ZuluruHtml->iconLink('initialize_24.png',
				array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'date' => $date),
				array('alt' => __('Initialize', true), 'title' => __('Initialize schedule dependencies', true)));
			echo $this->ZuluruHtml->iconLink('reset_24.png',
				array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'date' => $date, 'reset' => true),
				array('alt' => __('Reset', true), 'title' => __('Reset schedule dependencies', true)));
		}
		?>
		<?php echo $this->ZuluruHtml->iconLink('field_24.png',
				array('action' => 'slots', 'division' => $division['Division']['id'], 'date' => $date),
				array('alt' => __(Configure::read('sport.fields_cap'), true), 'title' => sprintf(__('Available %s', true), __(Configure::read('sport.fields_cap'), true)))); ?>
		<?php echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['Division']['id'], 'edit_date' => $date, '#' => $date),
				array('alt' => __('Edit Day', true), 'title' => __('Edit Day', true))); ?>
		<?php echo $this->ZuluruHtml->iconLink('delete_24.png',
				array('controller' => 'schedules', 'action' => 'delete', 'division' => $division['Division']['id'], 'date' => $date),
				array('alt' => __('Delete Day', true), 'title' => __('Delete Day', true))); ?>
		<?php echo $this->ZuluruHtml->iconLink('reschedule_24.png',
				array('controller' => 'schedules', 'action' => 'reschedule', 'division' => $division['Division']['id'], 'date' => $date),
				array('alt' => __('Reschedule', true), 'title' => __('Reschedule', true))); ?>
		<?php
		if ($published) {
			echo $this->ZuluruHtml->iconLink('unpublish_24.png',
					array('controller' => 'schedules', 'action' => 'unpublish', 'division' => $division['Division']['id'], 'date' => $date),
					array('alt' => __('Unpublish', true), 'title' => __('Unpublish', true)));
		} else {
			echo $this->ZuluruHtml->iconLink('publish_24.png',
					array('controller' => 'schedules', 'action' => 'publish', 'division' => $division['Division']['id'], 'date' => $date),
					array('alt' => __('Publish', true), 'title' => __('Publish', true)));
		}
		?>
	<?php
	else:
		echo '&nbsp;';
	endif;
	?></th>
</tr>
<tr>
	<th><?php if ($is_tournament): ?><?php __('Game'); ?><?php endif; ?></th>
	<th><?php __('Time'); ?></th>
	<th><?php __(Configure::read('sport.field_cap')); ?></th>
	<th><?php __('Home'); ?></th>
	<th><?php __('Away'); ?></th>
	<th><?php __('Score'); ?></th>
</tr>

<?php
foreach ($division['Game'] as $game):
	if (! ($game['published'] || $is_admin || $is_manager || $is_coordinator)) {
		continue;
	}
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
	Game::_readDependencies($game);
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($is_tournament): ?><?php echo $game['name']; ?><?php endif; ?></td>
	<td><?php
	$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
			$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
	echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['id']));
	?></td>
	<td><?php echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'])); ?></td>
	<td><?php
	if (empty ($game['HomeTeam'])) {
		if (array_key_exists ('home_dependency', $game)) {
			echo $game['home_dependency'];
		} else {
			__('Unassigned');
		}
	} else {
		echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td><?php
	if (empty ($game['AwayTeam'])) {
		if (array_key_exists ('away_dependency', $game)) {
			echo $game['away_dependency'];
		} else {
			__('Unassigned');
		}
	} else {
		echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16)));
	}
	?></td>
	<td class="actions"><?php echo $this->ZuluruGame->displayScore ($game, $division['League']); ?></td>
</tr>

<?php
endforeach;
?>
