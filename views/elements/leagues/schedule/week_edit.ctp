<?php
if (isset($division)) {
	$games = $division['Game'];
	$competition = ($division['Division']['schedule_type'] == 'competition');
	$double_booking = $division['Division']['double_booking'];
	$id = $division['Division']['id'];
	$id_field = 'division';
	$my_divisions = array();
	$teams = Set::combine ($division['Team'], '{n}.id', '{n}.name');
	natcasesort ($teams);
	$only_some_divisions = false;
} else {
	$games = $league['Game'];
	$schedule_types = array_unique(Set::extract('/Division/schedule_type', $league));
	$competition = (count($schedule_types) == 1 && $schedule_types[0] == 'competition');
	$double_bookings = Set::extract('/Division/double_booking', $league);
	$double_booking = in_array(true, $double_bookings);
	$id = $league['League']['id'];
	$id_field = 'league';

	$my_divisions = $this->UserCache->read('DivisionIDs');
	$teams = array();
	foreach ($league['Division'] as $division) {
		if ($is_admin || $is_manager || in_array($division['id'], $my_divisions)) {
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
	if (count($teams) == 1) {
		$teams = reset($teams);
	}
}

$published = array_unique (Set::extract ("/GameSlot[game_date>={$week[0]}][game_date<={$week[1]}]/../published", $games));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

$dependency_types = array(
	'game_winner' => __('Winner of', true),
	'game_loser' => __('Loser of', true),
);

// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
$finalized = true;
$is_season = $is_tournament = $editing_tournament = $has_dependent_games = false;
$season_divisions = array();
foreach ($games as $game) {
	if ($game['GameSlot']['game_date'] >= $week[0] && $game['GameSlot']['game_date'] <= $week[1]) {
		if ($game['type'] != SEASON_GAME) {
			$is_tournament = true;
			if ($is_admin || $is_manager || in_array($game['division_id'], $my_divisions)) {
				$editing_tournament = true;
			}
		} else {
			$is_season = true;
			$season_divisions[$game['division_id']] = true;
		}
		if ($is_admin || $is_manager || in_array($game['division_id'], $my_divisions)) {
			$finalized &= Game::_is_finalized($game);
			$has_dependent_games |= (!empty($game['HomePoolTeam']['dependency_type']) || !empty($game['AwayPoolTeam']['dependency_type']));
		}
	}
}

$cross_division = (count($season_divisions) > 1);

if ($only_some_divisions || !$is_season) {
	echo $this->element('leagues/schedule/view_header', compact('week', 'competition', 'id_field', 'id', 'published', 'finalized', 'is_tournament', 'multi_day', 'has_dependent_games'));
} else {
	echo $this->element('leagues/schedule/edit_header', compact('week', 'competition', 'id_field', 'id', 'is_tournament', 'multi_day'));
}
?>

<?php if ($editing_tournament): ?>
<tr><td colspan="<?php echo 5 + $multi_day + !$competition; ?>" class="warning-message"><?php echo sprintf(__('For normal usage, it is safest to only change %s values for tournament or playoff games; editing of other values should be reserved for extreme situations', true), sprintf(__('Time/%s', true), __(Configure::read('sport.field_cap'), true))); ?></td></tr>
<?php endif; ?>

<?php if ($this->Session->check('Message.schedule_edit')): ?>
<tr><td colspan="<?php echo 5 + $multi_day + !$competition; ?>"><?php echo $this->Session->flash('schedule_edit'); ?></td></tr>
<?php endif; ?>

<?php
$last_date = $last_slot = null;
foreach ($games as $game):
	if ($game['GameSlot']['game_date'] < $week[0] || $game['GameSlot']['game_date'] > $week[1]) {
		continue;
	}

	Game::_readDependencies($game);
	$same_date = ($game['GameSlot']['game_date'] === $last_date);
	$same_slot = ($game['GameSlot']['id'] === $last_slot);
	if (!$is_admin && !$is_manager && !in_array($game['division_id'], $this->UserCache->read('DivisionIDs'))) {
		if ($game['published']) {
			echo $this->element('leagues/schedule/game_view', compact('game', 'competition', 'is_tournament', 'multi_day', 'same_date', 'same_slot'));
			$last_date = $game['GameSlot']['game_date'];
			$last_slot = $game['GameSlot']['id'];
		}
		continue;
	}
	$last_date = $game['GameSlot']['game_date'];
	$last_slot = $game['GameSlot']['id'];

	if (empty ($this->data)) {
		$data = $game;
	} else {
		$data = reset(Set::extract("/Game[id={$game['id']}]/.", $this->data));
	}
?>

<tr<?php if (!$game['published']) echo ' class="unpublished"'; ?>>
	<td><?php if ($game['type'] != SEASON_GAME): ?><?php
	echo $this->Form->input ("Game.{$game['id']}.name", array(
			'div' => false,
			'label' => false,
			'size' => 5,
			'default' => $data['name'],
	));
	?><?php endif; ?></td>
	<td colspan="<?php echo 2 + $multi_day; ?>"><?php
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
		if ($game['home_dependency_type'] == 'pool') {
			// Get the list of seeds in the pool
			$ids = array();
			foreach ($games as $other_game) {
				if ($other_game['division_id'] == $game['division_id'] && $other_game['type'] != SEASON_GAME && $other_game['round'] == $game['round'] && $other_game['pool_id'] == $game['pool_id']) {
					if (!in_array($other_game['HomePoolTeam']['id'], $ids)) {
						$dependency = Pool::_dependency($other_game['HomePoolTeam']);
						$alias = $other_game['HomePoolTeam']['alias'];
						if (!empty($alias)) {
							$dependency = "$alias [$dependency]";
						}
						$ids[$other_game['HomePoolTeam']['id']] = $dependency;
					}

					if (!in_array($other_game['AwayPoolTeam']['id'], $ids)) {
						$dependency = Pool::_dependency($other_game['AwayPoolTeam']);
						$alias = $other_game['AwayPoolTeam']['alias'];
						if (!empty($alias)) {
							$dependency = "$alias [$dependency]";
						}
						$ids[$other_game['AwayPoolTeam']['id']] = $dependency;
					}
				}
			}

			echo $this->Form->input ("Game.{$game['id']}.home_pool_team_id", array(
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
					'selected' => $data['home_pool_team_id'],
			));
		} else {
			// Get the list of games in the previous round
			$ids = array();
			foreach ($games as $other_game) {
				if ($other_game['division_id'] == $game['division_id'] && $other_game['type'] != SEASON_GAME && $other_game['type'] != POOL_PLAY_GAME && $other_game['round'] == $game['round'] - 1) {
					$ids[$other_game['id']] = $other_game['name'];
				}
			}

			echo $this->Form->input ("Game.{$game['id']}.home_dependency_type", array(
					'div' => false,
					'label' => false,
					'options' => $dependency_types,
					'empty' => '---',
					'selected' => $data['home_dependency_type'],
			));
			echo $this->Form->input ("Game.{$game['id']}.home_dependency_id", array(
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
					'selected' => $data['home_dependency_id'],
			));
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
		if ($game['away_dependency_type'] == 'pool') {
			// Get the list of seeds in the pool
			$ids = array();
			foreach ($games as $other_game) {
				if ($other_game['division_id'] == $game['division_id'] && $other_game['type'] != SEASON_GAME && $other_game['round'] == $game['round'] && $other_game['pool_id'] == $game['pool_id']) {
					if (!in_array($other_game['HomePoolTeam']['id'], $ids)) {
						$dependency = Pool::_dependency($other_game['HomePoolTeam']);
						$alias = $other_game['HomePoolTeam']['alias'];
						if (!empty($alias)) {
							$dependency = "$alias [$dependency]";
						}
						$ids[$other_game['HomePoolTeam']['id']] = $dependency;
					}

					if (!in_array($other_game['AwayPoolTeam']['id'], $ids)) {
						$dependency = Pool::_dependency($other_game['AwayPoolTeam']);
						$alias = $other_game['AwayPoolTeam']['alias'];
						if (!empty($alias)) {
							$dependency = "$alias [$dependency]";
						}
						$ids[$other_game['AwayPoolTeam']['id']] = $dependency;
					}
				}
			}

			echo $this->Form->input ("Game.{$game['id']}.away_pool_team_id", array(
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
					'selected' => $data['away_pool_team_id'],
			));
		} else {
			// Get the list of games in the previous round
			$ids = array();
			foreach ($games as $other_game) {
				if ($other_game['division_id'] == $game['division_id'] && $other_game['type'] != SEASON_GAME && $other_game['type'] != POOL_PLAY_GAME && $other_game['round'] == $game['round'] - 1) {
					$ids[$other_game['id']] = $other_game['name'];
				}
			}

			echo $this->Form->input ("Game.{$game['id']}.away_dependency_type", array(
					'div' => false,
					'label' => false,
					'options' => $dependency_types,
					'empty' => '---',
					'selected' => $data['away_dependency_type'],
			));
			echo $this->Form->input ("Game.{$game['id']}.away_dependency_id", array(
					'div' => false,
					'label' => false,
					'options' => $ids,
					'empty' => '---',
					'selected' => $data['away_dependency_id'],
			));
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
	<td colspan="<?php echo 3 + $multi_day + !$competition; ?>"><?php
	echo $this->Form->input ('publish', array(
			'label' => __('Set as published for player viewing?', true),
			'type' => 'checkbox',
			'checked' => $published,
	));
	if ($is_season) {
		echo $this->Form->input ('double_header', array(
				'label' => __('Allow double-headers?', true),
				'type' => 'checkbox',
				'checked' => false,
		));
	}
	if ($multi_day) {
		echo $this->Form->input ('multiple_days', array(
				'label' => __('Allow teams to be booked on more than one day?', true),
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
		<?php echo $this->Form->hidden ('edit_date', array('value' => $week[0])); ?>
		<?php echo $this->Form->submit (__('Reset', true), array('type' => 'reset', 'div' => false)); ?>
		<?php echo $this->Form->submit (__('Submit', true), array('div' => false)); ?>
	</td>
</tr>
