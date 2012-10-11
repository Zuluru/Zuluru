<?php
class AllController extends AppController {

	var $name = 'All';
	var $uses = array('Game', 'Team', 'Event', 'League');
	var $helpers = array('ZuluruGame');

	function publicActions() {
		return array('cron');
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		switch ($this->params['action']) {
			case 'splash':
				return true;
		}

		return false;
	}

	// TODO: Split the pieces into their own controllers and use requestAction to fetch them here
	function splash() {
		// We already have a lot of the information we need, stored from when we built the menu
		$id = $this->Auth->user('id');
		$name = $this->Session->read('Zuluru.Person.full_name');
		$teams = $this->Session->read('Zuluru.Teams');
		$team_ids = $this->Session->read('Zuluru.TeamIDs');
		$divisions = $this->Session->read('Zuluru.Divisions');
		$unpaid = $this->Session->read('Zuluru.Unpaid');

		if (!empty ($team_ids)) {
			$game_opts = array(
				'limit' => 4,
				'conditions' => array(
					'OR' => array(
						'HomeTeam.id' => $team_ids,
						'AwayTeam.id' => $team_ids,
					),
					'Game.published' => true,
				),
				'fields' => array(
					'Game.id', 'Game.home_team', 'Game.home_score', 'Game.away_team', 'Game.away_score', 'Game.status', 'Game.division_id',
					'Game.home_dependency_type', 'Game.home_dependency_id', 'Game.away_dependency_type', 'Game.away_dependency_id',
					'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
					'HomeTeam.id', 'HomeTeam.name',
					'AwayTeam.id', 'AwayTeam.name',
				),
				'contain' => array(
					'Division' => array('Day'),
					'GameSlot' => array('Field' => 'Facility'),
					'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
					'HomeTeam',
					'AwayTeam',
					'Attendance' => array(
						'conditions' => array('Attendance.person_id' => $id),
					),
				),
			);

			$past_games = array_reverse ($this->Game->find ('all', array_merge_recursive ($game_opts, array(
				'conditions' => array('GameSlot.game_date < CURDATE()'),
				'order' => 'GameSlot.game_date DESC, GameSlot.game_start DESC',
			))));
			$future_games = $this->Game->find ('all', array_merge_recursive ($game_opts, array(
				'conditions' => array('GameSlot.game_date >= CURDATE()'),
				'order' => 'GameSlot.game_date ASC, GameSlot.game_start ASC',
			)));

			// Check if we need to update attendance records for any upcoming games
			$reread = false;
			foreach ($future_games as $game) {
				if (empty ($game['Attendance'])) {
					if ($game['HomeTeam']['track_attendance'] && in_array($game['HomeTeam']['id'], $team_ids)) {
						$attendance = $this->Game->_read_attendance($game['HomeTeam']['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);
						$reread = true;
					}
					if ($game['AwayTeam']['track_attendance'] && in_array($game['AwayTeam']['id'], $team_ids)) {
						$attendance = $this->Game->_read_attendance($game['AwayTeam']['id'], Set::extract('/Division/Day/id', $game), $game['Game']['id']);
						$reread = true;
					}
				}
			}

			if ($reread) {
				$future_games = $this->Game->find ('all', array_merge_recursive ($game_opts, array(
					'conditions' => array('GameSlot.game_date >= CURDATE()'),
					'order' => 'GameSlot.game_date ASC, GameSlot.game_start ASC',
				)));
			}

			$this->set('games', array_merge ($past_games, $future_games));
		} else {
			$this->set('games', array());
		}

		$past_teams = $this->Team->TeamsPerson->find('count', array(
				'conditions' => array('person_id' => $id),
				'contain' => array(),
		)) - count($team_ids);

		// If the user has nothing going on, pull some more details to allow us to help them get started
		if (!$this->is_admin && empty($teams) && empty($divisions) && empty($unpaid)) {
			if (Configure::read('feature.registration')) {
				$membership_types = $this->Event->EventType->find('list', array(
						'conditions' => array('type' => 'membership'),
				));
				$membership_events = $this->Event->find('count', array(
						'conditions' => array(
							'event_type_id' => array_keys($membership_types),
							'open < CURDATE()',
							'close > CURDATE()',
						),
						'contain' => array(),
				));
				$non_membership_events = $this->Event->find('count', array(
						'conditions' => array(
							'NOT' => array('event_type_id' => array_keys($membership_types)),
							'open < CURDATE()',
							'close > CURDATE()',
						),
						'contain' => array(),
				));
			}

			$open_teams = $this->Team->find('count', array(
					'conditions' => array(
						'Team.open_roster' => true,
						'OR' => array(
							'Division.is_open',
							'Division.open > CURDATE()',
						),
					),
					'contain' => array('Division'),
			));

			$open_leagues = $this->League->find('count', array(
					'conditions' => array(
						'OR' => array(
							'League.is_open',
							'League.open > CURDATE()',
						),
					),
					'contain' => array(),
			));
		}

		$this->set(compact('id', 'name', 'teams', 'divisions', 'unpaid', 'past_teams',
				'membership_events', 'non_membership_events', 'open_teams', 'open_leagues'));
	}

	function cron() {
		$this->layout = 'bare';
		if (!ini_get('safe_mode')) { 
			set_time_limit(0);
		}
		Configure::write ('debug', 0);
		$controllers = array('people', 'leagues', 'teams', 'games', 'team_events');
		$this->set(compact('controllers'));
		foreach ($controllers as $controller) {
			$this->set($controller, $this->requestAction(array('controller' => $controller, 'action' => 'cron'), array('return')));
		}
	}
}
?>
