<?php
AppModel::_reindexOuter($games, 'Game', 'id');
ksort($games);
AppModel::_reindexOuter($teams, 'Team', 'id');

while (!empty($games)):
	$bracket = Game::_extractBracket($games);
	ksort($bracket);
	// For the class names to format this correctly, we need the rounds in
	// this bracket to be numbered from 0, regardless of what their real
	// round number is.
	$bracket = array_values($bracket);
?>
<div class="bracket rounds<?php echo count($bracket); ?>">
<?php foreach ($bracket as $round => $round_games): ?>
	<div class="round round<?php echo count($bracket) - $round; ?>">
<?php
		foreach ($round_games as $game) {
			echo $this->element('leagues/standings/tournament/bracket_game', compact('game', 'teams'));
		}
?>

	</div>
<?php endforeach; ?>
	<div class="round round0">
		<div class="winner">
		<?php
		// Whatever game we have here will be the final one in this bracket
		if (Game::_is_finalized($game)) {
			if ($game['Game']['home_score'] > $game['Game']['away_score']) {
				echo $this->element('teams/block', array('team' => $teams[$game['Game']['home_team']], 'options' => array('max_length' => 16)));
			} else {
				echo $this->element('teams/block', array('team' => $teams[$game['Game']['away_team']], 'options' => array('max_length' => 16)));
			}
		}
		?>
		</div>
	</div>

</div>
<?php endwhile; ?>

</div>
