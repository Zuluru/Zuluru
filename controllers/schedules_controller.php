<?php
class SchedulesController extends AppController {

	var $name = 'Schedules';
	var $uses = array('League');
	var $components = array('Lock');

	var $numTeams = null;

	function isAuthorized() {
		// People can perform these operations on leagues they coordinate
		if (in_array ($this->params['action'], array(
				'add',
				'delete',
				'reschedule',
				'publish',
				'unpublish',
		)))
		{
			// If a league id is specified, check if we're a coordinator of that league
			$league = $this->_arg('league');
			if ($league && in_array ($league, $this->Session->read('Zuluru.LeagueIDs'))) {
				return true;
			}
		}

		return false;
	}

	function _init($id) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->League->contain (array (
			'Team' => array('order' => 'Team.name'),
		));
		$this->league = $this->League->read(null, $id);
		if ($this->league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->league_obj = $this->_getComponent ('LeagueType', $this->league['League']['schedule_type'], $this);

		$this->set(array('id' => $id, 'league' => $this->league));
		$this->_addLeagueMenuItems ($this->league);
	}

	function add() {
		$id = $this->_arg('league');
		$this->_init($id);

		if($this->_numTeams() < 2) {
			$this->Session->setFlash(__('Cannot schedule games in a league with less than two teams.', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'view', 'league' => $id));
		}

		// Must currently have even # of teams for scheduling unless the excludeTeams flag is set
		if ($this->_numTeams() % 2 && !$this->league['League']['excludeTeams']) {
			// TODO: Embed a link to "edit your league" into this, in a way that doesn't break i18n
			$this->Session->setFlash(__('Must currently have an even number of teams in your league. ' . 
				'If you need a bye, please create a team named Bye and add it to your league. ' .
				'Otherwise, edit your league and set the "excludeTeams" flag.', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'view', 'league' => $id));
		}

		// What's the default first step?
		$step = ($this->league['League']['exclude_teams'] ? 'exclude' : 'type');
		if (!empty ($this->data)) {
			$step = $this->data['Game']['step'];
		}
		$this->autoRender = false;
		$func = "_$step";
		$this->$func($id);
	}

	function _exclude($id) {
		// Validate any exclusion selection
		if (!empty ($this->data)){
			if ($this->_numTeams() % 2) {
				$this->Session->setFlash(sprintf (__('You marked %s teams to exclude, that leaves %s.' .
						' Cannot schedule games for an un-even number of teams!', true),
						count($this->data['ExcludeTeams']), $this->_numTeams()));
			} else {
				return $this->_type($id);
			}
		}
		$this->render('exclude');
	}

	function _type($id) {
		$types = $this->league_obj->scheduleOptions($this->_numTeams());

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'type') {
			if (!array_key_exists ($this->data['Game']['type'], $types)) {
				$this->Session->setFlash(__('Select the type of game or games to add.', true));
			} else {
				return $this->_date($id);
			}
		}

		$this->set(compact('types'));
		$this->render('type');
	}

	function _date($id) {
		// Find the list of available dates for scheduling this league
		$dates = $this->League->LeagueGameslotAvailability->find('all', array(
				'conditions' => array(
					'GameSlot.game_id' => null,
					'LeagueGameslotAvailability.league_id' => $id,
				),
				'fields' => 'DISTINCT GameSlot.game_date AS date',
				'order' => 'GameSlot.game_date',
		));
		if (count($dates) == 0) {
			$this->Session->setFlash(__('Sorry, there are no fields available for your league.  Check that fields have been allocated before attempting to proceed.', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'view', 'league' => $id));
		}
		$dates = Set::extract ('/GameSlot/date', $dates);

		list($num_dates, $num_fields) = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams());
		$desc = $this->league_obj->scheduleDescription ($this->data['Game']['type']);

		$this->set(compact('dates', 'num_dates', 'num_fields', 'desc'));
		$this->render('date');
	}

	function _confirm($id) {
		if (!$this->_canSchedule($id)) {
			$this->redirect(array('controller' => 'leagues', 'action' => 'view', 'league' => $id));
		}

		$this->set(array(
				'desc' => $this->league_obj->scheduleDescription($this->data['Game']['type'], $this->_numTeams()),
				'start_date' => $this->data['Game']['start_date'],
		));
		$this->render('confirm');
	}

	function _finalize($id) {
		if (!$this->Lock->lock ('scheduling')) {
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if (!$this->_canSchedule($id)) {
			$this->redirect(array('controller' => 'leagues', 'action' => 'view', 'league' => $id));
		}

		$exclude_teams = array();
		if (array_key_exists ('ExcludeTeams', $this->data)) {
			$exclude_teams = array_keys($this->data['ExcludeTeams']);
		}
		if ($this->league_obj->createSchedule ($id, $exclude_teams, $this->data['Game']['type'],
				$this->data['Game']['start_date'], $this->data['Game']['publish']))
		{
			$this->Lock->unlock ();
			$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
		}
		$this->Lock->unlock ();

		// The reason for failure will have been set in the flash somewhere in createSchedule.
		$this->set(array(
				'desc' => $this->league_obj->scheduleDescription($this->data['Game']['type'], $this->_numTeams()),
				'start_date' => $this->data['Game']['start_date'],
		));
		$this->render('confirm');
	}

	function _canSchedule($id) {
		$this->League->Game->contain ('GameSlot');
		$games = $this->League->Game->find ('count', array(
				'conditions' => array(
					'Game.league_id' => $id,
					'GameSlot.game_date' => $this->data['Game']['start_date'],
				),
		));

		if ($this->_numTeams() >= $games * 2 && !$this->data['Game']['double_header']) {
			$this->Session->setFlash(__('This league is already fully scheduled on the selected date.', true));
			return false;
		}

		list($num_dates, $num_fields) = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams());

		// TODO: Calculate this for each date in the range; not important right now as the ladder schedules one week at a time
		$fields = $this->League->LeagueGameslotAvailability->find('count', array(
				'conditions' => array(
					'GameSlot.game_id' => null,
					'GameSlot.game_date' => $this->data['Game']['start_date'],
					'LeagueGameslotAvailability.league_id' => $id,
				),
		));

		if ($num_fields > $fields) {
			$this->Session->setFlash(sprintf (__('There are insufficient fields available on %s.', true), $this->data['Game']['start_date']));
			return false;
		}

		return true;
	}

	function _numTeams() {
		if ($this->numTeams === null) {
			$this->numTeams = count($this->league['Team']);
			if (is_array($this->data) && array_key_exists ('ExcludeTeams', $this->data)){
				$this->numTeams -= count($this->data['ExcludeTeams']);
			}
		}

		return $this->numTeams;
	}

	function delete() {
		$id = $this->_arg('league');
		$this->_init($id);
		$date = $this->_arg('date');

		$this->League->Game->contain (array (
			'GameSlot',
		));

		$games = $this->League->Game->find ('all', array(
				'conditions' => array(
					'Game.league_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => array('Game.id', 'Game.published', 'Game.home_score'),
		));

		if ($this->_arg('confirm')) {
			// Wrap the whole thing in a transaction, for safety.
			$db =& ConnectionManager::getDataSource($this->League->Game->useDbConfig);
			$db->begin($this->League->Game);

			// Clear game_id from game_slots, and delete the games.
			$game_ids = Set::extract ('/Game/id', $games);
			if ($this->League->Game->GameSlot->updateAll (array('game_id' => null), array(
					'GameSlot.game_id' => $game_ids,
				)) &&
				$this->League->Game->deleteAll(array(
					'Game.id' => $game_ids,
				), false))
			{
				$this->Session->setFlash(__('Deleted games on the requested date.', true));
				$db->commit($this->League->Game);
				$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
			} else {
				$this->Session->setFlash(__('Failed to delete games on the requested date.', true));
				$db->rollback($this->League->Game);
			}
		}

		$this->set (compact ('date', 'games'));
	}

	function reschedule() {
		$id = $this->_arg('league');
		if (!$id) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$date = $this->_arg('date');

		$this->League->contain (array (
			'Team',
			'Day' => array('order' => 'day_id'),
			'Game' => array(
				'GameSlot' => array(
					'conditions' => array('game_date' => $date),
				),
			),
			'LeagueGameslotAvailability' => array(
				'GameSlot' => array(
					// This will still return all of the Availability records, but many will have
					// empty GameSlot arrays, so Set::Extract calls won't match and they're ignored
					'conditions' => array(
						'game_date >=' => $date,
						'game_id' => null,
					),
				),
			),
		));
		$league = $this->League->read(null, $id);
		if ($league === false) {
			$this->Session->setFlash(__('Invalid league', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		// TODO: The read will load a bunch of games with empty game slots because
		// they don't match the provided date; need a custom join?
		$league['Game'] = Set::extract ("/GameSlot[game_date=$date]/..", $league['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
		$league_obj->league = $league;
		if (!empty ($this->data)) {
			// Wrap the whole thing in a transaction, for safety.
			$db =& ConnectionManager::getDataSource($this->League->Game->useDbConfig);
			$db->begin($this->League->Game);

			if ($league_obj->assignFieldsByPreferences($this->data['new_date'], $league['Game'])) {

				if ($this->League->Game->_saveGames ($league_obj->games, $this->data['publish'])) {
					$unused_slots = Set::extract ('/GameSlot/id', $league['Game']);
					if ($this->League->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $unused_slots))) {
						$this->Session->setFlash(__('Games rescheduled', true));
						$db->commit($this->League->Game);
						$this->redirect (array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
					} else {
						$this->Session->setFlash(__('Problem! Games were rescheduled, but old game slots were not freed. Schedule will be unstable!', true));
					}
				}
			}
			// Failure flash message will have been set by whatever failed
			$db->rollback($this->League->Game);
		}

		// Find the list of available dates for scheduling this league
		$dates = $this->League->LeagueGameslotAvailability->find('all', array(
				'conditions' => array(
					'GameSlot.game_date >' => $date,
					'GameSlot.game_id' => null,
					'LeagueGameslotAvailability.league_id' => $id,
				),
				'fields' => 'DISTINCT UNIX_TIMESTAMP(GameSlot.game_date) AS date',
				'order' => 'GameSlot.game_date',
		));
		if (count($dates) == 0) {
			$this->Session->setFlash(__('Sorry, there are no fields available for your league.  Check that fields have been allocated before attempting to proceed.', true));
			$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
		}
		$dates = Set::extract ('/0/date', $dates);

		$this->set(compact('id', 'league', 'date', 'dates'));
		$this->_addLeagueMenuItems ($league);
	}

	function publish() {
		$id = $this->_arg('league');
		$date = $this->_arg('date');

		$this->League->Game->contain (array (
			'GameSlot',
		));
		$games = Set::extract ('/Game/id', $this->League->Game->find ('all', array(
				'conditions' => array(
					'Game.league_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => 'Game.id',
		)));

		if ($this->League->Game->updateAll (
			array('published' => 1),
			array('Game.id' => $games)
		))
		{
			$this->Session->setFlash(__('Published games on the requested date.', true));
		} else {
			$this->Session->setFlash(__('Failed to publish games on the requested date.', true));
		}

		$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
	}

	function unpublish() {
		$id = $this->_arg('league');
		$date = $this->_arg('date');

		$this->League->Game->contain (array (
			'GameSlot',
		));
		$games = Set::extract ('/Game/id', $this->League->Game->find ('all', array(
				'conditions' => array(
					'Game.league_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => 'Game.id',
		)));

		if ($this->League->Game->updateAll (
			array('published' => 0),
			array('Game.id' => $games)
		))
		{
			$this->Session->setFlash(__('Unpublished games on the requested date.', true));
		} else {
			$this->Session->setFlash(__('Failed to unpublish games on the requested date.', true));
		}

		$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $id));
	}
}
?>
