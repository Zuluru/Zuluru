			<dt><?php __('Rating Points'); ?></dt>
			<dd>
				<?php
				if ($game['Game']['home_score'] == $game['Game']['away_score'] && $game['Game']['rating_points'] == 0) {
					echo 'No points were transferred between teams';
				}
				else {
					if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
						$winner = $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']));
						$loser = $this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']));
					}
					else {
						$winner = $this->Html->link($game['AwayTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['AwayTeam']['id']));
						$loser = $this->Html->link($game['HomeTeam']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $game['HomeTeam']['id']));
					}
					if ($game['Game']['rating_points'] < 0) {
						$winner_text = __('lose', true);
						$loser_text = __('gain', true);
						$transfer = -$game['Game']['rating_points'];
					} else {
						$winner_text = __('gain', true);
						$loser_text = __('lose', true);
						$transfer = $game['Game']['rating_points'];
					}
					if ($transfer == 1) {
						$points = __('point', true);
					} else {
						$points = __('points', true);
					}
					echo "{$game['Game']['rating_points']} ($winner $winner_text $transfer $points " .
						__('and', true) . " $loser $loser_text $transfer $points)";
				}
				?>

			</dd>
