<?php
if (isset($division)) {
	$games = $division['Game'];
	$competition = ($division['Division']['schedule_type'] == 'competition');
	$id = $division['Division']['id'];
	$id_field = 'division';
} else {
	$games = $league['Game'];
	$schedule_types = array_unique(Set::extract('/Division/schedule_type', $league));
	$competition = (count($schedule_types) == 1 && $schedule_types[0] == 'competition');
	$id = $league['League']['id'];
	$id_field = 'league';
}
$published = array_unique (Set::extract ("/GameSlot[game_date=$date]/../published", $games));
if (count ($published) != 1 || $published[0] == 0) {
	$published = false;
} else {
	$published = true;
}

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
?>

<?php
$last_slot = null;
foreach ($games as $game):
	if (! ($game['published'] || $is_admin || $is_manager || $is_coordinator)) {
		continue;
	}
	if ($date != $game['GameSlot']['game_date']) {
		continue;
	}
	Game::_readDependencies($game);
	$same_slot = ($game['GameSlot']['id'] === $last_slot);
	echo $this->element('leagues/schedule/game_view', compact('game', 'competition', 'is_tournament', 'same_slot'));
	$last_slot = $game['GameSlot']['id'];
endforeach;
?>
