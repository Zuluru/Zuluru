<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Scores', true));
?>

<div class="divisions scores">
<h2><?php echo __('Division Scores', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<?php
// We need a list of all of the teams that have participated in games, as some may have moved
$all_teams = $division['Team'];

// Rearrange game results into a nice array we can just dump out
$games = array();
foreach ($division['Game'] as $game) {
	if (Game::_is_finalized ($game) && $game['Game']['status'] != 'rescheduled') {
		$home = $game['Game']['home_team'];
		$away = $game['Game']['away_team'];

		// Add the game to the home team's list
		if (!array_key_exists ($home, $games)) {
			$games[$home] = array();
		}
		if (!array_key_exists ($away, $games[$home])) {
			$games[$home][$away] = array();
		}
		$games[$home][$away][] = $game;

		// Add the game to the away team's list
		if (!array_key_exists ($away, $games)) {
			$games[$away] = array();
		}
		if (!array_key_exists ($home, $games[$away])) {
			$games[$away][$home] = array();
		}
		$games[$away][$home][] = $game;

		// Make sure both teams are in the all_teams list
		if (!array_key_exists ($home, $all_teams)) {
			$all_teams[$home] = $game['HomeTeam'];
		}
		if (!array_key_exists ($away, $all_teams)) {
			$all_teams[$away] = $game['AwayTeam'];
		}
	}
}
$header = array(null);
foreach ($all_teams as $team_id => $team) {
	$header[] = $this->element('teams/block', array('team' => $team, 'max_length' => 16, 'show_shirt' => false));
}
$header[] = null;

?>

<table class="list">
<thead>
<?php echo $this->Html->tableHeaders ($header); ?>
</thead>
<tbody>
<?php
$rows = array();
// Down the left side, we only list teams currently in the division
foreach ($division['Team'] as $team_id => $team) {
	$link = $this->Html->link ($team['name'], array('controller' => 'teams', 'action' => 'schedule', 'team' => $team_id));
	$row = array($link);
	// In each row, we want all teams included
	foreach ($all_teams as $opp_id => $opp) {
		if ($team_id == $opp_id) {
			$row[] = array('N/A', array('style' => 'color: gray;'));
		} else if (array_key_exists ($team_id, $games) && array_key_exists ($opp_id, $games[$team_id])) {
			$results = array();
			$wins = $losses = 0;
			foreach ($games[$team_id][$opp_id] as $game) {
				switch($game['Game']['status']) {
					case 'home_default':
						$game_score = '(default)';
						$game_result = "{$game['HomeTeam']['name']} defaulted";
						break;
					case 'away_default':
						$game_score = '(default)';
						$game_result = "{$game['AwayTeam']['name']} defaulted";
						break;
					case 'forfeit':
						$game_score = '(forfeit)';
						$game_result = 'forfeit';
						break;
					default: //normal finalized game
						if($game['Game']['home_team'] == $team_id) {
							$game_score = "{$game['Game']['home_score']}-{$game['Game']['away_score']}";
							if ($game['Game']['home_score'] > $game['Game']['away_score']) {
								$wins++;
							} else if ($game['Game']['home_score'] < $game['Game']['away_score']) {
								$losses++;
							}
						} else {
							$game_score = "{$game['Game']['away_score']}-{$game['Game']['home_score']}";
							if ($game['Game']['away_score'] > $game['Game']['home_score']) {
								$wins++;
							} else if ($game['Game']['away_score'] < $game['Game']['home_score']) {
								$losses++;
							}
						}
						if ($game['Game']['home_score'] > $game['Game']['away_score']) {
							$game_result = "{$game['HomeTeam']['name']} defeated {$game['AwayTeam']['name']} {$game['Game']['home_score']}-{$game['Game']['away_score']}";
						} else if ($game['Game']['home_score'] < $game['Game']['away_score']) {
							$game_result = "{$game['AwayTeam']['name']} defeated {$game['HomeTeam']['name']} {$game['Game']['away_score']}-{$game['Game']['home_score']}";
						} else {
							$game_result = "{$game['HomeTeam']['name']} and {$game['AwayTeam']['name']} tied $game_score";
						}
						$game_result .= " ({$game['Game']['rating_points']} rating points transferred)";
				}

				$popup = $this->ZuluruTime->date ($game['GameSlot']['game_date']) . " at {$game['GameSlot']['Field']['long_code']}: $game_result";

				$results[] = $this->Html->link($game_score, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), array('title' => $popup));
			}
			$cell = implode ('<br />', $results);
			if ($wins > $losses) {
				$row[] = array($cell, array('class'=>'winning'));
			} else if ($wins < $losses) {
				$row[] = array($cell, array('class'=>'losing'));
			} else {
				$row[] = $cell;
			}
		} else {
			$row[] = null;
		}
	}
	$row[] = $link;
	$rows[] = $row;
}

echo $this->Html->tableCells ($rows, array(), array('class' => 'altrow'));
?>

</tbody>
<thead>
<?php echo $this->Html->tableHeaders ($header); ?>
</thead>
</table>

<p>Scores are listed with the first score belonging the team whose name appears on the left.
<br />Green backgrounds means row team is winning season series, red means column team is winning series. Defaulted games are not counted.</p>

</div>
