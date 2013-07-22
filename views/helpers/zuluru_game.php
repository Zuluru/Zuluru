<?php

class ZuluruGameHelper extends Helper {
	var $helpers = array('Html', 'ZuluruHtml', 'Session');

	function displayScore($game, $league, $show_score_for_team = false) {
		// Data may come in one of two forms.
		if (array_key_exists ('Game', $game)) {
			// Either all the models are at the same level in the array...
			$details = $game['Game'];
		} else {
			// ...or the Game model is at the top and others are below
			$details = $game;
		}

		$view =& ClassRegistry::getObject('view');
		$is_logged_in = $view->viewVars['is_logged_in'];
		$is_admin = $view->viewVars['is_admin'];
		$is_manager = $view->viewVars['is_manager'] && in_array($league['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs'));
		$is_volunteer = $view->viewVars['is_volunteer'];
		$is_coordinator = in_array ($details['division_id'], $this->Session->read('Zuluru.DivisionIDs'));

		// Calculate the game start and end time stamps
		$start_time = strtotime("{$game['GameSlot']['game_date']} {$game['GameSlot']['game_start']}") +
				Configure::read('timezone.adjust') * 60;
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

		// Check if one of the teams involved in the game is a team the current user is a captain of
		$teams = array_intersect (array($details['home_team'], $details['away_team']), $this->Session->read('Zuluru.OwnedTeamIDs'));
		$team_id = array_pop ($teams);

		$links = array();
		if (Game::_is_finalized($details)) {
			if (in_array($details['status'], Configure::read('unplayed_status'))) {
				__($details['status']);
			} else {
				echo "{$first_score} - {$second_score}";
				if (strpos ($details['status'], 'default') !== false) {
					echo ' (' . __('default', true) . ')';
				}
			}

			if (League::hasStats($league)) {
				if ($team_id || $is_coordinator) {
					$links[] = $this->Html->link(
							__('Submit Stats', true),
							array('controller' => 'games', 'action' => 'submit_stats', 'game' => $details['id'], 'team' => $team_id));
				}
				if (($this->params['controller'] != 'games' || $this->params['action'] != 'stats') && ($is_logged_in || Configure::read('feature.public'))) {
					$links[] = $this->ZuluruHtml->iconLink('stats_24.png',
							array('controller' => 'games', 'action' => 'stats', 'game' => $details['id'], 'team' => $show_score_for_team),
							array('alt' => __('Game Stats', true), 'title' => __('Game Stats', true)));
				}
			}
		} else {
			$score_entry = Game::_get_best_score_entry($game);
			if (!empty($score_entry)) {
				if (in_array($score_entry['status'], Configure::read('unplayed_status'))) {
					__($score_entry['status']);
				} else {
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
				}

				if ($team_id) {
					if ($score_entry['status'] == 'in_progress') {
						$links[] = $this->Html->link(
								__('Live Score', true),
								array('controller' => 'games', 'action' => 'live_score', 'game' => $details['id'], 'team' => $team_id));
					} else {
						$links[] = $this->Html->link(
							__('Edit score', true),
							array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $team_id));
					}

					// Check if someone is a captain on both teams that played each other
					$second_team_id = array_pop ($teams);
					if ($second_team_id) {
						$links[] = $this->Html->link(
							__('Submit', true),
							array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $second_team_id));
					}
				} else if ($is_volunteer) {
					// Allow specified individuals (referees, umpires, volunteers) to live score without a team id
					if ($score_entry['status'] == 'in_progress') {
						$links[] = $this->Html->link(
								__('Live Score', true),
								array('controller' => 'games', 'action' => 'live_score', 'game' => $details['id']));
					} else {
						$links[] = $this->Html->link(
							__('Edit score', true),
							array('controller' => 'games', 'action' => 'edit', 'game' => $details['id']));
					}
				}

				if ($score_entry['status'] == 'in_progress') {
					echo ' (' . __('in progress', true) . ')';
				} else {
					echo ' (' . __('unofficial', true) . ')';
				}
			} else if ($score_entry === null) {
				__('score mismatch');
			} else if (time() > $end_time - 60 * 60 + Configure::read('timezone.adjust') * 60) {
				// Allow score submissions up to an hour before scheduled game end time.
				// Some people like to submit via mobile phone immediately, and games can end early.
				if ($team_id) {
					$links[] = $this->Html->link(
						__('Submit', true),
						array('controller' => 'games', 'action' => 'submit_score', 'game' => $details['id'], 'team' => $team_id));
				} else {
					__('not entered');
				}
			} else if (time() > $start_time - 30 * 60 + Configure::read('timezone.adjust') * 60 && $details['home_team'] != null && $details['away_team'] != null) {
				// Allow live scoring to start up to half an hour before scheduled game start time.
				// This allows score keepers to get the page loaded and ready to go in advance.
				if ($team_id) {
					$links[] = $this->Html->link(
							__('Live Score', true),
							array('controller' => 'games', 'action' => 'live_score', 'game' => $details['id'], 'team' => $team_id));
				} else if ($is_volunteer) {
					// Allow specified individuals (referees, umpires, volunteers) to live score without a team id
					$links[] = $this->Html->link(
							__('Live Score', true),
							array('controller' => 'games', 'action' => 'live_score', 'game' => $details['id']));
				}
			} else {
				// Check if one of the teams involved in the game is a team the current user is on
				$player_team_id = array_pop (array_intersect (array($details['home_team'], $details['away_team']), $this->Session->read('Zuluru.TeamIDs')));
				if ($player_team_id) {
					$links[] = $this->Html->link(
							__('iCal', true),
							array('controller' => 'games', 'action' => 'ical', $details['id'], $player_team_id, 'game.ics'));
				}
			}
		}

		// Give admins, managers and coordinators the option to edit games
		if ($is_admin || $is_manager || $is_coordinator) {
			$links[] = $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'games', 'action' => 'edit', 'game' => $details['id'], 'return' => true),
				array('alt' => __('Edit', true), 'title' => __('Edit', true)));
		}

		echo $this->Html->tag('span', implode('', $links), array('class' => 'actions'));

	}
}

?>
