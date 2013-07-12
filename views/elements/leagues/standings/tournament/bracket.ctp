<?php
AppModel::_reindexOuter($games, 'Game', 'id');
ksort($games);
AppModel::_reindexOuter($teams, 'Team', 'id');

$init_pools = array();

while (!empty($games)):
	$bracket = Game::_extractBracket($games);
	ksort($bracket);
	// For the class names to format this correctly, we need the rounds in
	// this bracket to be numbered from 0, regardless of what their real
	// round number is.
	$bracket = array_values($bracket);

	if (!empty($bracket[0][0]) && !in_array($bracket[0][0]['pool_id'], $init_pools) && ($is_admin || $is_manager || $is_coordinator)) {
		$init_pools[] = $bracket[0][0]['pool_id'];
		echo $this->ZuluruHtml->iconLink('delete_24.png',
			array('controller' => 'schedules', 'action' => 'delete', 'division' => $division['Division']['id'], 'pool' => $bracket[0][0]['pool_id'], 'return' => true),
			array('alt' => __('Delete', true), 'title' => __('Delete pool games', true)));
		echo $this->ZuluruHtml->iconLink('initialize_24.png',
			array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'pool' => $bracket[0][0]['pool_id'], 'return' => true),
			array('alt' => __('Initialize', true), 'title' => __('Initialize schedule dependencies', true)));
		echo $this->ZuluruHtml->iconLink('reset_24.png',
			array('action' => 'initialize_dependencies', 'division' => $division['Division']['id'], 'pool' => $bracket[0][0]['pool_id'], 'reset' => true, 'return' => true),
			array('alt' => __('Reset', true), 'title' => __('Reset schedule dependencies', true)));
	}
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
			if ($game['home_score'] > $game['away_score']) {
				echo $this->element('teams/block', array('team' => $teams[$game['home_team']], 'options' => array('max_length' => 16)));
			} else {
				echo $this->element('teams/block', array('team' => $teams[$game['away_team']], 'options' => array('max_length' => 16)));
			}
		}
		?>
		</div>
	</div>

</div>
<?php endwhile; ?>

</div>
