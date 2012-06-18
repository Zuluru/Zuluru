<?php
class SchedulesController extends AppController {

	var $name = 'Schedules';
	var $uses = array('Division');
	var $components = array('Lock');

	var $numTeams = null;

	function isAuthorized() {
		// People can perform these operations on divisions they coordinate
		if (in_array ($this->params['action'], array(
				'add',
				'delete',
				'reschedule',
				'publish',
				'unpublish',
		)))
		{
			// If a division id is specified, check if we're a coordinator of that division
			$division = $this->_arg('division');
			if ($division && in_array ($division, $this->Session->read('Zuluru.DivisionIDs'))) {
				return true;
			}
		}

		return false;
	}

	function _init($id) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain (array (
			'Team' => array('order' => 'Team.name'),
			'League',
		));
		$this->division = $this->Division->read(null, $id);
		if ($this->division === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		if ($this->_arg('playoff')) {
			$this->league_obj = $this->_getComponent ('LeagueType', 'tournament', $this);
			$this->set('playoff', true);
		} else {
			$this->league_obj = $this->_getComponent ('LeagueType', $this->division['Division']['schedule_type'], $this);
		}

		Configure::load("sport/{$this->division['League']['sport']}");

		$this->set(array('id' => $id, 'division' => $this->division));
		$this->_addDivisionMenuItems ($this->division['Division'], $this->division['League']);
	}

	function add() {
		$id = $this->_arg('division');
		$this->_init($id);

		if($this->_numTeams() < 2) {
			$this->Session->setFlash(__('Cannot schedule games in a division with less than two teams.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		// Non-tournament divisions must currently have even # of teams for scheduling unless the exclude_teams flag is set
		if ($this->_numTeams() % 2 && !$this->division['Division']['exclude_teams'] &&
			$this->division['Division']['schedule_type'] != 'tournament' && !$this->_arg('playoff'))
		{
			// TODO: Embed a link to "edit your division" into this, in a way that doesn't break i18n
			$this->Session->setFlash(__('Must currently have an even number of teams in your division. ' . 
				'If you need a bye, please create a team named Bye and add it to your division. ' .
				'Otherwise, edit your division and set the "exclude teams" flag.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		// What's the default first step?
		$step = ($this->division['Division']['exclude_teams'] ? 'exclude' : 'type');
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
						count($this->data['ExcludeTeams']), $this->_numTeams()), 'default', array('class' => 'info'));
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
				$this->Session->setFlash(__('Select the type of game or games to add.', true), 'default', array('class' => 'info'));
			} else {
				return $this->_overflow_type($id);
			}
		}

		$this->set(compact('types'));
		$this->render('type');
	}

	function _overflow_type($id) {
		// Large tournaments might have an additional bracket to sort out,
		// but small ones won't.
		if (substr($this->data['Game']['type'], 0, 12) != 'brackets_of_') {
			return $this->_date($id);
		}

		$size = substr($this->data['Game']['type'], 12);
		list ($x, $r) = $this->league_obj->splitBrackets($this->_numTeams(), $size);
		if (!$r) {
			return $this->_names($id);
		}
		$types = $this->league_obj->scheduleOptions($r);

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'overflow_type') {
			if (!array_key_exists ($this->data['Game']['overflow_type'], $types)) {
				$this->Session->setFlash(__('Select the type of game or games to add.', true), 'default', array('class' => 'info'));
			} else {
				return $this->_names($id);
			}
		}

		$this->set(compact('types'));
		$this->render('overflow_type');
	}

	function _names($id) {
		$size = substr($this->data['Game']['type'], 12);
		list ($x, $r) = $this->league_obj->splitBrackets($this->_numTeams(), $size);
		if ($x == 1 && $r == 0) {
			return $this->_date($id);
		}

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'names') {
			$pools = $x + ($r > 0);
			$proceed = true;
			for ($i = 1; $i <= $pools; ++ $i) {
				if (empty($this->data['Game']['name'][$i])) {
					$proceed = false;
					$this->Session->setFlash(__('Pool names cannot be empty.', true), 'default', array('class' => 'info'));
				}
			}
			if ($proceed) {
				return $this->_date($id);
			}
		}

		if ($r > 0) {
			$types = $this->league_obj->scheduleOptions($r);
		}
		$this->set(compact('size', 'x', 'r', 'types'));
		$this->render('names');
	}

	function _date($id) {
		// Find the list of available dates for scheduling this division
		$dates = $this->Division->DivisionGameslotAvailability->find('all', array(
				'conditions' => array(
					'GameSlot.game_id' => null,
					'DivisionGameslotAvailability.division_id' => $id,
					'GameSlot.game_date >= CURDATE()',
				),
				'fields' => 'DISTINCT GameSlot.game_date AS date',
				'order' => 'GameSlot.game_date',
		));
		if (count($dates) == 0) {
			$this->Session->setFlash(sprintf(__('Sorry, there are no %s available for your division. Check that %s have been allocated before attempting to proceed.', true), Configure::read('sport.fields'), Configure::read('sport.fields')), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}
		$dates = Set::extract ('/GameSlot/date', $dates);

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'date') {
			if ($this->_canSchedule($id)) {
				return $this->_confirm($id);
			}
		}

		$num_fields = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams(), $this->data['Game']['overflow_type']);
		$desc = $this->league_obj->scheduleDescription ($this->data['Game']['type'], $this->_numTeams());

		$this->set(compact('dates', 'num_fields', 'desc'));
		$this->render('date');
	}

	function _confirm($id) {
		if (!$this->_canSchedule($id)) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		$this->set(array(
				'desc' => $this->league_obj->scheduleDescription($this->data['Game']['type'], $this->_numTeams()),
				'start_date' => $this->data['Game']['start_date'],
		));
		$this->render('confirm');
	}

	function _finalize($id) {
		if (!$this->Lock->lock ('scheduling', 'schedule creation or edit')) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		if (!$this->_canSchedule($id)) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		$exclude_teams = array();
		if (array_key_exists ('ExcludeTeams', $this->data)) {
			$exclude_teams = array_keys($this->data['ExcludeTeams']);
		}
		if (array_key_exists ('name', $this->data['Game'])) {
			$names = $this->data['Game']['name'];
		} else {
			$names = null;
		}
		if ($this->league_obj->createSchedule ($id, $exclude_teams, $this->data['Game']['type'],
				$this->data['Game']['start_date'], $this->data['Game']['publish'], $this->data['Game']['overflow_type'], $names))
		{
			$this->Lock->unlock ();
			$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
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
		$this->Division->contain('League');
		$division = $this->Division->read(null, $id);

		$this->Division->Game->contain ('GameSlot');
		$games = $this->Division->Game->find ('count', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'GameSlot.game_date' => $this->data['Game']['start_date'],
				),
		));

		if ($this->_numTeams() <= $games * 2 && !$this->data['Game']['double_header'] &&
			$division['Division']['schedule_type'] != 'tournament' && !$this->_arg('playoff'))
		{
			$this->Session->setFlash(__('This division is already fully scheduled on the selected date.', true), 'default', array('class' => 'info'));
			return false;
		}

		// The requirements may come back from this as an array for each schedule block.
		// For our check here, we want them as a single array, ordered by round number.
		$num_fields = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams(), $this->data['Game']['overflow_type']);
		if (is_array(current($num_fields))) {
			$temp = array();
			foreach ($num_fields as $rounds) {
				foreach ($rounds as $round => $required) {
					if (!array_key_exists($round, $temp)) {
						$temp[$round] = $required;
					} else {
						$temp[$round] += $required;
					}
				}
			}
			$num_fields = $temp;
		}

		$field_counts = $this->Division->DivisionGameslotAvailability->find('all', array(
				'fields' => array('count(GameSlot.id) AS count'),
				'conditions' => array(
					'GameSlot.game_id' => null,
					'GameSlot.game_date >=' => $this->data['Game']['start_date'],
					'DivisionGameslotAvailability.division_id' => $id,
				),
				'group' => array('GameSlot.game_date', 'GameSlot.game_start'),
				'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
		));

		foreach ($num_fields as $required) {
			while ($required > 0) {
				if (empty($field_counts)) {
					$this->Session->setFlash(sprintf(__('There are insufficient %s available to support the requested schedule.', true), Configure::read('sport.fields')), 'default', array('class' => 'info'));
					return false;
				}
				$field_count = array_shift($field_counts);
				$required -= $field_count[0]['count'];
			}
		}

		return true;
	}

	function _numTeams() {
		if ($this->numTeams === null) {
			$this->numTeams = count($this->division['Team']);
			if (is_array($this->data) && array_key_exists ('ExcludeTeams', $this->data)){
				$this->numTeams -= count($this->data['ExcludeTeams']);
			}
		}

		return $this->numTeams;
	}

	function delete() {
		$id = $this->_arg('division');
		$this->_init($id);
		$date = $this->_arg('date');

		$this->Division->Game->contain (array (
			'GameSlot',
		));

		$games = $this->Division->Game->find ('all', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => array('Game.id', 'Game.published', 'Game.home_score'),
		));

		if ($this->_arg('confirm')) {
			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Division->Game);

			// Clear game_id from game_slots, and delete the games.
			$game_ids = Set::extract ('/Game/id', $games);
			if ($this->Division->Game->GameSlot->updateAll (array('game_id' => null), array(
					'GameSlot.game_id' => $game_ids,
				)) &&
				$this->Division->Game->deleteAll(array(
					'Game.id' => $game_ids,
				), false))
			{
				$this->Session->setFlash(__('Deleted games on the requested date.', true), 'default', array('class' => 'success'));
				$transaction->commit();
				$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
			} else {
				$this->Session->setFlash(__('Failed to delete games on the requested date.', true), 'default', array('class' => 'warning'));
			}
		}

		$this->set (compact ('date', 'games'));
	}

	function reschedule() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$date = $this->_arg('date');

		$this->Division->contain (array (
			'League',
			'Team',
			'Day' => array('order' => 'day_id'),
			'Game' => array(
				'GameSlot' => array(
					'conditions' => array('game_date' => $date),
				),
			),
			'DivisionGameslotAvailability' => array(
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
		$division = $this->Division->read(null, $id);
		if ($division === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		// TODO: The read will load a bunch of games with empty game slots because
		// they don't match the provided date; need a custom join?
		$division['Game'] = Set::extract ("/GameSlot[game_date=$date]/..", $division['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$league_obj->division = $division;
		if (!empty ($this->data)) {
			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Division->Game);

			if ($league_obj->assignFieldsByPreferences($this->data['new_date'], $division['Game'])) {

				if ($this->Division->Game->_saveGames ($league_obj->games, $this->data['publish'])) {
					$unused_slots = Set::extract ('/GameSlot/id', $division['Game']);
					if ($this->Division->Game->GameSlot->updateAll (array('game_id' => null), array('GameSlot.id' => $unused_slots))) {
						$this->Session->setFlash(__('Games rescheduled', true), 'default', array('class' => 'success'));
						$transaction->commit();
						$this->redirect (array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
					} else {
						$this->Session->setFlash(__('Problem! Games were rescheduled, but old game slots were not freed. Schedule will be unstable!', true), 'default', array('class' => 'error'));
					}
				}
			}
			// Failure flash message will have been set by whatever failed
		}

		// Find the list of available dates for scheduling this division
		$dates = $this->Division->DivisionGameslotAvailability->find('all', array(
				'conditions' => array(
					'GameSlot.game_date >' => $date,
					'GameSlot.game_id' => null,
					'DivisionGameslotAvailability.division_id' => $id,
				),
				'fields' => 'DISTINCT UNIX_TIMESTAMP(GameSlot.game_date) AS date',
				'order' => 'GameSlot.game_date',
		));
		if (count($dates) == 0) {
			$this->Session->setFlash(sprintf (__('Sorry, there are no %s available for your division. Check that %s have been allocated before attempting to proceed.', true), Configure::read('sport.fields'), Configure::read('sport.fields')), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
		}
		$dates = Set::extract ('/0/date', $dates);

		$this->set(compact('id', 'division', 'date', 'dates'));
		$this->_addDivisionMenuItems ($division['Division'], $division['League']);
	}

	function publish() {
		$id = $this->_arg('division');
		$date = $this->_arg('date');

		$this->Division->Game->contain (array (
			'GameSlot',
		));
		$games = Set::extract ('/Game/id', $this->Division->Game->find ('all', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => 'Game.id',
		)));

		if ($this->Division->Game->updateAll (
			array('published' => 1),
			array('Game.id' => $games)
		))
		{
			$this->Session->setFlash(__('Published games on the requested date.', true), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(__('Failed to publish games on the requested date.', true), 'default', array('class' => 'warning'));
		}

		$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
	}

	function unpublish() {
		$id = $this->_arg('division');
		$date = $this->_arg('date');

		$this->Division->Game->contain (array (
			'GameSlot',
		));
		$games = Set::extract ('/Game/id', $this->Division->Game->find ('all', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'GameSlot.game_date' => $date,
				),
				'fields' => 'Game.id',
		)));

		if ($this->Division->Game->updateAll (
			array('published' => 0),
			array('Game.id' => $games)
		))
		{
			$this->Session->setFlash(__('Unpublished games on the requested date.', true), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(__('Failed to unpublish games on the requested date.', true), 'default', array('class' => 'warning'));
		}

		$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
	}
}
?>
