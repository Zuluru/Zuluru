<?php if (empty($games)): ?>
<p>No game results to report.</p>
<?php else: ?>
<div style="width: 200px;">
	<a class="scroll_prev browse scroll_left"></a>
	<div class="scrollable" id="scrollable">
		<div class="items">
<?php foreach ($games as $game): ?>
			<div><p>
<?php
			echo $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['Game']['home_team'])) .
				' vs ' .
				$this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['Game']['away_team'])) .
				'<br />';
			if (Game::_is_finalized($game)) {
				$home_score = $game['Game']['home_score'];
				$away_score = $game['Game']['away_score'];
				$suffix = ' (F)';
			} else {
				$suffix = " (in progress at {$game['GameSlot']['Field']['long_code']})";
				$entry = Game::_get_best_score_entry($game);
				if (empty($entry)) {
					$suffix = " (just starting at {$game['GameSlot']['Field']['long_code']})";
					$home_score = 0;
					$away_score = 0;
				} else if ($entry['team_id'] == $game['Game']['home_team']) {
					$home_score = $entry['score_for'];
					$away_score = $entry['score_against'];
				} else {
					$home_score = $entry['score_against'];
					$away_score = $entry['score_for'];
				}
			}
			echo $this->Html->link("$home_score - $away_score", array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id'])) . $suffix;
?>
			</p></div>
<?php endforeach; ?>
		</div>
	</div>
	<a class="scroll_next browse scroll_right"></a>
</div>
<?php
	echo $this->Html->scriptBlock('
jQuery(".scrollable").scrollable({
	next: ".scroll_next",
	prev: ".scroll_prev"
});
	');
?>
<?php endif; ?>
