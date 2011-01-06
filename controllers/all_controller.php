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
		$this->set('leagues', $this->Session->read('Zuluru.Leagues'));
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
					'Game.id', 'Game.home_team', 'Game.home_score', 'Game.away_team', 'Game.away_score', 'Game.status', 'Game.league_id',
					'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
					'HomeTeam.id', 'HomeTeam.name',
					'AwayTeam.id', 'AwayTeam.name',
				),
				'contain' => array(
					'GameSlot' => array('Field' => array('ParentField')),
					'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
					'HomeTeam',
					'AwayTeam',
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
			$this->set('games', array_merge ($past_games, $future_games));
		} else {
			$this->set('games', array());
		}
	}

	function cron() {
		$this->layout = 'bare';
		Configure::write ('debug', 0);
		$games = $this->requestAction(array('controller' => 'games', 'action' => 'cron'), array('return'));
		$people = $this->requestAction(array('controller' => 'people', 'action' => 'cron'), array('return'));
		$this->set(compact ('games', 'people'));
	}
}
?>
