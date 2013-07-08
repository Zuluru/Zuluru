<?php
$this->Html->addCrumb (__('Divisions', true));
$this->Html->addCrumb ($division['Division']['full_league_name']);
$this->Html->addCrumb (__('Approve Scores', true));
?>

<div class="divisions approve_scores">
<h2><?php echo __('Approve Scores', true) . ': ' . $division['Division']['full_league_name'];?></h2>

<table class="list">
	<thead>
		<tr>
			<th>Game Date</th>
			<th colspan="2">Home Team Submission</th>
			<th colspan="2">Away Team Submission</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($games as $game):
	Game::_readDependencies($game);

	if (array_key_exists ($game['Game']['home_team'], $game['ScoreEntry'])) {
		$home = $game['ScoreEntry'][$game['Game']['home_team']];
	} else {
		$home = array(
			'score_for' => __('not entered', true),
			'score_against' => __('not entered', true),
		);
	}

	if (array_key_exists ($game['Game']['away_team'], $game['ScoreEntry'])) {
		$away = $game['ScoreEntry'][$game['Game']['away_team']];
	} else {
		$away = array(
			'score_for' => __('not entered', true),
			'score_against' => __('not entered', true),
		);
	}
?>
		<tr>
			<td rowspan="3"><?php echo $this->ZuluruTime->day($game['GameSlot']['game_date']) . ', ' .
					$this->ZuluruTime->time($game['GameSlot']['game_start']); ?></td>
			<td colspan="2"><?php
			if ($game['Game']['home_team'] === null) {
				echo $game['Game']['home_dependency'];
			} else {
				echo $game['HomeTeam']['name'];
			}
			?></td>
			<td colspan="2"><?php
			if ($game['Game']['away_team'] === null) {
				echo $game['Game']['away_dependency'];
			} else {
				echo $game['AwayTeam']['name'];
			}
			?></td>
			<td><?php echo $this->Html->link(__('approve score', true),
					array('controller' => 'games', 'action' => 'edit', 'game' => $game['Game']['id'], 'return' => true)); ?></td>
		</tr>
		<tr>
			<td><?php __('Home score'); ?>:</td>
			<td><?php echo $home['score_for']; ?></td>
			<td><?php __('Home score'); ?>:</td>
			<td><?php echo $away['score_against']; ?></td>
			<td><?php
			// Tournament games may not have teams filled in
			if (!array_key_exists('Person', $game['HomeTeam'])) {
				$game['HomeTeam']['Person'] = array();
			}
			if (!array_key_exists('Person', $game['AwayTeam'])) {
				$game['AwayTeam']['Person'] = array();
			}
			$captains = Set::extract ('/email_formatted', array_merge ($game['HomeTeam']['Person'], $game['AwayTeam']['Person']));
			if (!empty($captains)) {
				echo $this->Html->link(__('email captains', true),
						'mailto:' . implode (';', $captains));
			}
			?></td>
		</tr>
		<tr>
			<td><?php __('Away score'); ?>:</td>
			<td><?php echo $home['score_against']; ?></td>
			<td><?php __('Away score'); ?>:</td>
			<td><?php echo $away['score_for']; ?></td>
			<td></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

</div>
