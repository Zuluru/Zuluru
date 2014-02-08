<?php
if (isset($division)) {
	$games = $division['Game'];
	$competition = ($division['Division']['schedule_type'] == 'competition');
	$double_booking = $division['Division']['double_booking'];
	$id = $division['Division']['id'];
	$id_field = 'division';
	$teams = Set::combine ($division['Team'], '{n}.id', '{n}.name');
	natcasesort ($teams);
	$cross_division = false;
	$only_some_divisions = false;
} else {
	$games = $league['Game'];
	$schedule_types = array_unique(Set::extract('/Division/schedule_type', $league));
	$competition = (count($schedule_types) == 1 && $schedule_types[0] == 'competition');
	$double_bookings = Set::extract('/Division/double_booking', $league);
	$double_booking = in_array(true, $double_bookings);
	$id = $league['League']['id'];
	$id_field = 'league';

	$teams = array();
	foreach ($league['Division'] as $division) {
		if ($is_admin || $is_manager || in_array($division['id'], $this->UserCache->read('DivisionIDs'))) {
			$division_teams = array_merge(
					Set::extract ("/Game/HomeTeam[division_id={$division['id']}]/.", $league),
					Set::extract ("/Game/AwayTeam[division_id={$division['id']}]/.", $league));
			$teams[$division['name']] = Set::combine ($division_teams, '{n}.id', '{n}.name');
			// Games with unresolved dependencies have a null team ID
			unset($teams[$division['name']][null]);
			natcasesort ($teams[$division['name']]);
		}
	}
	$only_some_divisions = (count($league['Division']) != count($teams));
	if (count($teams) > 1) {
		$cross_division = true;
	} else {
		$teams = reset($teams);
		$cross_division = false;
	}
}
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $games));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

if ($only_some_divisions) {
	// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
	$finalized = true;
	$is_tournament = $has_dependent_games = false;
	foreach ($games as $game) {
		if ($date == $game['GameSlot']['game_date']) {
			$finalized &= Game::_is_finalized($game);
			$is_tournament |= ($game['type'] != SEASON_GAME);
			$has_dependent_games |= (!empty($game['HomePoolTeam']['dependency_type']) || !empty($game['AwayPoolTeam']['dependency_type']));
		}
	}

	echo $this->element('leagues/schedule/view_header', compact('date', 'competition', 'id_field', 'id', 'published', 'finalized', 'is_tournament', 'has_dependent_games'));
} else {
	echo $this->element('leagues/schedule/edit_header', compact('date', 'competition', 'id_field', 'id', 'is_tournament'));
}
?>

<?php
$last_slot = null;
foreach ($games as $game):
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}

	Game::_readDependencies($game);
	$same_slot = ($game['GameSlot']['id'] === $last_slot);
	if (!$is_admin && !$is_manager && !in_array($game['division_id'], $this->UserCache->read('DivisionIDs'))) {
		if ($game['published']) {
			echo $this->element('leagues/schedule/game_view', compact('game', 'competition', 'is_tournament', 'same_slot'));
		}
		continue;
	}
	$last_slot = $game['GameSlot']['id'];

	if (empty ($this->data)) {
		$data = $game;
	} else {
		$data = reset(Set::extract("/Game[id={$game['id']}]/.", $this->data));
	}
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($game['type'] != SEASON_GAME): ?><?php
	if (!empty($data['name'])) {
		echo $data['name'];
	}
	?><?php endif; ?></td>
	<td colspan="2"><?php
	echo $this->Form->hidden ("Game.{$game['id']}.id", array('value' => $game['id']));
	echo $this->Form->input ("Game.{$game['id']}.game_slot_id", array(
			'div' => false,
			'label' => false,
			'options' => $slots,
			'empty' => '---',
			'selected' => $data['game_slot_id'],
	));
	?></td>
	<td><?php
	if ($game['type'] != SEASON_GAME) {
		if (empty ($game['HomeTeam'])) {
			if (array_key_exists ('home_dependency', $game)) {
				echo $game['home_dependency'];
			} else {
				__('Unassigned');
			}
		} else {
			echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16)));
		}
	} else {
		echo $this->Form->input ("Game.{$game['id']}.home_team", array(
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
				'selected' => $data['home_team'],
		));
	}
	?></td>
	<?php if (!$competition): ?>
	<td><?php
	if ($game['type'] != SEASON_GAME) {
		if (empty ($game['AwayTeam'])) {
			if (array_key_exists ('away_dependency', $game)) {
				echo $game['away_dependency'];
			} else {
				__('Unassigned');
			}
		} else {
			echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16)));
		}
	} else {
		echo $this->Form->input ("Game.{$game['id']}.away_team", array(
				'div' => false,
				'label' => false,
				'options' => $teams,
				'empty' => '---',
				'selected' => $data['away_team'],
		));
	}
	?></td>
	<?php endif; ?>
	<td></td>
</tr>

<?php
endforeach;
?>

<tr>
	<td colspan="<?php echo 3 + !$competition; ?>"><?php
	echo $this->Form->input ('publish', array(
			'label' => __('Set as published for player viewing?', true),
			'type' => 'checkbox',
			'checked' => $published,
	));
	if (!$is_tournament) {
		echo $this->Form->input ('double_header', array(
				'label' => __('Allow double-headers?', true),
				'type' => 'checkbox',
				'checked' => false,
		));
	}
	if ($double_booking) {
		echo $this->Form->input ('double_booking', array(
				'label' => __('Allow double-booking?', true),
				'type' => 'checkbox',
				'checked' => true,
		));
	}
	if ($cross_division) {
		echo $this->Form->input ('cross_division', array(
				'label' => __('Allow cross-division games?', true),
				'type' => 'checkbox',
				'checked' => false,
		));
	}
	?></td>
	<td colspan="2" class="actions splash_action">
		<?php echo $this->Form->hidden ('edit_date', array('value' => $date)); ?>
		<?php echo $this->Form->submit (__('Reset', true), array('type' => 'reset', 'div' => false)); ?>
		<?php echo $this->Form->submit (__('Submit', true), array('div' => false)); ?>
	</td>
</tr>
