			<dt><?php __('Rating Points'); ?></dt>
			<dd>
				<?php
				if ($game['Game']['home_score'] == $game['Game']['away_score'] && $game['Game']['rating_points'] == 0) {
					echo 'No points were transferred between teams';
				}
				else {
					if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
						$winner = $this->element('teams/block', array('team' => $game['HomeTeam'], 'show_shirt' => false));
						$loser = $this->element('teams/block', array('team' => $game['AwayTeam'], 'show_shirt' => false));
					}
					else {
						$winner = $this->element('teams/block', array('team' => $game['AwayTeam'], 'show_shirt' => false));
						$loser = $this->element('teams/block', array('team' => $game['HomeTeam'], 'show_shirt' => false));
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
