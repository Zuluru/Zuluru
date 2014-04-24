<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($is_tournament && !$same_slot): ?><?php echo $game['name']; ?><?php endif; ?></td>
	<?php if ($multi_day): ?>
	<td><?php
	if (!$same_date) {
		echo $this->ZuluruTime->day($game['GameSlot']['game_date']);
	}
	?></td>
	<?php endif; ?>
	<td><?php
	if (!$same_slot) {
		$time = $this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
				$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
		echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['id']));
	}
	?></td>
	<td><?php if (!$same_slot) echo $this->element('fields/block', array('field' => $game['GameSlot']['Field'])); ?></td>
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
	<?php if (!$competition): ?>
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
	<?php endif; ?>
	<td class="actions"><?php
	if (isset($division)) {
		echo $this->ZuluruGame->displayScore ($game, $division['Division'], $division['League']);
	} else {
		echo $this->ZuluruGame->displayScore ($game, $game['Division'], $league['League']);
	}
	?></td>
</tr>
