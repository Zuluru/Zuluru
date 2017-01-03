<?php
class SchedulesController extends AppController {

	var $name = 'Schedules';
	var $uses = array('Division');
	var $helpers = array('ZuluruGame');
	var $components = array('Lock');

	var $numTeams = null;
	var $pool = null;

	function publicActions() {
		return array('today', 'day');
	}

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
			if ($division && in_array ($division, $this->UserCache->read('DivisionIDs'))) {
				return true;
			}

			// If a division id is specified, check if we're a manager of that division's affiliate
			if ($this->is_manager && $division) {
				if (in_array($this->Division->affiliate($division), $this->UserCache->read('ManagedAffiliateIDs'))) {
					return true;
				}
			}

			// If a league id is specified, check if we're a coordinator of all divisions in that league
			$league = $this->_arg('league');
			if ($league && $this->Division->League->is_coordinator($league)) {
				return true;
			}
		}

		return false;
	}

	function add() {
		$id = $this->_arg('division');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}

		$this->Division->contain (array (
			'Team' => array('order' => 'Team.name'),
			'League',
			'Pool' => array(
				'order' => 'Pool.id',
				'PoolsTeam' => array(
					'order' => 'PoolsTeam.id',
				),
			),
		));
		$this->division = $this->Division->read(null, $id);
		if (!$this->division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		if ($this->division['Division']['schedule_type'] == 'none') {
			$this->Session->setFlash(__('This division\'s "schedule type" is set to "none", so no games can be added.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}
		$this->Configuration->loadAffiliate($this->division['League']['affiliate_id']);

		if ($this->_arg('playoff') ||
			($this->division['Division']['schedule_type'] != 'tournament' && $this->_unscheduledPools($id)))
		{
			$this->league_obj = $this->_getComponent ('LeagueType', 'tournament', $this);
			$this->set('playoff', true);
		} else {
			$this->league_obj = $this->_getComponent ('LeagueType', $this->division['Division']['schedule_type'], $this);
		}

		Configure::load("sport/{$this->division['League']['sport']}");

		$this->set(array('id' => $id, 'division' => $this->division));
		$this->_addDivisionMenuItems ($this->division['Division'], $this->division['League']);

		if ($this->_numTeams() < 2) {
			$this->Session->setFlash(__('Cannot schedule games in a division with less than two teams.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		// Non-tournament divisions must currently have even # of teams for scheduling unless the exclude_teams flag is set
		if ($this->_numTeams() % 2 && !$this->division['Division']['exclude_teams'] &&
			$this->division['Division']['schedule_type'] != 'tournament' && !$this->_arg('playoff') && !$this->pool)
		{
			// TODO: Embed a link to "edit your division" into this, in a way that doesn't break i18n
			$this->Session->setFlash(__('Must currently have an even number of teams in your division. ' . 
				'If you need a bye, please create a team named Bye and add it to your division. ' .
				'Otherwise, edit your division and set the "exclude teams" flag.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		if (!empty ($this->data)) {
			$step = $this->data['Game']['step'];
		} else {
			// What's the default first step?
			if ($this->_arg('playoff') || $this->division['Division']['schedule_type'] == 'tournament') {
				$step = 'pools';
			} else if ($this->division['Division']['exclude_teams']) {
				$step = 'exclude';
			} else {
				$step = 'type';
			}
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
						!empty($this->data['ExcludeTeams']) ? count($this->data['ExcludeTeams']) : 'no',
						$this->_numTeams()), 'default', array('class' => 'info'));
			} else {
				return $this->_type($id);
			}
		}
		$this->render('exclude');
	}

	function _pools($id) {
		if ($this->_unscheduledPools($id)) {
			return $this->_type($id);
		}

		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$stage = max($stages) + 1;
		} else {
			$stage = 1;
		}
		$types = $this->league_obj->poolOptions($this->_numTeams(), $stage);

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'pools') {
			if (!array_key_exists ($this->data['Game']['pools'], $types)) {
				$this->Session->setFlash(__('Select the number of pools to add.', true), 'default', array('class' => 'info'));
			} else if ($this->data['Game']['pools'] == 'crossover') {
				return $this->_crosscount($id);
			} else {
				return $this->_details($id);
			}
		}

		$this->set(compact('types', 'stage'));
		$this->render('pools');
	}

	function _crosscount($id) {
		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'crosscount') {
			list($type, $pools) = explode('_', $this->data['Game']['pools']);
			for ($i = 1, $name = 'A'; $i <= $pools; ++ $i, ++ $name) {
				$this->data['Game']['name'][$i] = "X$name";
				$this->data['Game']['count'][$i] = 2;
			}
			$this->_reseed($id);
		}

		$this->set('teams', $this->_numTeams());
		$this->render('crossover');
	}

	function _details($id) {
		list($type, $pools) = explode('_', $this->data['Game']['pools']);
		if ($pools == 1) {
			$this->data['Game']['name'] = array('A');
			$this->data['Game']['count'] = array($this->_numTeams());
			$func = "_$type";
			$this->$func($id);
		}

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'details') {
			$proceed = true;

			if ($type != 'snake' && array_sum($this->data['Game']['count']) != $this->_numTeams()) {
				$proceed = false;
				$this->Session->setFlash(sprintf(__('Number of teams must add up to %d.', true), $this->_numTeams()), 'default', array('class' => 'info'));
			}

			for ($i = 1; $i <= $pools; ++ $i) {
				if (empty($this->data['Game']['name'][$i])) {
					$proceed = false;
					$this->Session->setFlash(__('Pool names cannot be empty.', true), 'default', array('class' => 'info'));
				} else if (!preg_match("/^[A-Z]+$/i", $this->data['Game']['name'][$i])) {
					$proceed = false;
					$this->Session->setFlash(__('Pool names can only include letters.', true), 'default', array('class' => 'info'));
				} else if (strlen($this->data['Game']['name'][$i]) > 2) {
					$proceed = false;
					$this->Session->setFlash(__('Pool names can be no longer than two letters.', true), 'default', array('class' => 'info'));
				} else if ($type != 'snake') {
					if ($this->data['Game']['count'][$i] < 2) {
						$proceed = false;
						$this->Session->setFlash(__('Pools cannot have less than 2 teams.', true), 'default', array('class' => 'info'));
					} else if ($this->data['Game']['count'][$i] > 11) {
						$proceed = false;
						$this->Session->setFlash(__('Pools cannot have more than 11 teams.', true), 'default', array('class' => 'info'));
					}
				}
			}
			if ($proceed) {
				$func = "_$type";
				$this->$func($id);
			}
		}

		$size = floor($this->_numTeams() / $pools);
		$sizes = array_fill(1, $pools, $size);
		$r = $this->_numTeams() % $pools;
		for ($i = 1; $i <= $r; ++ $i) {
			++ $sizes[$i];
		}

		$existing_names = Set::extract('/Pool[type!=crossover]/name', $this->division);
		if (!empty($existing_names)) {
			$name = max($existing_names);
			++ $name;
		} else {
			$name = 'A';
		}

		$this->set(compact('type', 'pools', 'sizes', 'name'));
		$this->render('details');
	}

	function _seeded($id) {
		$save = array();
		$seed = 1;

		foreach ($this->data['Game']['name'] as $key => $name) {
			$save[$key] = array(
				'Pool' => array(
					'division_id' => $id,
					'name' => $name,
					'stage' => 1,	// Seeded split is only an option for stage 1
					'type' => 'seeded',
				),
				'PoolsTeam' => array(),
			);

			for ($i = 1; $i <= $this->data['Game']['count'][$key]; ++ $i) {
				$save[$key]['PoolsTeam'][] = array(
					'alias' => "{$this->data['Game']['name'][$key]}$i",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				);
			}
		}

		$this->_savePools($id, $save);
	}

	function _snake($id) {
		$save = array();

		foreach ($this->data['Game']['name'] as $key => $name) {
			$save[$key] = array(
				'Pool' => array(
					'division_id' => $id,
					'name' => $name,
					'stage' => 1,	// Snake seeding is only an option for stage 1
					'type' => 'snake',
				),
				'PoolsTeam' => array(),
			);
		}

		$num_teams = $this->_numTeams();
		$pools = count($save);
		$seed = 1;
		for ($tier = 1; $seed <= $num_teams; ++ $tier) {
			for ($pool = 1; $pool <= $pools; ++ $pool) {
				if ($seed > $num_teams) {
					break;
				}
				$save[$pool]['PoolsTeam'][] = array(
					'alias' => "{$this->data['Game']['name'][$pool]}$tier",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				);
			}
			++ $tier;
			for ($pool = $pools; $pool > 0; -- $pool) {
				if ($seed > $num_teams) {
					break;
				}
				$save[$pool]['PoolsTeam'][] = array(
					'alias' => "{$this->data['Game']['name'][$pool]}$tier",
					'dependency_type' => 'seed',
					'dependency_id' => $seed++,
				);
			}
		}

		$this->_savePools($id, $save);
	}

	function _reseed($id) {
		$options = $valid_options = $pool_sizes = $ordinal_counts = $save = array();
		list($type, $pools) = explode('_', $this->data['Game']['pools']);

		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$last_stage = max($stages);
		} else {
			$last_stage = 0;
		}
		$this_stage = $last_stage + 1;

		// Check if the previous stage was crossovers
		$crossovers = Set::extract("/Pool[type=crossover][stage=$last_stage]", $this->division);
		$crossover_names = Set::extract('/Pool/name', $crossovers);
		if (!empty($crossovers)) {
			$crossover_stage = $last_stage;
			-- $last_stage;
		} else {
			$crossover_stage = 0;
		}

		// List of finishing options for each pool
		foreach ($this->division['Pool'] as $pool) {
			if ($pool['stage'] == $last_stage) {
				$group = "Pool {$pool['name']}";
				$pool_sizes[] = count($pool['PoolsTeam']);
				for ($i = 1; $i <= count($pool['PoolsTeam']); ++ $i) {
					$in_crossover = Set::extract("/Pool/PoolsTeam[dependency_pool_id={$pool['id']}][dependency_id=$i]", $crossovers);
					if (empty($in_crossover)) {
						$key = "{$pool['name']}-$i";
						$options[$group][$key] = ordinal($i) . " ($key)";
						if (!array_key_exists($i, $ordinal_counts)) {
							$ordinal_counts[$i] = 1;
						} else {
							++ $ordinal_counts[$i];
						}
					}
				}
				if (!empty($options[$group])) {
					$valid_options = array_merge($valid_options, $options[$group]);
				}
			}
		}

		// List of finishing options between pools
		for ($ordinal = 1; $ordinal <= max($pool_sizes); ++ $ordinal) {
			if (array_key_exists($ordinal, $ordinal_counts)) {
				$group = ordinal($ordinal) . ' ' . __('place teams', true);

				// List of finishing options for each pool
				for ($i = 1; $i <= $ordinal_counts[$ordinal]; ++ $i) {
					$in_crossover = Set::extract("/Pool/PoolsTeam[dependency_ordinal=$ordinal][dependency_id=$i]", $crossovers);
					if (empty($in_crossover)) {
						$key = "$ordinal-$i";
						$options[$group][$key] = ordinal($i) . " ($key)";
					}
				}

				$valid_options = array_merge($valid_options, $options[$group]);
			}
		}

		// Add any crossovers
		foreach ($crossovers as $crossover) {
			$options['Crossovers']["{$crossover['Pool']['name']}-1"] = "Winner of {$crossover['Pool']['name']}";
			$options['Crossovers']["{$crossover['Pool']['name']}-2"] = "Loser of {$crossover['Pool']['name']}";
			$valid_options = array_merge($valid_options, $options['Crossovers']);
		}

		// Validate any data posted to us, building the data to save as we go
		if ($this->data['Game']['step'] == 'reseed') {
			$proceed = true;

			// Check that no qualifier was chosen twice
			$qualifiers = $qualifier_type = array();
			foreach ($this->data['Game']['name'] as $key => $name) {
				$qualifiers = array_merge($qualifiers, $this->data['Game'][$name]);
				$save[$key] = array(
					'Pool' => array(
						'division_id' => $id,
						'name' => $name,
						'stage' => $this_stage,
						'type' => ($type == 'crossover' ? 'crossover' : 'power'),
					),
					'PoolsTeam' => array(),
				);

				foreach ($this->data['Game'][$name] as $qkey => $qualifier) {
					// Make sure that some weird option didn't get selected.
					if (!array_key_exists($qualifier, $valid_options)) {
						$this->Session->setFlash(sprintf(__('Invalid qualifier %s.', true), $qualifier), 'default', array('class' => 'info'));
						$proceed = false;
						break;
					}

					// Make sure that we haven't got both pool and ordinal types selected
					// for any particular "tier" in the pools. For example, A-1 can be used
					// with B-1, but not with 1-1. Crossovers can be used with either.
					list ($pool, $pos) = explode('-', $qualifier);
					$numeric = is_numeric($pool);
					if (!in_array($pool, $crossover_names)) {
						if (array_key_exists($pos, $qualifier_type)) {
							if ($qualifier_type[$pos]['value'] != $numeric) {
								$this->Session->setFlash(sprintf(__('You have selected %s and %s, but you cannot mix "pool"-type options with "ordinal"-type options; both could end up being the same team.', true), $qualifier_type[$pos]['qualifier'], $qualifier), 'default', array('class' => 'info'));
								$proceed = false;
								break;
							}
						} else {
							$qualifier_type[$pos] = array('qualifier' => $qualifier, 'value' => $numeric);
						}
					}

					if ($numeric) {
						$save[$key]['PoolsTeam'][] = array(
							'alias' => "$name$qkey",
							'dependency_type' => 'ordinal',
							'dependency_ordinal' => $pool,
							'dependency_id' => $pos,
						);
					} else {
						$pool_id = current(Set::extract("/Pool[name=$pool]/id", $this->division));
						$save[$key]['PoolsTeam'][] = array(
							'alias' => "$name$qkey",
							'dependency_type' => 'pool',
							'dependency_pool_id' => $pool_id,
							'dependency_id' => $pos,
						);
					}
				}
			}

			if (array_sum($this->data['Game']['count']) != count($qualifiers) || in_array('', $qualifiers)) {
				$this->Session->setFlash(__('You must select a qualifier for each slot.', true), 'default', array('class' => 'info'));
				$proceed = false;
			} else if (array_sum($this->data['Game']['count']) != count(array_unique($qualifiers))) {
				$this->Session->setFlash(__('You must select a unique qualifier for each slot.', true), 'default', array('class' => 'info'));
				$proceed = false;
			}

			if ($proceed) {
				$this->_savePools($id, $save);
			}
		}

		$this->set(compact('type', 'options'));
		$this->render('reseed');
	}

	function _savePools($id, $save) {
		if (!$this->Lock->lock ('scheduling', $this->division['League']['affiliate_id'], 'schedule creation or edit')) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		// saveAll handles hasMany relations OR multiple records, but not both,
		// so we have to save each pool separately. Wrap the whole thing in a
		// transaction, for safety.
		$transaction = new DatabaseTransaction($this->Division->Pool);
		$success = true;
		foreach ($save as $pool) {
			$success &= $this->Division->Pool->saveAll($pool);
		}

		if ($success && $transaction->commit() !== false) {
			$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('pools', true)), 'default', array('class' => 'success'));
			$this->Lock->unlock();
			$this->redirect(array('controller' => 'schedules', 'action' => 'add', 'division' => $id));
		} else {
			$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('game slots', true)), 'default', array('class' => 'warning'));
			$this->Lock->unlock();
		}
	}

	function _type($id) {
		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$stage = max($stages);
		} else {
			$stage = 0;
		}

		if ($this->pool['Pool']['type'] == 'crossover') {
			$types = array('crossover' => 'crossover game');
		} else {
			$types = $this->league_obj->scheduleOptions($this->_numTeams(), $stage);
		}

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'type') {
			if (!array_key_exists ($this->data['Game']['type'], $types)) {
				$this->Session->setFlash(__('Select the type of game or games to add.', true), 'default', array('class' => 'info'));
			} else {
				return $this->_date($id);
			}
		}

		$this->set(compact('types', 'stage'));
		$this->render('type');
	}

	function _date($id) {
		$preview = $this->league_obj->schedulePreview ($this->data['Game']['type'], $this->_numTeams(), $this->pool);
		if (empty($preview)) {
			$field = 'DISTINCT GameSlot.game_date AS date';
			$extract = 'GameSlot';
		} else {
			$field = 'DISTINCT CONCAT(GameSlot.game_date, " ", GameSlot.game_start) AS date';
			$extract = '0';
		}

		// Find the list of available dates for scheduling this division
		$conditions = array(
			'DivisionGameslotAvailability.division_id' => $id,
		);
		if (empty($this->data['Game']['past'])) {
			$conditions[] = 'GameSlot.game_date >= CURDATE()';
		}
		if (empty($this->data['Game']['double_booking'])) {
			$conditions['GameSlot.assigned'] = false;
		}
		$dates = $this->Division->DivisionGameslotAvailability->find('all', array(
				'conditions' => $conditions,
				'fields' => $field,
				'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
		));

		if (count($dates) == 0 && !Configure::read('feature.allow_past_games')) {
			$this->Session->setFlash(sprintf(__('Sorry, there are no %s available for your division. Check that %s have been allocated before attempting to proceed.', true), Configure::read('sport.fields'), Configure::read('sport.fields')), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}
		$dates = Set::extract ("/$extract/date", $dates);

		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$stage = max($stages);
		} else {
			$stage = 0;
		}

		// Validate any data posted to us
		if ($this->data['Game']['step'] == 'date') {
			if ($this->_canSchedule($id, $stage)) {
				return $this->_confirm($id);
			}
		}

		$num_fields = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams());
		$desc = $this->league_obj->scheduleDescription ($this->data['Game']['type'], $this->_numTeams(), $stage);

		$this->set(compact('dates', 'num_fields', 'desc', 'preview'));
		$this->render('date');
	}

	function _confirm($id) {
		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$stage = max($stages);
		} else {
			$stage = 0;
		}

		if (!$this->_canSchedule($id, $stage)) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		$this->set(array(
				'desc' => $this->league_obj->scheduleDescription($this->data['Game']['type'], $this->_numTeams(), $stage),
				'start_date' => $this->data['Game']['start_date'],
		));
		$this->render('confirm');
	}

	function _finalize($id) {
		if (!$this->Lock->lock ('scheduling', $this->division['League']['affiliate_id'], 'schedule creation or edit')) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		$stages = Set::extract('/Pool/stage', $this->division);
		if (!empty($stages)) {
			$stage = max($stages);
		} else {
			$stage = 0;
		}

		if (!$this->_canSchedule($id, $stage)) {
			$this->Lock->unlock();
			$this->redirect(array('controller' => 'divisions', 'action' => 'view', 'division' => $id));
		}

		$exclude_teams = array();
		if (array_key_exists ('ExcludeTeams', $this->data)) {
			$exclude_teams = array_keys($this->data['ExcludeTeams']);
		}

		if ($this->league_obj->createSchedule($id, $exclude_teams, $this->data['Game'], $this->pool)) {
			Cache::delete('division/' . intval($id) . '/standings', 'long_term');
			Cache::delete('division/' . intval($id) . '/schedule', 'long_term');
			Cache::delete('league/' . $this->Division->league($id) . '/standings', 'long_term');
			Cache::delete('league/' . $this->Division->league($id) . '/schedule', 'long_term');

			$this->Lock->unlock();

			if ($this->_unscheduledPools($id)) {
				return $this->_type($id);
			}

			$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
		}

		$this->Lock->unlock();

		// The reason for failure will have been set in the flash somewhere in createSchedule.
		$this->set(array(
				'desc' => $this->league_obj->scheduleDescription($this->data['Game']['type'], $this->_numTeams(), $stage),
				'start_date' => $this->data['Game']['start_date'],
		));
		$this->render('confirm');
	}

	function _canSchedule($id, $stage) {
		if (is_array($this->data['Game']['start_date'])) {
			list ($start_date, $x) = explode(' ', min($this->data['Game']['start_date']));
		} else {
			$start_date = $this->data['Game']['start_date'];
		}

		$this->Division->contain('League');
		$division = $this->Division->read(null, $id);
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);

		$this->Division->Game->contain ('GameSlot');
		$games = $this->Division->Game->find ('count', array(
				'conditions' => array(
					'Game.division_id' => $id,
					'GameSlot.game_date' => $start_date,
				),
		));

		$conditions = array(
			'GameSlot.game_date >=' => $start_date,
			'DivisionGameslotAvailability.division_id' => $id,
		);
		if (empty($this->data['Game']['double_booking'])) {
			$conditions['GameSlot.assigned'] = false;
		}
		$field_counts = $this->Division->DivisionGameslotAvailability->find('all', array(
				'fields' => array('count(GameSlot.id) AS count'),
				'conditions' => $conditions,
				'group' => array('GameSlot.game_date', 'GameSlot.game_start'),
				'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
		));

		if ($this->data['Game']['double_booking']) {
			// If double-booking is allowed, we only need a single field availabile
			return ($field_counts > 0);
		}

		$num_fields = $this->league_obj->scheduleRequirements ($this->data['Game']['type'], $this->_numTeams());
		return $this->league_obj->canSchedule($num_fields, $field_counts);
	}

	function _numTeams() {
		if ($this->numTeams === null) {
			if (!empty($this->data['Game']['pool_id'])) {
				$this->Division->Pool->contain ('PoolsTeam');
				$this->pool = $this->Division->Pool->read(null, $this->data['Game']['pool_id']);
			}
			if (isset($this->pool)) {
				$this->numTeams = count($this->pool['PoolsTeam']);
			} else {
				$this->numTeams = count($this->division['Team']);
				if (is_array($this->data) && array_key_exists ('ExcludeTeams', $this->data)){
					$this->numTeams -= count($this->data['ExcludeTeams']);
				}
			}
		}

		return $this->numTeams;
	}

	function _unscheduledPools($id) {
		// Check if we have any pools defined without games
		foreach ($this->division['Pool'] as $pool) {
			if ($pool['id'] > $this->pool['Pool']['id']) {
				$pool_team_ids = Set::extract('/PoolsTeam/id', $pool);
				$pool_games = $this->Division->Game->find('count', array(
						'contain' => array(),
						'conditions' => array(
							'Game.division_id' => $id,
							'OR' => array(
								array(
									'Game.home_dependency_type' => 'pool',
									'Game.home_pool_team_id' => $pool_team_ids,
								),
								array(
									'Game.away_dependency_type' => 'pool',
									'Game.away_pool_team_id' => $pool_team_ids,
								),
							),
						),
				));
				if (!$pool_games) {
					$this->set(compact('pool'));
					// The format of the data in this kind of read is different from the other kind of read...
					$this->pool = array(
						'Pool' => $pool,
						'PoolsTeam' => $pool['PoolsTeam'],
					);
					$this->numTeams = count($pool['PoolsTeam']);
					return true;
				}
			}
		}
	}

	function delete() {
		$division_id = $this->_arg('division');
		if (!$division_id) {
			$league_id = $this->_arg('league');
			if (!$league_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			$this->Division->League->contain(array('Division' => array('Day' => array('order' => 'day_id'))));
			$league = $this->Division->League->read(null, $league_id);
			if (!$league) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			if (empty($league['Division'])) {
				$this->Session->setFlash(__('This league has no divisions yet.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			$divisions = Set::extract('/Division/id', $league);

			$multi_day = (count(array_unique(Set::extract('/Division[schedule_type!=tournament]/Day/id', $league))) > 1);

			$this->Configuration->loadAffiliate($league['League']['affiliate_id']);
			$this->_addLeagueMenuItems ($league['League']);
		} else {
			$this->Division->contain (array (
				'Day' => array('order' => 'day_id'),
				'Team' => array('order' => 'Team.name'),
				'League',
				'Pool' => array(
					'order' => 'Pool.id',
					'PoolsTeam' => array(
						'order' => 'PoolsTeam.id',
					),
				),
			));
			$division = $this->Division->read(null, $division_id);
			if (!$division) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}
			$divisions = array($division['Division']['id']);
			$league_id = $division['Division']['league_id'];

			$multi_day = ($division['Division']['schedule_type'] != 'tournament' && count($division['Day']) > 1);

			$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
			$this->_addDivisionMenuItems ($division['Division'], $division['League']);
		}
		$this->set(compact('division_id', 'division', 'league_id', 'league'));

		$date = $this->_arg('date');
		$pool_id = $this->_arg('pool');

		$conditions = array(
			'Game.division_id' => $divisions,
		);
		if ($date) {
			if ($multi_day) {
				$first_day = Configure::read('organization.first_day');
				$offset = (6 + $first_day - date('N', strtotime($date))) % 7;
				$conditions['GameSlot.game_date >='] = $date;
				$conditions[] = "GameSlot.game_date <= DATE_ADD('$date', INTERVAL $offset DAY)";
			} else {
				$conditions['GameSlot.game_date'] = $date;
			}
			$contain = array('GameSlot');
		}
		if ($pool_id) {
			$conditions['Game.pool_id'] = $pool_id;
			$contain = array();
			$this->Division->Pool->contain();
			$pool = $this->Division->Pool->read(null, $pool_id);
		}
		$games = $this->Division->Game->find ('all', array(
				'conditions' => $conditions,
				'contain' => $contain,
		));

		$pools = array_unique(Set::extract('/Game/pool_id', $games));
		if (!empty($pools)) {
			$reset_pools = $pools;

			if ($date) {
				$same_pool = $this->Division->Game->find ('all', array(
						'conditions' => array(
							'Game.pool_id' => $pools,
							'GameSlot.game_date !=' => $date,
						),
						'fields' => array('Game.id', 'Game.published', 'Game.home_score', 'Game.pool_id', 'Game.game_slot_id'),
						'contain' => array('GameSlot'),
				));
			}

			$stages = $this->Division->Pool->find ('list', array(
					'conditions' => array(
						'Pool.id' => $pools,
					),
					'fields' => array('Pool.id', 'Pool.stage'),
					'contain' => array(),
			));

			if (!empty($stages)) {
				$later_pools = $this->Division->Pool->find ('list', array(
						'conditions' => array(
							'Pool.division_id' => $divisions,
							'Pool.stage >' => max($stages),
						),
						'fields' => array('Pool.id', 'Pool.id'),
						'contain' => array(),
				));

				if (!empty($later_pools)) {
					$reset_pools = array_merge($reset_pools, $later_pools);

					$dependent = $this->Division->Game->find ('all', array(
							'conditions' => array(
								'Game.pool_id' => $later_pools,
							),
							'fields' => array('Game.id', 'Game.published', 'Game.home_score', 'Game.pool_id', 'Game.game_slot_id'),
							'contain' => array(),
					));
				}
			}
		}

		if ($this->_arg('confirm')) {
			// Wrap the whole thing in a transaction, for safety.
			$transaction = new DatabaseTransaction($this->Division->Game);

			// Reset dependencies for affected pools
			if (!empty($reset_pools)) {
				$this->Division->Pool->PoolsTeam->updateAll (array('team_id' => null), array('pool_id' => $reset_pools));
			}

			// Clear assigned flag from game_slots, and delete the games.
			$game_ids = Set::extract ('/Game/id', $games);
			$slot_ids = Set::extract ('/Game/game_slot_id', $games);
			if (!empty($same_pool)) {
				$game_ids = array_merge($game_ids, Set::extract ('/Game/id', $same_pool));
				$slot_ids = array_merge($slot_ids, Set::extract ('/Game/game_slot_id', $same_pool));
			}
			if (!empty($dependent)) {
				$game_ids = array_merge($game_ids, Set::extract ('/Game/id', $dependent));
				$slot_ids = array_merge($slot_ids, Set::extract ('/Game/game_slot_id', $dependent));
			}
			if ($this->Division->Game->GameSlot->updateAll (array('assigned' => 0), array(
					'GameSlot.id' => $slot_ids,
				)) &&
				$this->Division->Game->deleteAll(array(
					'Game.id' => $game_ids,
				), false))
			{
				if ($date) {
					$this->Session->setFlash(__('Deleted games on the requested date.', true), 'default', array('class' => 'success'));
				} else {
					$this->Session->setFlash(__('Deleted games from the requested pool.', true), 'default', array('class' => 'success'));
				}
				$transaction->commit();

				foreach ($divisions as $id) {
					Cache::delete("division/$id/standings", 'long_term');
					Cache::delete("division/$id/schedule", 'long_term');
				}
				Cache::delete("league/$league_id/standings", 'long_term');
				Cache::delete("league/$league_id/schedule", 'long_term');

				if (isset($league)) {
					$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league_id));
				} else {
					$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division_id));
				}
			} else {
				$this->Session->setFlash(__('Failed to delete games on the requested date.', true), 'default', array('class' => 'warning'));
			}
		}

		$this->set (compact ('date', 'pool_id', 'pool', 'games', 'same_pool', 'dependent'));
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
			'Team' => array(
				'Facility',
			),
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
						'assigned' => false,
					),
					'Field' => 'Facility',
				),
			),
		));
		$division = $this->Division->read(null, $id);
		if (!$division) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($division['League']['affiliate_id']);
		// TODO: The read will load a bunch of games with empty game slots because
		// they don't match the provided date; need a custom join?
		$division['Game'] = Set::extract ("/GameSlot[game_date=$date]/..", $division['Game']);
		$league_obj = $this->_getComponent ('LeagueType', $division['Division']['schedule_type'], $this);
		$league_obj->division = $division;
		if (!empty ($this->data)) {
			if (!$this->Lock->lock ('scheduling', $division['League']['affiliate_id'], 'schedule creation or edit')) {
				return false;
			}
			if ($league_obj->assignFieldsByPreferences($this->data['new_date'], $division['Game'])) {
				$ret = $this->Division->Game->_saveGames ($league_obj->games, $this->data['publish']);
				if ($ret === true) {
					$unused_slots = Set::extract ('/GameSlot/id', $division['Game']);
					if ($this->Division->Game->GameSlot->updateAll (array('assigned' => 0), array('GameSlot.id' => $unused_slots))) {
						$this->Session->setFlash(__('Games rescheduled', true), 'default', array('class' => 'success'));
						Cache::delete('division/' . intval($id) . '/standings', 'long_term');
						Cache::delete('division/' . intval($id) . '/schedule', 'long_term');
						Cache::delete('league/' . $this->Division->league($id) . '/standings', 'long_term');
						Cache::delete('league/' . $this->Division->league($id) . '/schedule', 'long_term');
						$this->Lock->unlock();
						$this->redirect (array('controller' => 'divisions', 'action' => 'schedule', 'division' => $id));
					} else {
						$this->Session->setFlash(__('Games were rescheduled, but failed to clear unused slots!', true), 'default', array('class' => 'warning'));
					}
				} else {
					$this->Session->setFlash($ret['text'], 'default', array('class' => $ret['class']));
				}
			}
			$this->Lock->unlock();
			// Failure flash message will have been set by whatever failed
		}

		// Find the list of available dates for scheduling this division
		$dates = $this->Division->DivisionGameslotAvailability->find('all', array(
				'conditions' => array(
					'GameSlot.game_date >' => $date,
					'GameSlot.assigned' => false,
					'DivisionGameslotAvailability.division_id' => $id,
				),
				'fields' => 'DISTINCT UNIX_TIMESTAMP(CONCAT(GameSlot.game_date, " ", GameSlot.game_start)) AS date',
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
		$this->_publish(1, 'publish', 'Published');
	}

	function unpublish() {
		$this->_publish(0, 'unpublish', 'Unpublished');
	}

	function _publish($true, $publish, $published) {
		$division_id = $this->_arg('division');
		if (!$division_id) {
			$league_id = $this->_arg('league');
			if (!$league_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			$this->Division->League->contain(array('Division' => array('Day' => array('order' => 'day_id'))));
			$league = $this->Division->League->read(null, $league_id);
			if (!$league) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('league', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			$divisions = Set::extract('/Division/id', $league);
			if (empty($divisions)) {
				$this->Session->setFlash(__('This league has no divisions yet.', true), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			$multi_day = (count(array_unique(Set::extract('/Division[schedule_type!=tournament]/Day/id', $league))) > 1);
		} else {
			$this->Division->contain(array('Day' => array('order' => 'day_id')));
			$division = $this->Division->read(null, $division_id);
			if (!$division) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('division', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'leagues', 'action' => 'index'));
			}

			$divisions = array($division_id);
			$league_id = $division['Division']['league_id'];
			$multi_day = ($division['Division']['schedule_type'] != 'tournament' && count($division['Day']) > 1);
		}
		$date = $this->_arg('date');

		$conditions = array(
				'Game.division_id' => $divisions,
		);
		if ($multi_day) {
			$first_day = Configure::read('organization.first_day');
			$offset = (6 + $first_day - date('N', strtotime($date))) % 7;
			$conditions['GameSlot.game_date >='] = $date;
			$conditions[] = "GameSlot.game_date <= DATE_ADD('$date', INTERVAL $offset DAY)";
		} else {
			$conditions['GameSlot.game_date'] = $date;
		}

		$this->Division->Game->contain (array (
			'GameSlot',
		));
		$games = Set::extract ('/Game/id', $this->Division->Game->find ('all', array(
				'conditions' => $conditions,
				'fields' => 'Game.id',
		)));

		if ($this->Division->Game->updateAll (
			array('published' => $true),
			array('Game.id' => $games)
		))
		{
			foreach ($divisions as $id) {
				Cache::delete("division/$id/standings", 'long_term');
				Cache::delete("division/$id/schedule", 'long_term');
			}
			Cache::delete("league/$league_id/standings", 'long_term');
			Cache::delete("league/$league_id/schedule", 'long_term');

			$this->Session->setFlash(sprintf(__('%s games on the requested date.', true), __($published, true)), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(sprintf(__('Failed to %s games on the requested date.', true), __($publish, true)), 'default', array('class' => 'warning'));
		}

		if ($division_id) {
			$this->redirect(array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division_id));
		} else {
			$this->redirect(array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league_id));
		}
	}

	function today() {
		Configure::write ('debug', 0);
		if ($this->params['url']['ext'] == 'html') {
			$this->layout = 'iframe';
		}

		$games = $this->Division->Game->find('count', array(
				'contain' => array('GameSlot'),
				'conditions' => array(
					'GameSlot.game_date' => date('Y-m-d'),
					'Game.published' => true,
				),
		));
		$this->set(compact('games'));
	}

	function day() {
		if (!empty($this->data)) {
			$date = $this->data['date']['year'] . '-' . $this->data['date']['month'] . '-' . $this->data['date']['day'];
		} else {
			$date = $this->_arg('date');
		}
		if (empty($date)) {
			$date = date('Y-m-d');
		}

		// Hopefully, everything we need is already cached
		$cache_key = "schedule/$date";
		$cached = Cache::read($cache_key, 'long_term');
		if ($cached) {
			$games = $cached;
		}
		if (empty($games)) {
			$affiliates = $this->_applicableAffiliateIDs(true);

			// Find divisions that match the affiliates, and specified date
			$divisions = $this->Division->find('all', array(
					'contain' => array('League'),
					'conditions' => array(
						'League.affiliate_id' => $affiliates,
						'Division.open <=' => $date,
						'Division.close >=' => $date,
					),
			));
			$divisions = Set::extract('/Division/id', $divisions);

			$games = $this->Division->Game->find('all', array(
					'contain' => array(
						'GameSlot' => array('Field' => 'Facility'),
						'Division' => array('League' => 'Affiliate'),
						'ScoreEntry',
						'HomeTeam',
						'HomePoolTeam' => 'DependencyPool',
						'AwayTeam',
						'AwayPoolTeam' => 'DependencyPool',
					),
					'conditions' => array(
						'Division.id' => $divisions,
						'GameSlot.game_date' => $date,
						'Game.published' => true,
						'OR' => array(
							'Game.home_dependency_type !=' => 'copy',
							'Game.home_dependency_type' => null,
						),
					),
			));

			// Sort games by sport, time and field
			usort ($games, array ('Game', 'compareSportDateAndField'));
		}

		$this->set(compact('date', 'games'));
	}

	/**
	 * Override the redirect function; if it's a view and there's only one division, view the league instead
	 */
	function redirect($url = null, $next = null) {
		if (isset($url['action']) && $url['action'] == 'view' && isset($url['controller']) && $url['controller'] == 'divisions') {
			$league = $this->Division->league($url['division']);
			$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
					array('named' => compact('league')));
			if ($division_count == 1) {
				parent::redirect(array('controller' => 'leagues', 'action' => $url['action'], 'league' => $league), $next);
			}
		}
		parent::redirect($url, $next);
	}
}
?>
