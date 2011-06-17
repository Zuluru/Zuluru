<?php

class ZuluruGameHelper extends Helper {
	var $helpers = array('Html', 'ZuluruHtml', 'Session');

	function displayScore($game, $show_score_for_team = false) {
		// Data may come in one of two forms.
		if (array_key_exists ('Game', $game)) {
			// Either all the models are at the same level in the array...
			$details = $game['Game'];
		} else {
			// ...or the Game model is at the top and others are below
			$details = $game;
		}
		$score_entry = $game['ScoreEntry'];

		// Calculate the game end time stamp
		$end_time = strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['display_game_end']}") +
				Configure::read('timezone.adjust') * 60;

		// If scores are being shown from a particular team's perspective,
		// we may need to swap the home and away scores.
		if ($show_score_for_team == $details['away_team']) {
			$first_score = $details['away_score'];
			$second_score = $details['home_score'];
		} else {
			$first_score = $details['home_score'];
			$second_score = $details['away_score'];
		}

		if ($first_score !== null && $second_score !== null) {
			echo "{$first_score} - {$second_score}";
			if (strpos ($details['status'], 'default') !== false) {
				echo ' (' . __('default', true) . ')';
			}
		} else {
			// Check if one of the teams involved in the game is a team the current user is a captain of
			$teams = array_intersect (array($details['home_team'], $details['away_team']), $this->Session->read('Zuluru.OwnedTeamIDs'));
			$team_id = array_pop ($teams);

			if (!empty ($score_entry)) {
				$score_entry = array_shift ($score_entry);

				// If scores are being shown from a particular team's perspective,
				// we may need to swap the home and away scores.
				if ($show_score_for_team == $score_entry['team_id'] ||
					($show_score_for_team === false && $score_entry['team_id'] == $details['home_team']))
				{
					$first_score = $score_entry['score_for'];
					$second_score = $score_entry['score_against'];
				} else {
					$first_score = $score_entry['score_against'];
					$second_score = $score_entry['score_for'];
				}
				echo "{$first_score} - {$second_score}";

				if ($team_id) {
					echo $this->Html->link(
							__('Edit score', true),
							array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $team_id));

					// Check if someone is a captain on both teams that played each other
					$team_id = array_pop ($teams);
					if ($team_id) {
						echo $this->Html->link(
								__('Submit', true),
								array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $team_id));
					}
				} else {
					echo ' (' . __('unofficial', true) . ')';
				}
			} else if (time() > $end_time - 60 * 60) {
				// Allow score submissions up to an hour before scheduled game end time.
				// Some people like to submit via mobile phone immediately, and games can end early.
				if ($team_id) {
					echo $this->Html->link(
							__('Submit', true),
							array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $team_id));
				} else {
					__('not entered');
				}
			} else {
				// Check if one of the teams involved in the game is a team the current user is on
				$team_id = array_pop (array_intersect (array($details['home_team'], $details['away_team']), $this->Session->read('Zuluru.TeamIDs')));
				if ($team_id) {
					echo $this->Html->link(
							__('iCal', true),
							array('controller' => 'games', 'action' => 'ical', $details['id'], $team_id, 'game.ics'));
				}
			}
		}

		// Give admins and coordinators the option to edit games
		$view =& ClassRegistry::getObject('view');
		if ($view->viewVars['is_admin'] || in_array ($details['league_id'], $this->Session->read('Zuluru.LeagueIDs'))) {
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'games', 'action' => 'edit', 'game' => $details['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		}
	}
}

?>
