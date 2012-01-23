<?php
class AllController extends AppController {

	var $name = 'All';
	var $uses = array('Game');
	var $helpers = array('ZuluruGame');

	function isAuthorized() {
		switch ($this->params['action']) {
			case 'splash':
				return true;
		}

		return false;
	}

	// TODO: Split the pieces into their own controllers and use requestAction to fetch them here
	function splash() {
		// We already have a lot of the information we need, stored from when we built the menu
		$this->set('id', $this->Auth->user('id'));
		$this->set('name', $this->Session->read('Zuluru.Person.full_name'));
		$this->set('teams', $this->Session->read('Zuluru.Teams'));
		$this->set('divisions', $this->Session->read('Zuluru.Divisions'));
		$this->set('unpaid', $this->Session->read('Zuluru.Unpaid'));

		$team_ids = $this->Session->read('Zuluru.TeamIDs');
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
					'GameSlot' => array('Field' => 'Facility'),
					'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
					// Get the list of captains for each team, for the popup
					'HomeTeam' => array(
						'Person' => array(
							'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
							'fields' => array('id', 'first_name', 'last_name'),
						),
					),
					'AwayTeam' => array(
						'Person' => array(
							'conditions' => array('TeamsPerson.position' => Configure::read('privileged_roster_positions')),
							'fields' => array('id', 'first_name', 'last_name'),
						),
					),
					'Attendance' => array(
						'conditions' => array('Attendance.person_id' => $this->Auth->user('id')),
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
						$attendance = $this->Game->_read_attendance($game['HomeTeam']['id'], $game['Game']['id']);
						$reread = true;
					}
					if ($game['AwayTeam']['track_attendance'] && in_array($game['AwayTeam']['id'], $team_ids)) {
						$attendance = $this->Game->_read_attendance($game['AwayTeam']['id'], $game['Game']['id']);
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
	}

	function cron() {
		$this->layout = 'bare';
		Configure::write ('debug', 0);
		$controllers = array('people', 'leagues', 'divisions', 'teams', 'games', 'team_events');
		$this->set(compact('controllers'));
		foreach ($controllers as $controller) {
			$this->set($controller, $this->requestAction(array('controller' => $controller, 'action' => 'cron'), array('return')));
		}
	}
}
?>
