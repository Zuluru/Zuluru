<?php
class PeopleController extends AppController {

	var $name = 'People';
	var $uses = array('Person', 'Team', 'Division', 'Group', 'Province', 'Country');
	var $helpers = array('CropImage');
	var $components = array('ImageCrop', 'Lock');
	var $paginate = array('Person' => array());

	function publicActions() {
		return array('cron', 'view', 'tooltip', 'ical',
			// Relative approvals and removals may come from emailed links; people might not be logged in
			'approve_relative', 'remove_relative',
		);
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'search',
				'teams',
				'photo',
				'vcf',
				'document',
				'delete_document',
				'note',
				'delete_note',
				'nominate',
		)))
		{
			return true;
		}

		// People can perform these operations on their own account
		if (in_array ($this->params['action'], array(
				'edit',
				'preferences',
				'add_relative',
				'waivers',
				'photo_upload',
				'photo_resize',
				'document_upload',
				'registrations',
				'credits',
		)))
		{
			// If a player id is specified, check if it's the logged-in user, or a relative
			// If no player id is specified, it's always the logged-in user
			$person = $this->_arg('person');
			$relatives = $this->UserCache->read('RelativeIDs');
			if (!$person || $person == $this->Auth->user('zuluru_person_id') || in_array($person, $relatives)) {
				return true;
			}
		}

		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'index',
					'list_new',
					'statistics',
					'participation',
					'retention',
					'rule_search',
					'league_search',
					'inactive_search',
					'approve_badges',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if (!$affiliate) {
					// If there's no affiliate id, this is a top-level operation that all managers can perform
					return true;
				} else if (in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
					return true;
				}
			}

			if (in_array ($this->params['action'], array(
				'registrations',
				'credits',
			)))
			{
				// If a badge id is specified, check if we're a manager of that badge's affiliate
				// This isn't the real badge id, but the id of the badge/person join table
				$badge = $this->_arg('badge');
				if ($badge) {
					if (in_array($this->Person->BadgesPerson->affiliate($badge), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			if (in_array ($this->params['action'], array(
				'waivers',
				'approve_badge',
				'delete_badge',
			)))
			{
				// If a person id is specified, check if we're a manager of that person's affiliate
				$person = $this->_arg('person');
				if ($person) {
					$intersect = array_intersect($this->UserCache->read('AffiliateIDs', $person), $this->UserCache->read('ManagedAffiliateIDs'));
					if (!empty($intersect)) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$user_model = $this->Auth->authenticate->name;
		$id_field = $this->Auth->authenticate->primaryKey;

		$config = new DATABASE_CONFIG;
		$prefix = $this->Auth->authenticate->tablePrefix;
		if ($this->Auth->authenticate->useDbConfig != 'default') {
			$config_name = $this->Auth->authenticate->useDbConfig;
			$config = $config->$config_name;
			$prefix = "{$config['database']}.$prefix";
		}

		$this->paginate = array(
				'conditions' => array(
					'Affiliate.id' => $affiliates
				),
				'joins' => array(
					array(
						'table' => "{$this->Person->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
					array(
						'table' => "{$this->Person->tablePrefix}affiliates",
						'alias' => 'Affiliate',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'Affiliate.id = AffiliatePerson.affiliate_id',
					),
					array(
						'table' => "$prefix{$this->Auth->authenticate->useTable}",
						'alias' => $user_model,
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => "$user_model.$id_field = Person.user_id",
					),
				),
				'contain' => array(),
				'fields' => array('Person.*', 'Affiliate.*', "$user_model.*"),
				'order' => array('Affiliate.name', 'Person.last_name', 'Person.first_name'),
				'limit' => Configure::read('feature.items_per_page'),
		);
		$this->set('people', $this->paginate());
	}

	function statistics() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$joins = array(
			array(
				'table' => "{$this->Person->tablePrefix}affiliates_people",
				'alias' => 'AffiliatePerson',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'AffiliatePerson.person_id = Person.id',
			),
			array(
				'table' => "{$this->Person->tablePrefix}affiliates",
				'alias' => 'Affiliate',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Affiliate.id = AffiliatePerson.affiliate_id',
			),
		);

		// Get the list of accounts by status
		$status_count = $this->Person->find('all', array(
				'fields' => array(
					'Affiliate.*',
					'Person.status',
					'COUNT(Person.id) AS count',
				),
				'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
				'joins' => $joins,
				'group' => array('AffiliatePerson.affiliate_id', 'Person.status'),
				'order' => array('Affiliate.name', 'Person.status'),
				'recursive' => -1,
		));

		// Get the list of accounts by group
		$group_count = $this->Person->find('all', array(
				'fields' => array(
					'Affiliate.*',
					'Person.group_id',
					'COUNT(Person.id) AS count',
				),
				'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
				'joins' => $joins,
				'group' => array('AffiliatePerson.affiliate_id', 'Person.group_id'),
				'order' => array('Affiliate.name', 'Person.group_id'),
				'recursive' => -1,
		));
		$groups = $this->Person->Group->find('list');

		// Get the list of accounts by gender
		$gender_count = $this->Person->find('all', array(
				'fields' => array(
					'Affiliate.*',
					'Person.gender',
					'COUNT(Person.id) AS count',
				),
				'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
				'joins' => $joins,
				'group' => array('AffiliatePerson.affiliate_id', 'Person.gender'),
				'order' => array('Affiliate.name', 'Person.gender' => 'DESC'),
				'recursive' => -1,
		));

		// Get the list of accounts by age
		if (Configure::read('profile.birthdate')) {
			$age_count = $this->Person->find('all', array(
					'fields' => array(
						'Affiliate.*',
						'FLOOR((YEAR(NOW()) - YEAR(birthdate)) / 5) * 5 AS age_bucket',
						'COUNT(Person.id) AS count',
					),
					'conditions' => array(
						'AffiliatePerson.affiliate_id' => $affiliates,
						array('birthdate !=' => null),
						array('birthdate !=' => '0000-00-00'),
					),
					'joins' => $joins,
					'group' => array('AffiliatePerson.affiliate_id', 'age_bucket'),
					'order' => array('Affiliate.name', 'age_bucket'),
					'recursive' => -1,
			));
		}

		// Get the list of accounts by year started
		if (Configure::read('profile.year_started')) {
			$started_count = $this->Person->find('all', array(
					'fields' => array(
						'Affiliate.*',
						'Person.year_started',
						'COUNT(Person.id) AS count',
					),
					'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
					'joins' => $joins,
					'group' => array('AffiliatePerson.affiliate_id', 'year_started'),
					'order' => array('Affiliate.name', 'year_started'),
					'recursive' => -1,
			));
		}

		// Get the list of accounts by skill level
		if (Configure::read('profile.skill_level')) {
			$skill_count = $this->Person->find('all', array(
					'fields' => array(
						'Affiliate.*',
						'Person.skill_level',
						'COUNT(Person.id) AS count',
					),
					'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
					'joins' => $joins,
					'group' => array('AffiliatePerson.affiliate_id', 'skill_level'),
					'order' => array('Affiliate.name', 'skill_level' => 'DESC'),
					'recursive' => -1,
			));
		}

		// Get the list of accounts by city
		if (Configure::read('profile.addr_city')) {
			$city_count = $this->Person->find('all', array(
					'fields' => array(
						'Affiliate.*',
						'Person.addr_city',
						'COUNT(Person.id) AS count',
					),
					'conditions' => array('AffiliatePerson.affiliate_id' => $affiliates),
					'joins' => $joins,
					'group' => array('AffiliatePerson.affiliate_id', 'addr_city HAVING count > 2'),
					'order' => array('Affiliate.name', 'count' => 'DESC'),
					'recursive' => -1,
			));
		}

		$this->set(compact('status_count', 'groups', 'group_count', 'gender_count',
				'age_count', 'started_count', 'skill_count', 'city_count'));
	}

	function participation() {
		$min = min(
			date('Y', strtotime($this->Person->Registration->Event->field('open', array(), 'open'))),
			$this->Division->League->field('YEAR(open) AS year', array(), 'year')
		);
		$this->set(compact('min'));

		// Check form data
		if (empty ($this->data)) {
			$this->data = array('download' => true);
			return;
		}
		if ($this->data['start'] > $this->data['end']) {
			$this->Session->setFlash(__('End date cannot precede start date', true), 'default', array('class' => 'info'));
			return;
		}

		// Initialize the data structures
		$participation = array();
		$pos = array('captain' => 0, 'player' => 0);
		$seasons = array_fill_keys(Configure::read('options.season'), array(
				'season' => $pos,
				'tournament' => $pos,
		));
		$years = array_fill_keys(range($this->data['start'], $this->data['end']), $seasons);

		$seasons_found = array_fill_keys(Configure::read('options.season'), array(
				'season' => 0,
				'tournament' => 0,
		));

		$captains = Configure::read('privileged_roster_roles');

		$membership_event_list = $this->Person->Registration->Event->find('all', array(
			// TODO: Fix or remove these hard-coded values
			'conditions' => array('event_type_id' => array(1)),
			'contain' => false,
		));
		$event_names = array();

		for ($year = $this->data['start']; $year <= $this->data['end']; ++ $year) {
			$start = "$year-01-01";
			$end = "$year-12-31";

			// We are interested in teams in divisions that operated this year
			$divisions = $this->Division->find('all', array(
				'conditions' => array(
					'Division.open >=' => $start,
					'Division.open <=' => $end,
				),
				'contain' => array(
					'Team' => array(
						'Person' => array('conditions' => array(
							'TeamsPerson.role' => Configure::read('playing_roster_roles'),
							'TeamsPerson.status' => 1,
						)),
					),
					'League',
				),
			));

			// Consolidate the team data into the person-based array
			foreach ($divisions as $division) {
				foreach ($division['Team'] as $team) {
					foreach ($team['Person'] as $person) {
						if (!array_key_exists($person['id'], $participation)) {
							$participation[$person['id']] = array(
								'Person' => $person,
								'Event' => array(),
								'Division' => $years,
							);
						}

						if ($division['Division']['schedule_type'] == 'tournament') {
							$key = 'tournament';
						} else {
							$key = 'season';
						}
						if (in_array($person['TeamsPerson']['role'], $captains)) {
							$pos = 'captain';
						} else {
							$pos = 'player';
						}
						++ $participation[$person['id']]['Division'][$year][$division['League']['season']][$key][$pos];
						$seasons_found[$division['League']['season']][$key] = true;
					}
				}
			}

			// These arrays get big, and we don't need team data any more
			unset ($divisions);

			// We are interested in memberships that covered this year
			$membership_event_ids = array();
			foreach ($membership_event_list as $event) {
				if ($event['Event']['membership_begins'] >= $start &&
					$event['Event']['membership_ends'] <= $end)
				{
					$event_names[$event['Event']['id']] = $event['Event']['name'];
					$membership_event_ids[] = $event['Event']['id'];
				}
			}

			// We are interested in some other registration events that closed this year
			$events = $this->Person->Registration->Event->find('all', array(
				'conditions' => array(
					'OR' => array(
						'Event.id' => $membership_event_ids,
						'AND' => array(
							'Event.close >=' => "$year-01-01",
							'Event.close <' => $year + 1 . '-12-31',
							// TODO: Fix or remove these hard-coded values
							'Event.event_type_id' => array(5,6,7),
						),
					),
				),
				'contain' => array(
					'Registration' => array(
						'Person',
						'conditions' => array('payment' => 'Paid'),
					),
				),
				'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			));

			// Consolidate the registrations into the person-based array
			foreach ($events as $event) {
				$event_names[$event['Event']['id']] = $event['Event']['name'];
				foreach ($event['Registration'] as $registration) {
					if (!array_key_exists($registration['person_id'], $participation)) {
						$participation[$registration['person_id']] = array(
							'Person' => $registration['Person'],
							'Event' => array(),
							'Division' => $years,
						);
					}
					$participation[$registration['person_id']]['Event'][$event['Event']['id']] = true;
				}
			}

			// These arrays get big, and we don't need event data any more
			unset ($events);
		}

		usort ($participation, array('Person', 'comparePerson'));

		if ($this->data['download']) {
			$this->RequestHandler->renderAs($this, 'csv');
			$this->set('download_file_name', 'Participation');
			Configure::write ('debug', 0);
		}

		$this->set(compact('event_names', 'seasons_found', 'participation'));
	}

	function retention() {
		$min = min(
			date('Y', strtotime($this->Person->Registration->Event->field('open', array(), 'open'))),
			$this->Division->League->field('YEAR(open) AS year', array(), 'year')
		);
		$this->set(compact('min'));

		// Check form data
		if (empty ($this->data)) {
			$this->data = array('download' => true);
			return;
		}
		if ($this->data['start'] > $this->data['end']) {
			$this->Session->setFlash(__('End date cannot precede start date', true), 'default', array('class' => 'info'));
			return;
		}

		// We are interested in memberships
		$event_list = $this->Person->Registration->Event->find('all', array(
			// TODO: Fix or remove these hard-coded values
			'conditions' => array('event_type_id' => array(1)),
			'contain' => false,
			'order' => array('Event.open', 'Event.close', 'Event.id'),
		));

		$start = "{$this->data['start']}-01-01";
		$end = "{$this->data['end']}-12-31";

		$past_events = array();
		foreach ($event_list as $key => $event) {
			if ($event['Event']['membership_begins'] < $start ||
				$event['Event']['membership_ends'] > $end)
			{
				unset($event_list[$key]);
				continue;
			}

			foreach (array_keys($past_events) as $past) {
				$this->Person->Registration->unbindModel(array('belongsTo' => array('Person', 'Event'), 'hasMany' => array('Payment')));
				$people = $this->Person->Registration->find('count', array(
						'conditions' => array(
							'Registration.event_id' => $event['Event']['id'],
							'Registration.payment' => 'Paid',
							"Registration.person_id IN (SELECT person_id FROM registrations WHERE event_id = $past)",
						),
				));
				$past_events[$past][$event['Event']['id']] = $people;
			}

			if (!empty($past_events)) {
				$this->Person->Registration->unbindModel(array('belongsTo' => array('Person', 'Event'), 'hasMany' => array('Payment')));
				$event_list[$key]['total'] = $this->Person->Registration->find('count', array(
						'conditions' => array(
							'Registration.event_id' => $event['Event']['id'],
							'Registration.payment' => 'Paid',
							'Registration.person_id IN (SELECT DISTINCT person_id FROM registrations WHERE event_id IN (' . implode(',', array_keys($past_events)) . '))',
						),
				));
			} else {
				$event_list[$key]['total'] = 0;
			}

			$this->Person->Registration->unbindModel(array('belongsTo' => array('Person', 'Event'), 'hasMany' => array('Payment')));
			$event_list[$key]['count'] = $this->Person->Registration->find('count', array(
					'conditions' => array(
						'Registration.event_id' => $event['Event']['id'],
						'Registration.payment' => 'Paid',
					),
			));

			$past_events[$event['Event']['id']] = array();
		}

		// The last past events row will be empty
		array_pop($past_events);

		if ($this->data['download']) {
			$this->RequestHandler->renderAs($this, 'csv');
			$this->set('download_file_name', 'Retention');
			Configure::write ('debug', 0);
		}

		$this->set(compact('event_list', 'past_events'));
	}

	function view() {
		$id = $this->_arg('person');
		$user_id = $this->_arg('user');
		$my_id = $this->Auth->user('zuluru_person_id');

		if ($user_id) {
			$id = $this->Person->field('id', compact('user_id'));
		} else if (!$id) {
			$id = $my_id;
		}
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$group = $this->UserCache->read('Group', $person['id']);
		$teams = $this->UserCache->read('Teams', $person['id']);
		$photo = null;

		if ($this->is_logged_in) {
			$relatives = $this->UserCache->read('Relatives', $person['id']);
			$related_to = $this->UserCache->read('RelatedTo', $person['id']);
			$divisions = $this->UserCache->read('Divisions', $person['id']);
			$waivers = $this->UserCache->read('WaiversCurrent', $person['id']);
			if (Configure::read('feature.registration')) {
				$registrations = array_slice($this->UserCache->read('Registrations', $person['id']), 0, 4);
				$preregistrations = $this->UserCache->read('Preregistrations', $person['id']);
				$credits = $this->UserCache->read('Credits', $person['id']);
			}
			if (Configure::read('scoring.allstars')) {
				// Unfortunate that we have to manually specify the joins, but it seems
				// that it's (currently) the only way to fetch all this data in a
				// single query.
				$allstars = $this->Person->Allstar->find('all', array(
						'contain' => false,
						'conditions' => array(
							'Allstar.person_id' => $id,
							'Division.is_open' => true,
						),
						'order' => 'GameSlot.game_date, GameSlot.game_start',
						'fields' => array(
							'Allstar.id',
							'Game.id',
							'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
							'Facility.id', 'Facility.name', 'Facility.code', 'Field.num',
							'HomeTeam.id', 'HomeTeam.name',
							'AwayTeam.id', 'AwayTeam.name',
							'League.id', 'League.name',
							'Division.id', 'Division.name',
						),
						'joins' => array(
							array(
								'table' => "{$this->Person->Allstar->tablePrefix}games",
								'alias' => 'Game',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'Game.id = Allstar.game_id',
							),
								array(
									'table' => "{$this->Person->Allstar->tablePrefix}game_slots",
									'alias' => 'GameSlot',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'GameSlot.id = Game.game_slot_id',
								),
									array(
										// TODO: something more generic than explicitly escaping the table name
										'table' => "`{$this->Person->Allstar->tablePrefix}fields`",
										'alias' => 'Field',
										'type' => 'LEFT',
										'foreignKey' => false,
										'conditions' => 'Field.id = GameSlot.field_id',
									),
										array(
											'table' => "`{$this->Person->Allstar->tablePrefix}facilities`",
											'alias' => 'Facility',
											'type' => 'LEFT',
											'foreignKey' => false,
											'conditions' => 'Facility.id = Field.facility_id',
										),
								array(
									'table' => "{$this->Person->Allstar->tablePrefix}teams",
									'alias' => 'HomeTeam',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'HomeTeam.id = Game.home_team',
								),
								array(
									'table' => "{$this->Person->Allstar->tablePrefix}teams",
									'alias' => 'AwayTeam',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'AwayTeam.id = Game.away_team',
								),
								array(
									'table' => "{$this->Person->Allstar->tablePrefix}divisions",
									'alias' => 'Division',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'Division.id = Game.division_id',
								),
								array(
									'table' => "{$this->Person->Allstar->tablePrefix}leagues",
									'alias' => 'League',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'League.id = Division.league_id',
								),
						),
				));
			}
			if (Configure::read('feature.photos')) {
				$photo = $this->Person->Upload->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'person_id' => $person['id'],
							'type_id' => null,
							'approved' => true,
						),
				));
			}
			if (Configure::read('feature.documents')) {
				$documents = $this->UserCache->read('Documents', $person['id']);
			}
			if (Configure::read('feature.annotations')) {
				$note = $this->Person->Note->find('first', array(
						'contain' => false,
						'conditions' => array(
							'person_id' => $person['id'],
							'created_person_id' => $my_id,
						),
				));
			}
			if (Configure::read('feature.tasks') && ($id == $my_id || $this->is_admin || $this->is_manager)) {
				$tasks = $this->UserCache->read('Tasks', $person['id']);
			}
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->_getComponent('Badge', '', $this);
				$badge_obj->visibility($this->is_admin || $this->is_manager);

				$this->Person->contain(array(
						'Badge' => array(
							'conditions' => array(
								'BadgesPerson.approved' => true,
								'Badge.visibility' => $badge_obj->getVisibility(),
							),
						),
				));
				$badges = $this->Person->read(null, $person['id']);
				$badge_obj->prepForDisplay($badges);
			}
		}

		$this->set(compact('person', 'group', 'teams', 'relatives', 'related_to', 'divisions', 'waivers', 'registrations', 'preregistrations', 'credits', 'allstars', 'photo', 'documents', 'note', 'tasks', 'badges'));
		$this->set('is_me', ($id === $my_id));
		$this->set($this->_connections($id));
	}

	function tooltip() {
		$id = $this->_arg('person');
		if (!$id) {
			return;
		}

		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			return;
		}
		$photo = null;

		if ($this->is_logged_in) {
			if (Configure::read('feature.photos')) {
				$photo = $this->Person->Upload->find('first', array(
						'contain' => array(),
						'conditions' => array(
							'person_id' => $person['id'],
							'type_id' => null,
							'approved' => true,
						),
				));
			}
			if (Configure::read('feature.annotations') && $this->is_logged_in) {
				$note = $this->Person->Note->find('first', array(
						'contain' => false,
						'conditions' => array(
							'person_id' => $person['id'],
							'created_person_id' => $this->Auth->user('zuluru_person_id'),
						),
				));
			}
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->_getComponent('Badge', '', $this);
				$badge_obj->visibility($this->is_admin || $this->is_manager, BADGE_VISIBILITY_HIGH);

				$this->Person->contain(array(
						'Badge' => array(
							'conditions' => array(
								'BadgesPerson.approved' => true,
								'Badge.visibility' => $badge_obj->getVisibility(),
							),
						),
				));
				$badges = $this->Person->read(null, $person['id']);
				$badge_obj->prepForDisplay($badges);
			}
		}

		$this->set(compact('person', 'photo', 'note', 'badges'));
		$this->set('is_me', ($id === $this->Auth->user('zuluru_person_id')));
		$this->set($this->_connections($id));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function _connections($id) {
		$connections = array();

		// Pull some lists of team and division IDs for later comparisons
		$my_team_ids = $this->UserCache->read('TeamIDs');
		$my_owned_team_ids = $this->UserCache->read('OwnedTeamIDs');
		$my_owned_division_ids = $this->UserCache->read('DivisionIDs');
		$my_captain_division_ids = Set::extract('/Team/division_id', $this->UserCache->read('OwnedTeams'));
		$their_team_ids = $this->UserCache->read('TeamIDs', $id);
		$their_owned_team_ids = $this->UserCache->read('OwnedTeamIDs', $id);
		$their_owned_division_ids = $this->UserCache->read('DivisionIDs', $id);
		$their_captain_division_ids = Set::extract('/Team/division_id', $this->UserCache->read('OwnedTeams', $id));

		// Check if the current user is a captain of a team the viewed player is on
		$on_my_teams = array_intersect ($my_owned_team_ids, $their_team_ids);
		$connections['is_captain'] = !empty ($on_my_teams);

		// Check if the current user is on a team the viewed player is a captain of
		$on_their_teams = array_intersect ($my_team_ids, $their_owned_team_ids);
		$connections['is_my_captain'] = !empty ($on_their_teams);

		// Check if the current user is a coordinator of a division the viewed player is a captain in
		$in_my_divisions = array_intersect ($my_owned_division_ids, $their_captain_division_ids);
		$connections['is_coordinator'] = !empty ($in_my_divisions);

		// Check if the current user is a captain in a division the viewed player is a coordinator of
		$in_their_divisions = array_intersect ($my_captain_division_ids, $their_owned_division_ids);
		$connections['is_my_coordinator'] = !empty ($in_their_divisions);

		// Check if the current user is a captain in a division the viewed player is a captain in
		$captains_in_same_division = array_intersect ($my_captain_division_ids, $their_captain_division_ids);
		$connections['is_division_captain'] = !empty ($captains_in_same_division);

		return $connections;
	}

	function edit() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}
		$is_me = ($id === $this->Auth->user('zuluru_person_id'));
		$this->set(compact('id', 'is_me'));

		$this->_loadAddressOptions();
		$this->_loadGroupOptions();
		$this->_loadAffiliateOptions();

		$sport = reset(array_keys(Configure::read('options.sport')));
		Configure::load("sport/$sport");

		if (!empty($this->data)) {
			$this->data['Person']['complete'] = true;
			$this->Person->create();

			// Handle affiliations for non-admins
			if (!$this->is_admin) {
				if (Configure::read('feature.affiliates')) {
					// Manually select all affiliates the user is a manager of
					if (is_array($this->data['Affiliate']['Affiliate'])) {
						$this->data['Affiliate']['Affiliate'] = array_merge($this->data['Affiliate']['Affiliate'], $this->UserCache->read('ManagedAffiliateIDs'));
					} else if (!empty($this->data['Affiliate']['Affiliate'])) {
						$this->data['Affiliate']['Affiliate'] = array_merge(array($this->data['Affiliate']['Affiliate']), $this->UserCache->read('ManagedAffiliateIDs'));
					} else {
						$this->data['Affiliate']['Affiliate'] = $this->UserCache->read('ManagedAffiliateIDs');
					}

					if (Configure::read('feature.multiple_affiliates')) {
						if (empty($this->data['Affiliate']['Affiliate'])) {
							$this->Person->Affiliate->validationErrors['Affiliate'] = __('You must select at least one affiliate that you are interested in.', true);
						}
					} else {
						if (empty($this->data['Affiliate']['Affiliate']) || count($this->data['Affiliate']['Affiliate']) > 1) {
							$this->Person->Affiliate->validationErrors['Affiliate'] = __('You must select an affiliate that you are interested in.', true);
						}
					}
				} else {
					$this->data['Affiliate']['Affiliate'] = array(1);
				}
			}

			$this->Person->set($this->data);
			if ($this->Person->validates() && $this->Person->Affiliate->validates()) {
				if (!empty($this->data['Affiliate']['Affiliate'])) {
					foreach ($this->data['Affiliate']['Affiliate'] as $key => $affiliate_id) {
						if (in_array($affiliate_id, $this->UserCache->read('ManagedAffiliateIDs'))) {
							$position = 'manager';
						} else {
							unset($position);
						}
						$this->data['Affiliate']['Affiliate'][$key] = compact('affiliate_id', 'position');
					}
				}

				if ($this->Person->saveAll($this->data)) {
					if ($is_me) {
						$this->Session->setFlash(__('Your profile has been saved', true), 'default', array('class' => 'success'));
					} else {
						$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('person', true)), 'default', array('class' => 'success'));
					}

					// There may be callbacks to handle
					$components = Configure::read('callbacks.user');
					foreach ($components as $name => $config) {
						$component = $this->_getComponent('User', $name, $this, false, $config);
						$component->onEdit($this->data['Person']);
					}

					// Delete the cached data, so it's reloaded next time it's needed
					$this->UserCache->clear('Person', $this->data['Person']['id']);
					$this->UserCache->clear('Group', $this->data['Person']['id']);
					$this->UserCache->clear('Affiliates', $this->data['Person']['id']);
					$this->UserCache->clear('AffiliateIDs', $this->data['Person']['id']);

					$this->redirect('/');
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('person', true)), 'default', array('class' => 'warning'));
				}
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('account', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->Person->contain(array('Affiliate', $this->Auth->authenticate->name));
			$this->data = $this->Person->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		if (Configure::read('feature.photos')) {
			$upload = $this->Person->Upload->find('first', array(
					'conditions' => array(
						'person_id' => $id,
						'type_id' => null,
					),
					'contain' => array(),
			));
			if ($upload) {
				$this->data = array_merge($this->data, $upload);
			}
		}

		$this->set(array(
				'user_model' => $this->Auth->authenticate->name,
				'id_field' => $this->Auth->authenticate->primaryKey,
				'user_field' => $this->Auth->authenticate->userField,
				'email_field' => $this->Auth->authenticate->emailField,
		));
	}

	function note() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('id', 'my_id'));

		if (!empty($this->data)) {
			// Check that this user is allowed to edit this note
			if (!empty($this->data['Note'][0]['id'])) {
				$created = $this->Person->Note->field('created_person_id', array('id' => $this->data['Note'][0]['id']));
				if ($created != $my_id) {
					$this->Session->setFlash(sprintf(__('You are not allowed to edit that %s.', true), __('note', true)), 'default', array('class' => 'error'));
					$this->redirect(array('action' => 'view', 'person' => $id));
				}
			}

			$this->data['Note'][0]['person_id'] = $id;
			$this->data['Note'][0]['visibility'] = VISIBILITY_PRIVATE;
			if (empty($this->data['Note'][0]['note'])) {
				if (!empty($this->data['Note'][0]['id'])) {
					if ($this->Person->Note->delete($this->data['Note'][0]['id'])) {
						$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
					} else {
						$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
					}
				} else {
					$this->Session->setFlash(__('You entered no text, so no note was added.', true), 'default', array('class' => 'warning'));
				}
				$this->redirect(array('action' => 'view', 'person' => $id));
			} else if ($this->Person->Note->save($this->data['Note'][0])) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('note', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'person' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('note', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->Person->contain(array(
					'Note' => array('conditions' => array('created_person_id' => $my_id)),
			));

			$this->data = $this->Person->read(null, $id);
		}

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete_note() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$note_id = $this->Person->Note->field('id', array('person_id' => $id, 'created_person_id' => $my_id));
		if (!$note_id) {
			$this->Session->setFlash(sprintf(__('You do not have a note on that %s.', true), __('person', true)), 'default', array('class' => 'warning'));
		} else if ($this->Person->Note->delete($note_id)) {
			$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'person' => $id));
	}

	function preferences() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}
		$this->set(compact('id'));
		$this->set('person', $this->UserCache->read('Person', $id));

		$setting = ClassRegistry::init('Setting');
		if (!empty($this->data)) {
			if ($setting->saveAll ($this->data['Setting'], array('validate' => false))) {
				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				$this->Configuration->load($my_id);
				$this->_setLanguage();
				$this->_initMenu();
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('preferences', true)), 'default', array('class' => 'success'));
			} else {
				$this->Session->setFlash(__('Failed to save your preferences', true), 'default', array('class' => 'warning'));
			}
		}

		$this->data = $setting->find('all', array(
				'conditions' => array('person_id' => $id),
		));
	}

	function add_relative() {
		$person_id = $this->_arg('person');
		$person = $this->UserCache->read('Person', $person_id);
		if (!$person) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('person'));

		$relative_id = $this->_arg('relative');
		if ($relative_id !== null) {
			if ($relative_id == $person_id) {
				$this->Session->setFlash(__('You can\'t add yourself as a relative!', true), 'default', array('class' => 'info'));
			} else {
				$relative = $this->UserCache->read('Person', $relative_id);
				if (!$relative) {
					$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
					$this->redirect('/');
				}

				if (in_array($relative_id, $this->UserCache->read('RelativeIDs', $person_id))) {
					$this->Session->setFlash(sprintf(__('%s is already your relative.', true), $relative['full_name']), 'default', array('class' => 'info'));
				} else {
					if ($this->Person->PeoplePerson->save(compact('person_id', 'relative_id'), array('validate' => false))) {
						$this->set(compact('relative'));
						$this->set('code', $this->_hash (array(
								'id' => $this->Person->PeoplePerson->id,
								'person_id' => $person_id,
								'relative_id' => $relative_id,
								'created' => date('Y-m-d'),
						)));

						if (!$this->_sendMail (array (
								'to' => $relative,
								'replyTo' => $person,
								'subject' => 'You have been added as a relative',
								'template' => 'relative_add',
								'sendAs' => 'both',
						)))
						{
							$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['full_name']), 'default', array('class' => 'error'), 'email');
						}

						$this->UserCache->clear('Relatives', $person_id);
						$this->UserCache->clear('RelativeIDs', $person_id);
						$this->UserCache->clear('RelatedTo', $relative_id);
						$this->UserCache->clear('RelatedToIDs', $relative_id);
						$this->Session->setFlash(sprintf(__('Added %s as relative; you will not have access to their information until they have approved this.', true), $relative['full_name']), 'default', array('class' => 'success'));
						$this->redirect('/');
					} else {
						$this->Session->setFlash(sprintf(__('Failed to add %s as relative.', true), $relative['full_name']), 'default', array('class' => 'warning'));
						$this->redirect(array('action' => 'add_relative', 'person' => $person_id));
					}
				}
			}
		}

		$params = $url = $this->_extractSearchParams();
		unset ($params['person']);
		unset ($params['relative']);
		$this->_handlePersonSearch($params, $url, $this->Person);
	}

	function approve_relative() {
		$person_id = $this->_arg('person');
		$relative_id = $this->_arg('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'person' => $person_id));
		}

		$outstanding = Set::extract("/PeoplePerson[approved=0][person_id=$relative_id]/.", $this->UserCache->read('RelatedTo', $person_id));
		if (empty($outstanding)) {
			$this->Session->setFlash(__('This person does not have an outstanding relative request for you.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'person' => $person_id));
		}
		$outstanding = reset($outstanding);

		// We must do other permission checks here, because we allow non-logged-in users to approve
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($outstanding);
			if ($hash != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'person' => $person_id));
			}
		} else {
			if (!$this->is_admin && $person_id != $this->Auth->user('zuluru_person_id')) {
				$this->Session->setFlash(__('You are not allowed to approve this relative request.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'person' => $person_id));
			}
		}

		$this->Person->PeoplePerson->id = $outstanding['id'];
		if (!$this->Person->PeoplePerson->saveField ('approved', true)) {
			$this->Session->setFlash(__('Failed to approve the relative request!', true), 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'view', 'person' => $person_id));
		}

		$this->UserCache->clear('Relatives', $relative_id);
		$this->UserCache->clear('RelativeIDs', $relative_id);
		$this->UserCache->clear('RelatedTo', $person_id);
		$this->UserCache->clear('RelatedToIDs', $person_id);
		$this->Session->setFlash(__('Approved the relative request.', true), 'default', array('class' => 'success'));

		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);
		$this->set(compact('person', 'relative'));
		if (!$this->_sendMail (array (
				'to' => $relative,
				'replyTo' => $person,
				'subject' => "{$person['full_name']} approved your relative request",
				'template' => 'relative_approve',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
		}

		$this->redirect(array('action' => 'view', 'person' => $person_id));
	}

	function remove_relative() {
		$person_id = $this->_arg('person');
		$relative_id = $this->_arg('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'person' => $person_id));
		}

		$relation = Set::extract("/PeoplePerson[person_id=$relative_id]/.", $this->UserCache->read('RelatedTo', $person_id));
		if (empty($relation)) {
			$relation = Set::extract("/PeoplePerson[relative_id=$relative_id]/.", $this->UserCache->read('Relatives', $person_id));
			if (empty($relation)) {
				$this->Session->setFlash(__('This person is already not related to you.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'person' => $person_id));
			}
		}
		$relation = reset($relation);

		// We must do other permission checks here, because we allow non-logged-in users to remove
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($relation);
			if ($hash != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'person' => $person_id));
			}
		} else {
			if (!$this->is_admin && $person_id != $this->Auth->user('zuluru_person_id')) {
				$this->Session->setFlash(__('You are not allowed to remove this relation.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'person' => $person_id));
			}
		}

		if (!$this->Person->PeoplePerson->delete($relation['id'])) {
			$this->Session->setFlash(__('Failed to remove the relation!', true), 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'view', 'person' => $person_id));
		}

		$this->UserCache->clear('Relatives', $relation['person_id']);
		$this->UserCache->clear('RelativeIDs', $relation['person_id']);
		$this->UserCache->clear('RelatedTo', $relation['relative_id']);
		$this->UserCache->clear('RelatedToIDs', $relation['relative_id']);
		$this->Session->setFlash(__('Removed the relation.', true), 'default', array('class' => 'success'));

		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);
		$this->set(compact('person', 'relative'));
		if (!$this->_sendMail (array (
				'to' => $relative,
				'replyTo' => $person,
				'subject' => "{$person['full_name']} removed your relation",
				'template' => 'relative_remove',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
		}

		$this->redirect(array('action' => 'view', 'person' => $person_id));
	}

	function authorize_twitter() {
		if (!App::import('Lib', 'tmh_oauth')) {
			$this->Session->setFlash(sprintf(__('Failed to load the %s library! Contact your system administrator.', true), 'Twitter OAuth'), 'default', array('class' => 'error'));
			$this->redirect(array('action' => 'preferences'));
		}

		define('__DIR__', ROOT . DS . APP_DIR . DS . 'libs');
		$tmhOAuth = new tmhOAuth(array(
			'consumer_key' => Configure::read('twitter.consumer_key'),
			'consumer_secret' => Configure::read('twitter.consumer_secret'),
		));

		if (!empty($this->params['url']['oauth_token'])) {
			$response = $this->Session->read('Twitter.response');
			$this->Session->delete('Twitter.response');
			if ($this->params['url']['oauth_token'] !== $response['oauth_token']) {
				$this->Session->setFlash(__('The oauth token you started with doesn\'t match the one you\'ve been redirected with. Do you have multiple tabs open?', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'preferences'));
			}

			if (!isset($this->params['url']['oauth_verifier'])) {
				$this->Session->setFlash(__('The oauth verifier is missing so we cannot continue. Did you deny the appliction access?', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'preferences'));
			}

			// Update with the temporary token and secret
			$tmhOAuth->reconfigure(array_merge($tmhOAuth->config, array(
				'token' => $response['oauth_token'],
				'secret' => $response['oauth_token_secret'],
			)));

			$code = $tmhOAuth->user_request(array(
				'method' => 'POST',
				'url' => $tmhOAuth->url('oauth/access_token', ''),
				'params' => array(
					'oauth_verifier' => trim($this->params['url']['oauth_verifier']),
				)
			));

			if ($code == 200) {
				$oauth_creds = $tmhOAuth->extract_params($tmhOAuth->response['response']);
				if ($this->Person->updateAll(array('twitter_token' => "'{$oauth_creds['oauth_token']}'", 'twitter_secret' => "'{$oauth_creds['oauth_token_secret']}'"), array('Person.id' => $this->Auth->user('zuluru_person_id')))) {
					$this->Session->setFlash(sprintf(__('Your Twitter authorization has been completed. You can always revoke this at any time through the preferences page.', true), __('person', true)), 'default', array('class' => 'success'));
				} else {
					$this->Session->setFlash(sprintf(__('Twitter authorization was received, but the database failed to update.', true), __('person', true)), 'default', array('class' => 'warning'));
				}
			} else {
				$this->Session->setFlash(__('There was an error communicating with Twitter.', true) . ' ' . $tmhOAuth->response['response'], 'default', array('class' => 'warning'));
			}
			$this->redirect(array('action' => 'preferences'));
		} else {
			$code = $tmhOAuth->apponly_request(array(
				'without_bearer' => true,
				'method' => 'POST',
				'url' => $tmhOAuth->url('oauth/request_token', ''),
				'params' => array(
					'oauth_callback' => Router::url(Router::normalize($this->here), true),
				),
			));

			if ($code != 200) {
				$this->Session->setFlash(__('There was an error communicating with Twitter.', true) . ' ' . $tmhOAuth->response['response'], 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'preferences'));
			}

			// store the params into the session so they are there when we come back after the redirect
			$response = $tmhOAuth->extract_params($tmhOAuth->response['response']);

			// check the callback has been confirmed
			if ($response['oauth_callback_confirmed'] !== 'true') {
				$this->Session->setFlash(__('The callback was not confirmed by Twitter so we cannot continue.', true) . ' ' . $tmhOAuth->response['response'], 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'preferences'));
			} else {
				$this->Session->write('Twitter.response', $response);
				$this->redirect($tmhOAuth->url('oauth/authorize', '') . "?oauth_token={$response['oauth_token']}");			
			}
		}
	}

	function revoke_twitter() {
		if ($this->Person->updateAll(array('twitter_token' => null, 'twitter_secret' => null), array('Person.id' => $this->Auth->user('zuluru_person_id')))) {
			$this->Session->setFlash(sprintf(__('Your Twitter authorization has been revoked. You can always re-authorize at any time through the preferences page.', true), __('person', true)), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(sprintf(__('Failed to revoke your Twitter authorization.', true), __('person', true)), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'preferences'));
	}

	function photo() {
		if (!Configure::read('feature.photos')) {
			return;
		}

		$file_dir = Configure::read('folders.uploads');
		$photo = $this->Person->Upload->find('first', array(
				'contain' => false,
				'conditions' => array(
					'person_id' => $this->_arg('person'),
					'type_id' => null,
				),
		));
		if (!empty ($photo)) {
			$this->view = 'Media';
			$f = new File($photo['Upload']['filename']);
			$this->set(array(
					'path' => $file_dir . DS,
					'id' => $photo['Upload']['filename'],
					'extension' => $f->ext(),
			));
		}
	}

	function photo_upload() {
		if (!Configure::read('feature.photos')) {
			$this->Session->setFlash(__('Uploading of photos is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$temp_dir = Configure::read('folders.league_base') . DS . 'temp';
		if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
			if ($this->is_admin) {
				$this->Session->setFlash(sprintf(__('Your temp folder %s does not exist or is not writable.', true), $temp_dir), 'default', array('class' => 'error'));
			} else {
				$this->Session->setFlash(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.', true), 'default', array('class' => 'error'));
			}
			$this->redirect('/');
		}
		$file_dir = Configure::read('folders.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if ($this->is_admin) {
				$this->Session->setFlash(sprintf(__('Your uploads folder %s does not exist or is not writable.', true), $file_dir), 'default', array('class' => 'error'));
			} else {
				$this->Session->setFlash(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.', true), 'default', array('class' => 'error'));
			}
			$this->redirect('/');
		}

		$person = $this->UserCache->read('Person');
		$size = 150;
		$this->set(compact('person', 'size'));

		if (!empty ($this->data) && array_key_exists ('image', $this->data)) {
			if (empty ($this->data['image'])) {
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_INI_SIZE) {
				$max = ini_get('upload_max_filesize');
				$unit = substr($max,-1);
				if ($unit == 'M' || $unit == 'K') {
					$max .= 'b';
				}
				$this->Session->setFlash(sprintf (__('The selected photo is too large. Photos must be less than %s.', true), $max), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_NO_FILE) {
				$this->Session->setFlash(__('You must select a photo to upload', true), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_NO_TMP_DIR ||
				$this->data['image']['error'] == UPLOAD_ERR_CANT_WRITE)
			{
				$this->Session->setFlash(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.', true), 'default', array('class' => 'error'));
				return;
			}
			if ($this->data['image']['error'] != 0 ||
				strpos ($this->data['image']['type'], 'image/') === false)
			{
				$this->log($this->data, 'upload');
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true), 'default', array('class' => 'warning'));
				return;
			}
			$ext = strtolower(substr($this->data['image']['name'], strrpos($this->data['image']['name'], '.') + 1));
			if (!in_array($ext, array('gif', 'jpg', 'jpeg', 'png'))) {
				$this->log($this->data, 'upload');
				$this->Session->setFlash(__('Supported formats are PNG, JPEG and GIF.', true), 'default', array('class' => 'warning'));
				return;
			}

			// Image was uploaded, ask user to crop it
			$rand = mt_rand();
			$uploaded = $this->ImageCrop->uploadImage($this->data['image'], $temp_dir, "temp_{$person['id']}_$rand");
			$this->set(compact('uploaded'));
			if (!$uploaded) {
				$this->Session->setFlash(__('Unexpected error uploading the file', true), 'default', array('class' => 'warning'));
			} else {
				$this->render('photo_resize');
			}
		}
	}

	function photo_resize() {
		if (!empty ($this->data)) {
			$person = $this->UserCache->read('Person');
			$size = 150;
			$this->set(compact('person', 'size'));
			$temp_dir = Configure::read('folders.league_base') . DS . 'temp';
			$file_dir = Configure::read('folders.uploads');

			// Are approvals required?
			$approved = (Configure::read('feature.approve_photos') ? false : true);

			// Crop and resize the image
			$image = $this->ImageCrop->cropImage($size,
					$this->data['x1'], $this->data['y1'],
					$this->data['x2'], $this->data['y2'],
					$this->data['w'], $this->data['h'],
					$file_dir . DS . $person['id'] . '.jpg',
					$temp_dir . DS . $this->data['imageName']);
			if ($image) {
				// Check if we're overwriting an existing photo.
				$photo = $this->Person->Upload->find('first', array(
						'contain' => false,
						'conditions' => array(
							'person_id' => $person['id'],
							'type_id' => null,
						),
				));
				if (empty ($photo)) {
					$this->Person->Upload->save(array(
							'person_id' => $person['id'],
							'type_id' => null,
							'filename' => basename ($image),
							'approved' => $approved,
					));
				} else {
					$this->Person->Upload->id = $photo['Upload']['id'];
					$this->Person->Upload->saveField ('approved', $approved);
				}
				if (!$approved) {
					$this->Session->setFlash(sprintf(__('The %s has been saved, but will not be visible until approved', true), __('photo', true)), 'default', array('class' => 'success'));
				}
			}
			$this->redirect(array('action' => 'view'));
		}
	}

	function approve_photos() {
		if (!Configure::read('feature.approve_photos')) {
			$this->Session->setFlash(__('Approval of photos is not required on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$photos = $this->Person->Upload->find('all', array(
				'contain' => array('Person'),
				'conditions' => array(
					'approved' => 0,
					'type_id' => null,
				),
		));
		if (empty ($photos)) {
			$this->Session->setFlash(__('There are no photos to approve.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('photos'));
	}

	function approve_photo() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$this->Person->Upload->id = $id;
		$success = $this->Person->Upload->saveField ('approved', true);
		$this->set(compact('success'));

		$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name));
		$person = $this->Person->Upload->read (null, $id);
		$this->set(compact('person'));

		if (!$this->_sendMail (array (
				'to' => $person,
				'subject' => Configure::read('organization.name') . ' Notification of Photo Approval',
				'template' => 'photo_approved',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
		}
	}

	function delete_photo() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name));
		$photo = $this->Person->Upload->read(null, $id);
		if (!$photo) {
			$success = false;
		} else {
			$success = $this->Person->Upload->delete ($id);
			if ($success) {
				$file_dir = Configure::read('folders.uploads');
				unlink($file_dir . DS . $photo['Upload']['filename']);
			}
		}
		$this->set(compact('success'));
		$this->set('person', $photo);

		if (!$this->_sendMail (array (
				'to' => $photo,
				'subject' => Configure::read('organization.name') . ' Notification of Photo Deletion',
				'template' => 'photo_deleted',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $photo['Person']['email']), 'default', array('class' => 'error'), 'email');
		}
	}

	function document() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('id');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$file_dir = Configure::read('folders.uploads');
		$this->Person->Upload->contain(array('Person', 'UploadType'));
		$document = $this->Person->Upload->read(null, $id);
		if (!$document) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (!$this->is_admin && $document['Upload']['person_id'] != $this->Auth->user('zuluru_person_id')) {
			$this->Session->setFlash(__('You do not have permission to access this document.', true), 'default', array('class' => 'warning'));
			$this->redirect('/');
		}

		$this->view = 'Media';
		$f = new File($document['Upload']['filename']);
		$this->set(array(
				'path' => $file_dir . DS,
				'id' => $document['Upload']['filename'],
				'extension' => $f->ext(),
				'name' => $f->info['filename'],
				'mimeType' => Configure::read('new_mime_types'),
				'download' => !in_array($f->ext(), Configure::read('no_download_extensions')),
		));
	}

	function document_upload() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$file_dir = Configure::read('folders.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if ($this->is_admin) {
				$this->Session->setFlash(sprintf(__('Your uploads folder %s does not exist or is not writable.', true), $file_dir), 'default', array('class' => 'error'));
			} else {
				$this->Session->setFlash(__('This system does not appear to be properly configured for document uploads. Please contact your administrator to have them correct this.', true), 'default', array('class' => 'error'));
			}
			$this->redirect('/');
		}

		$id = $this->_arg('person');
		if ($id) {
			$this->Person->contain();
			$person = $this->Person->read(null, $id);
			if ($person) {
				$person = $person['Person'];
			}
		} else {
			$person = $this->UserCache->read('Person');
		}
		if (!$person) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$affiliates = $this->_applicableAffiliateIDs();
		$types = $this->Person->Upload->UploadType->find('all', array(
				'conditions' => array('UploadType.affiliate_id' => $affiliates),
				'contain' => array('Affiliate'),
				'order' => array('Affiliate.name', 'UploadType.name'),
		));
		if (count($affiliates) > 1) {
			$names = array();
			foreach ($types as $type) {
				$names[$type['Affiliate']['name']][$type['UploadType']['id']] = $type['UploadType']['name'];
			}
			$types = $names;
		} else {
			$types = Set::combine($types, '{n}.UploadType.id', '{n}.UploadType.name');
		}

		$type = $this->_arg('type');
		$this->set(compact('person', 'types', 'type'));

		if (!empty ($this->data) && array_key_exists ('document', $this->data)) {
			if (empty ($this->data['document'])) {
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['document']['error'] == UPLOAD_ERR_INI_SIZE) {
				$max = ini_get('upload_max_filesize');
				$unit = substr($max,-1);
				if ($unit == 'M' || $unit == 'K') {
					$max .= 'b';
				}
				$this->Session->setFlash(sprintf (__('The selected document is too large. Documents must be less than %s.', true), $max), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['document']['error'] == UPLOAD_ERR_NO_FILE) {
				$this->Session->setFlash(__('You must select a document to upload', true), 'default', array('class' => 'warning'));
				return;
			}
			if ($this->data['document']['error'] == UPLOAD_ERR_NO_TMP_DIR ||
				$this->data['document']['error'] == UPLOAD_ERR_CANT_WRITE)
			{
				$this->Session->setFlash(__('This system does not appear to be properly configured for document uploads. Please contact your administrator to have them correct this.', true), 'default', array('class' => 'error'));
				return;
			}
			if ($this->data['document']['error'] != 0) {
				$this->log($this->data, 'upload');
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true), 'default', array('class' => 'warning'));
				return;
			}

			$transaction = new DatabaseTransaction($this->Person->Upload);
			if ($this->Person->Upload->save(array(
					'person_id' => $person['id'],
					'type_id' => $this->data['document_type'],
			)))
			{
				$file_ext = substr($this->data['document']['name'], strrpos($this->data['document']['name'], '.') + 1);
				$upload_target = $file_dir . DS . $person['id'] . '_' . $this->Person->Upload->id . '.' . low($file_ext);
				move_uploaded_file($this->data['document']['tmp_name'], $upload_target);
				chmod ($upload_target, 0644);

				$this->Person->Upload->saveField ('filename', basename ($upload_target));

				$transaction->commit();
				$this->Session->setFlash(__('Document saved, you will receive an email when it has been approved', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'person' => $person['id']));
			} else {
				$this->Session->setFlash(__('Failed to save your document', true), 'default', array('class' => 'warning'));
			}
		}
	}

	function approve_documents() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$documents = $this->Person->Upload->find('all', array(
				'contain' => array('Person', 'UploadType'),
				'conditions' => array(
					'approved' => 0,
					'type_id !=' => null,
				),
				'order' => array('Person.last_name', 'Person.first_name', 'UploadType.id'),
		));
		if (empty ($documents)) {
			$this->Session->setFlash(__('There are no documents to approve.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('documents'));
	}

	function approve_document() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('id');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name, 'UploadType'));
		$document = $this->Person->Upload->read (null, $id);
		if (!$document) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('document'));

		if (!empty($this->data)) {
			if ($this->Person->Upload->save($this->data)) {
				// Read updated version
				$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name, 'UploadType'));
				$document = $this->Person->Upload->read (null, $id);
				$this->UserCache->clear('Documents', $document['Person']['id']);
				$this->set(compact('document'));
				$this->Session->setFlash(sprintf (__('Approved %s', true), __('document', true)), 'default', array('class' => 'success'));

				if (!$this->_sendMail (array (
						'to' => $document,
						'subject' => Configure::read('organization.name') . ' Notification of Document Approval',
						'template' => 'document_approved',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $document['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				$this->redirect(array('action' => 'approve_documents'));
			} else {
				$this->Session->setFlash(__('Failed to approve the document', true), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $document;
		}
	}

	function edit_document() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('id');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name, 'UploadType'));
		$document = $this->Person->Upload->read (null, $id);
		if (!$document) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('document', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('document'));

		if (!empty($this->data)) {
			if ($this->Person->Upload->save($this->data)) {
				// Read updated version
				$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name, 'UploadType'));
				$document = $this->Person->Upload->read (null, $id);
				$this->set(compact('document'));
				$this->Session->setFlash(sprintf (__('Updated %s', true), __('document', true)), 'default', array('class' => 'success'));

				if (!$this->_sendMail (array (
						'to' => $document,
						'subject' => Configure::read('organization.name') . ' Notification of Document Update',
						'template' => 'document_updated',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $document['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				$this->redirect(array('action' => 'view', 'person' => $document['Person']['id']));
			} else {
				$this->Session->setFlash(__('Failed to update the document', true), 'default', array('class' => 'warning'));
			}
		} else {
			$this->data = $document;
		}
		$this->render('approve_document');
	}

	function delete_document() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$this->Person->Upload->contain(array('Person' => $this->Auth->authenticate->name, 'UploadType'));
		$document = $this->Person->Upload->read (null, $id);
		if (!$document) {
			$success = false;
		} else if (!$this->is_admin && $document['Upload']['person_id'] != $this->Auth->user('zuluru_person_id')) {
			$success = false;
		} else {
			if (!empty($this->data['Document']['comment'])) {
				$this->set('comment', $this->data['Document']['comment']);
			}
			$success = $this->Person->Upload->delete ($id);
			if ($success) {
				$this->UserCache->clear('Documents', $document['Upload']['person_id']);
				$file_dir = Configure::read('folders.uploads');
				unlink($file_dir . DS . $document['Upload']['filename']);
			}
		}
		$this->set(compact('success', 'document'));

		if ($success && $document['Person']['id'] != $this->Auth->user('zuluru_person_id')) {
			if (!$this->_sendMail (array (
					'to' => $document,
					'subject' => Configure::read('organization.name') . ' Notification of Document Deletion',
					'template' => 'document_deleted',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $document['Person']['email']), 'default', array('class' => 'error'), 'email');
			}
		}
	}

	function nominate() {
		if (!empty($this->data)) {
			if (empty($this->data['badge'])) {
				$this->Session->setFlash(__('You must select an badge!', true), 'default', array('class' => 'warning'));
			} else {
				$this->redirect(array('action' => 'nominate_badge', 'badge' => $this->data['badge']));
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$conditions = array(
			'Badge.category' => 'nominated',
			'Badge.active' => true,
			'Badge.affiliate_id' => $affiliates,
		);
		if ($this->is_admin || $this->is_manager) {
			$conditions['Badge.category'] = array('nominated', 'assigned');
		} else {
			$conditions['Badge.visibility !='] = BADGE_VISIBILITY_ADMIN;
		}

		$badges = $this->Person->Badge->find('all', array(
				'conditions' => $conditions,
				'contain' => array('Affiliate'),
				'order' => array('Affiliate.name', 'Badge.category', 'Badge.name'),
		));

		if (count($affiliates) > 1) {
			$names = array();
			foreach ($badges as $badge) {
				$names[$badge['Affiliate']['name']][$badge['Badge']['id']] = $badge['Badge']['name'];
			}
			$badges = $names;
		} else {
			$badges = Set::combine($badges, '{n}.Badge.id', '{n}.Badge.name');
		}

		$this->set(compact('badges'));
	}

	function nominate_badge() {
		$params = $url = $this->_extractSearchParams();
		unset ($params['badge']);

		if (!$url['badge']) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->Person->Badge->contain();
		$badge = $this->Person->Badge->read(null, $url['badge']);
		if (!$badge) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->is_manager = in_array($badge['Badge']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));
		if (!$badge['Badge']['active']) {
			$this->Session->setFlash(sprintf(__('Inactive %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if ($badge['Badge']['visibility'] == BADGE_VISIBILITY_ADMIN && !($this->is_admin || $this->is_manager)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if ($badge['Badge']['category'] != 'nominated' && ($badge['Badge']['category'] != 'assigned' || !($this->is_admin || $this->is_manager))) {
			$this->Session->setFlash(__('This badge must be earned, not granted.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->set(compact('badge'));
		$this->Configuration->loadAffiliate($badge['Badge']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->_handlePersonSearch($params, $url, $this->Person);
	}

	function nominate_badge_reason() {
		$badge_id = $this->_arg('badge');
		if (!$badge_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->Person->Badge->contain(array(
			'Person' => array('conditions' => array('person_id' => $person_id)),
		));
		$badge = $this->Person->Badge->read(null, $badge_id);
		if (!$badge) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->is_manager = in_array($badge['Badge']['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));
		if (!$badge['Badge']['active']) {
			$this->Session->setFlash(sprintf(__('Inactive %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if ($badge['Badge']['visibility'] == BADGE_VISIBILITY_ADMIN && !($this->is_admin || $this->is_manager)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('badge', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if ($badge['Badge']['category'] != 'nominated' && ($badge['Badge']['category'] != 'assigned' || !($this->is_admin || $this->is_manager))) {
			$this->Session->setFlash(__('This badge must be earned, not granted.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if (!empty($badge['Person'])) {
			if ($badge['Badge']['active']) {
				// TODO: Allow multiple copies of the badge?
				$this->Session->setFlash(__('This player already has this badge', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'nominate_badge', 'badge' => $badge_id));
			} else {
				$this->Session->setFlash(__('This player has already been nominated for this badge', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'nominate_badge', 'badge' => $badge_id));
			}
		}
		$this->set(compact('badge'));
		$this->Configuration->loadAffiliate($badge['Badge']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		if (!empty($this->data)) {
			$data = array(
				'badge_id' => $badge_id,
				'person_id' => $person_id,
				'reason' => $this->data['BadgesPerson']['reason'],
			);
			if ($badge['Badge']['category'] == 'assigned') {
				$data['approved'] = true;
				$data['approved_by'] = $this->Auth->user('zuluru_person_id');
			} else {
				$data['nominated_by'] = $this->Auth->user('zuluru_person_id');
			}
			if ($this->Person->BadgesPerson->save($data)) {
				if ($badge['Badge']['category'] == 'assigned') {
					$this->Session->setFlash(__('The badge has been assigned', true), 'default', array('class' => 'success'));
					$this->UserCache->clear('Badges', $person_id);

					if ($badge['Badge']['visibility'] != BADGE_VISIBILITY_ADMIN) {
						$this->Person->BadgesPerson->contain(array(
								'Badge',
								'Person' => $this->Auth->authenticate->name,
								'ApprovedBy',
						));
						$person = $this->Person->BadgesPerson->read (null, $this->Person->BadgesPerson->id);
						$this->set(compact('person'));

						// Inform the recipient
						if (!$this->_sendMail (array (
							'to' => $person['Person'],
							'subject' => Configure::read('organization.name') . ' New Badge Awarded',
							'template' => 'badge_awarded',
							'sendAs' => 'both',
						)))
						{
							$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
						}
					}
				} else {
					$this->Session->setFlash(__('Your nomination has been saved', true), 'default', array('class' => 'success'));
				}
				$this->redirect(array('controller' => 'badges', 'action' => 'index'));
			} else {
				if ($badge['Badge']['category'] == 'assigned') {
					$this->Session->setFlash(__('Your badge assignment could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
				} else {
					$this->Session->setFlash(__('Your nomination could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
				}
			}
		}

		$this->Person->contain('Affiliate');
		$person = $this->Person->read(null, $person_id);
		if (!$person) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		if (Configure::read('feature.affiliates' && !in_array($badge['Badge']['affiliate_id'], Set::extract('/Affiliate/id', $person)))) {
			$this->Session->setFlash(__('That person is not a member of this affiliate.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'badges', 'action' => 'index'));
		}
		$this->set(compact('person'));
	}

	function approve_badges() {
		if (!Configure::read('feature.badges')) {
			$this->Session->setFlash(__('Badges are not enabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$badges = $this->Person->Badge->find('all', array(
				'contain' => array(
					'Person' => array('conditions' => array('approved' => 0))
				),
				'conditions' => array(
					'Badge.affiliate_id' => $affiliates,
				),
		));
		$people = Set::extract('/Person/id', $badges);
		if (empty($people)) {
			$this->Session->setFlash(__('There are no badges to approve.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('badges'));
	}

	function approve_badge() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$this->Person->BadgesPerson->contain(array(
				'Badge',
				'Person' => $this->Auth->authenticate->name,
				'ApprovedBy',
				'NominatedBy' => $this->Auth->authenticate->name,
		));
		$person = $this->Person->BadgesPerson->read (null, $id);
		$this->set(compact('person'));

		$success = $this->Person->BadgesPerson->save (array(
			'approved' => true,
			'approved_by' => $this->Auth->user('zuluru_person_id'),
		));
		$this->set(compact('success'));

		if ($success) {
			$this->UserCache->clear('Badges', $person['Person']['id']);
		}

		if ($success && $person['Badge']['visibility'] != BADGE_VISIBILITY_ADMIN) {
			// Inform the nominator
			if (!$this->_sendMail (array (
				'to' => $person['NominatedBy'],
				'subject' => Configure::read('organization.name') . ' Notification of Badge Approval',
				'template' => 'badge_nomination_approved',
				'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['NominatedBy']['email']), 'default', array('class' => 'error'), 'email');
			}

			// Inform the recipient
			if (!$this->_sendMail (array (
				'to' => $person['Person'],
				'subject' => Configure::read('organization.name') . ' New Badge Awarded',
				'template' => 'badge_awarded',
				'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
			}
		}
	}

	function delete_badge() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$this->Person->BadgesPerson->contain(array(
				'Badge',
				'Person' => $this->Auth->authenticate->name,
				'ApprovedBy',
				'NominatedBy' => $this->Auth->authenticate->name,
		));
		$person = $this->Person->BadgesPerson->read (null, $id);
		$this->set(compact('person'));

		if (!$person) {
			$success = false;
		} else {
			$success = $this->Person->BadgesPerson->delete ($id);
		}
		$this->set(compact('success'));

		if ($success) {
			$this->UserCache->clear('Badges', $person['Person']['id']);
		}

		if ($success && $person['Badge']['visibility'] != BADGE_VISIBILITY_ADMIN) {
			$this->set('comment', $this->data['Badge']['comment']);

			if ($person['BadgesPerson']['approved']) {
				// Inform the badge holder
				if (!$this->_sendMail (array (
					'to' => $person['Person'],
					'subject' => Configure::read('organization.name') . ' Notification of Badge Deletion',
					'template' => 'badge_deleted',
					'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
			} else {
				// Inform the nominator
				if (!$this->_sendMail (array (
					'to' => $person['NominatedBy'],
					'subject' => Configure::read('organization.name') . ' Notification of Badge Rejection',
					'template' => 'badge_nomination_rejected',
					'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['NominatedBy']['email']), 'default', array('class' => 'error'), 'email');
				}
			}
		}
	}

	function delete() {
		if (!Configure::read('feature.manage_accounts')) {
			$this->Session->setFlash (__('This system uses ' . Configure::read('feature.manage_name') . ' to manage user accounts. Account deletion through Zuluru is disabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('person');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// TODO: Don't delete the only admin
		$dependencies = $this->Person->dependencies($id, array('Affiliate'));
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this person, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect('/');
		}
		if (method_exists ($this->Auth->authenticate, 'delete_duplicate_user')) {
			$this->Auth->authenticate->delete_duplicate_user($id);
		}
		if ($this->Person->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Person', true)), 'default', array('class' => 'success'));
			// TODO: Unwind any registrations, including calling event_obj for additional processing like deleting team records
			$this->redirect('/');
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Person', true)), 'default', array('class' => 'warning'));
		$this->redirect('/');
	}

	function search() {
		$params = $url = $this->_extractSearchParams();
		$affiliates = $this->_applicableAffiliates();
		$this->set(compact('affiliates'));
		$this->_handlePersonSearch($params, $url);
	}

	function rule_search() {
		$params = $url = $this->_extractSearchParams();
		$this->_handleRuleSearch($params, $url);
	}

	function league_search() {
		$params = $url = $this->_extractSearchParams();
		unset($url['league_id']);
		unset($url['include_subs']);
		if (array_key_exists('league_id', $params)) {
			if (!empty($params['include_subs'])) {
				$subs = ',include_subs';
			} else {
				$subs = '';
			}
			$params['rule'] = "COMPARE(LEAGUE_TEAM_COUNT({$params['league_id']}$subs) > '0')";
		}

		// Get the list of possible leagues to look at
		$affiliates = $this->_applicableAffiliates();
		$affiliate_leagues = $this->Person->Affiliate->find('all', array(
			'conditions' => array(
				'Affiliate.id' => array_keys($affiliates),
			),
			'contain' => array('League' => array('order' => array('League.open' => 'DESC'))),
			'order' => array('Affiliate.name'),
		));
		$leagues = array();
		foreach ($affiliate_leagues as $affiliate) {
			if (!empty($affiliate['League'])) {
				$leagues[$affiliate['Affiliate']['name']] = array();
				foreach ($affiliate['League'] as $league) {
					$leagues[$affiliate['Affiliate']['name']][$league['id']] = $league['full_name'];
				}
			}
		}
		if (count($leagues == 1)) {
			$leagues = reset($leagues);
		}
		$this->set(compact('leagues'));

		$this->_handleRuleSearch($params, $url);
	}

	function inactive_search() {
		if (empty($this->data)) {
			$this->data = array(
				'rule' => "NOT(COMPARE(TEAM_COUNT('today') > '0'))",
				'sort' => 'last_name',
				'direction' => 'asc',
			);
		}
		$params = $url = $this->_extractSearchParams();

		$this->_handleRuleSearch($params, $url);
	}

	function _handleRuleSearch($params, $url) {
		$affiliates = $this->_applicableAffiliates();
		$this->set(compact('url', 'affiliates'));
		unset($url['rule']);

		if (array_key_exists('rule64', $params)) {
			// Base 64 input must have a length that's a multiple of 4, add = to pad it out
			while (strlen ($params['rule64']) % 4)
			{
				$params['rule64'] .= '=';
			}

			// Encoding can include + signs, which get converted to spaces. Put them back...
			$params['rule64'] = str_replace(' ', '+', $params['rule64']);

			// Base 64 decode to recover the original input
			$params['rule'] = base64_decode ($params['rule64']);
		}

		if (array_key_exists('rule', $params)) {
			// Handle the rule
			$rule_obj = AppController::_getComponent ('Rule', '', $this, true);
			if (!$rule_obj->init ($params['rule'])) {
				$this->set('error', __('Failed to parse the rule.', true));
				return;
			}
			if (!array_key_exists('rule64', $params)) {
				// Base 64 encode the rule for easy URL manipulation, trim any = from the end
				$url['rule64'] = base64_encode ($params['rule']);
				$url['rule64'] = trim ($url['rule64'], '=');
			}
			$this->set(compact('url', 'params'));

			$people = $rule_obj->query($params['rule']);
			if ($people === null) {
				$this->set('error', __('The syntax of the rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.', true));
				return;
			}

			if (!empty($people)) {
				$conditions = array('Person.id' => $people);
				if (array_key_exists('affiliate_id', $params)) {
					$conditions['OR'] = array(
						"AffiliatePerson.affiliate_id" => $params['affiliate_id'],
						'Person.group_id' => 3,
					);
				}

				if ($this->params['url']['ext'] == 'csv') {
					Configure::write ('debug', 0);
					$this->set('people', $this->Person->find ('all', array(
							'contain' => array(
								'Affiliate',
							),
							'conditions' => $conditions,
							'order' => array('Person.last_name', 'Person.first_name', 'Person.id'),
							'joins' => array(array(
								'table' => "{$this->Person->tablePrefix}affiliates_people",
								'alias' => 'AffiliatePerson',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'AffiliatePerson.person_id = Person.id',
							)),
					)));
					$this->set('download_file_name', 'Search results');
					$this->render('rule_search');
				} else {
					$this->_mergePaginationParams();

					$this->paginate = array('Person' => array(
							'conditions' => $conditions,
							'contain' => array(
								'Note' => array('conditions' => array('created_person_id' => $this->Auth->user('zuluru_person_id'))),
								'Affiliate',
							),
							'limit' => Configure::read('feature.items_per_page'),
							'joins' => array(array(
								'table' => "{$this->Person->tablePrefix}affiliates_people",
								'alias' => 'AffiliatePerson',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'AffiliatePerson.person_id = Person.id',
							)),
					));
					$this->set('people', $this->paginate('Person'));
				}
			} else {
				$this->set('error', __('No matches found!', true));
			}
		}
	}

	function list_new() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$user_model = $this->Auth->authenticate->name;
		$id_field = $this->Auth->authenticate->primaryKey;

		$config = new DATABASE_CONFIG;
		$prefix = $this->Auth->authenticate->tablePrefix;
		if ($this->Auth->authenticate->useDbConfig != 'default') {
			$config_name = $this->Auth->authenticate->useDbConfig;
			$config = $config->$config_name;
			$prefix = "{$config['database']}.$prefix";
		}

		$new = $this->Person->find ('all', array(
			'joins' => array(
				array(
					'table' => "{$this->Person->tablePrefix}affiliates_people",
					'alias' => 'AffiliatePerson',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'AffiliatePerson.person_id = Person.id',
				),
				array(
					'table' => "$prefix{$this->Auth->authenticate->useTable}",
					'alias' => $user_model,
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => "$user_model.$id_field = Person.user_id",
				),
			),
			'conditions' => array(
				'Person.status' => 'new',
				'Person.complete' => 1,
				'AffiliatePerson.affiliate_id' => $affiliates,
			),
			'contain' => array(),
			'fields' => array('Person.*', 'AffiliatePerson.*', "$user_model.*"),
			'order' => array('Person.last_name' => 'DESC', 'Person.first_name' => 'DESC'),
		));
		foreach ($new as $key => $person) {
			$duplicates = $this->Person->findDuplicates($person);
			$new[$key]['Person']['duplicate'] = !empty($duplicates);
		}

		$this->set(compact('new'));
	}

	function approve() {
		if (!empty ($this->data)) {
			if (empty ($this->data['Person']['disposition'])) {
				$id = $this->data['Person']['id'];
				$this->Session->setFlash(__('You must select a disposition for this account', true), 'default', array('class' => 'info'));
			} else {
				$this->_approve();
				$this->redirect(array('action' => 'list_new'));
			}
		} else {
			$id = $this->_arg('person');
		}

		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'list_new'));
		}

		$this->Person->contain('Affiliate', Configure::read('security.auth_model'));
		$person = $this->Person->read(null, $id);
		if (!$person) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'list_new'));
		}
		if ($person['Person']['status'] != 'new') {
			$this->Session->setFlash(__('That account has already been approved', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'list_new'));
		}

		$duplicates = $this->Person->findDuplicates($person);
		$activated = $this->Auth->authenticate->activated($person);

		$this->set(compact('person', 'duplicates', 'activated'));
	}

	function _approve() {
		if (strpos ($this->data['Person']['disposition'], ':') !== false) {
			list($disposition,$dup_id) = split(':', $this->data['Person']['disposition']);
		} else {
			$disposition = $this->data['Person']['disposition'];
			$dup_id = null;
		}

		$this->Person->contain($this->Auth->authenticate->name);
		$person_id = $this->data['Person']['id'];
		$person = $this->Person->read(null, $person_id);
		if (!empty ($dup_id)) {
			$this->Person->contain($this->Auth->authenticate->name);
			$existing = $this->Person->read(null, $dup_id);
		}

		// TODO: Some of these require updates/deletions in the settings table
		switch($disposition) {
			case 'approved_player':
				$data = array(
					'id' => $person_id,
					// TODO: 'Player' is hard-coded here, but also in the database
					'group_id' => $this->Person->Group->field('id', array('name' => 'Player')),
					'status' => 'active',
				);
				$saved = $this->Person->save ($data, false, array_keys ($data));
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member activation', true), 'default', array('class' => 'warning'));
					$this->redirect(array('action' => 'approve', 'person' => $person_id));
				}
				$this->UserCache->clear('Person', $person_id);

				$this->set('person', $saved);

				if (!$this->_sendMail (array (
						'to' => $person,
						'subject' => Configure::read('organization.name') . ' Account Activation for ' . $saved['Person']['user_name'],
						'template' => 'account_approved',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				break;

			case 'approved_visitor':
				$data = array(
					'id' => $person_id,
					// TODO: 'Non-player account' is hard-coded here, but also in the database
					'group_id' => $this->Person->Group->field('id', array('name' => 'Non-player account')),
					'status' => 'inactive',
				);
				$saved = $this->Person->save ($data, false, array_keys ($data));
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member activation', true), 'default', array('class' => 'warning'));
					$this->redirect(array('action' => 'approve', 'person' => $person_id));
				}
				$this->UserCache->clear('Person', $person_id);

				$this->set('person', $saved);

				if (!$this->_sendMail (array (
						'to' => $person,
						'subject' => Configure::read('organization.name') . ' Account Activation for ' . $saved['Person']['user_name'],
						'template' => 'account_approved',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				break;

			case 'delete':
				if (method_exists ($this->Auth->authenticate, 'delete_duplicate_user')) {
					$this->Auth->authenticate->delete_duplicate_user($person['Person']['user_id']);
				} else {
					$this->Auth->authenticate->delete($person['Person']['user_id']);
				}
				if (! $this->Person->delete($person_id) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']), 'default', array('class' => 'warning'));
				}
				Cache::delete("person/$person_id", 'file');
				break;

			case 'delete_duplicate':
				if (method_exists ($this->Auth->authenticate, 'delete_duplicate_user')) {
					$this->Auth->authenticate->delete_duplicate_user($person['Person']['user_id']);
				} else {
					$this->Auth->authenticate->delete($person['Person']['user_id']);
				}

				if (! $this->Person->delete($person_id) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']), 'default', array('class' => 'warning'));
					break;
				}
				Cache::delete("person/$person_id", 'file');

				$this->set(compact('person', 'existing'));

				if (!$this->_sendMail (array (
						'to' => array($person['Person'], $existing['Person']),
						'subject' => Configure::read('organization.name') . ' Account Update',
						'template' => 'account_delete_duplicate',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				break;

			// This is basically the same as the delete duplicate, except
			// that some old information (e.g. user ID) is preserved
			case 'merge_duplicate':
				$transaction = new DatabaseTransaction($this->Person);
				if (method_exists ($this->Auth->authenticate, 'merge_duplicate_user')) {
					$this->Auth->authenticate->merge_duplicate_user($person['Person']['user_id'], $existing['Person']['user_id']);
				}

				$this->Person->AffiliatesPerson->deleteAll(array('AffiliatesPerson.person_id' => $dup_id));

				// Update all related records
				foreach ($this->Person->hasMany as $class => $details) {
					$this->Person->$class->updateAll(
						array("$class.{$details['foreignKey']}" => $dup_id),
						array("$class.{$details['foreignKey']}" => $person_id)
					);
				}

				foreach ($this->Person->hasAndBelongsToMany as $class => $details) {
					if (array_key_exists ('with', $details)) {
						$this->Person->$class->{$details['with']}->updateAll(
							array("{$details['with']}.{$details['foreignKey']}" => $dup_id),
							array("{$details['with']}.{$details['foreignKey']}" => $person_id)
						);
					}
				}

				if (! $this->Person->delete($person_id, false) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']), 'default', array('class' => 'warning'));
					break;
				}

				// Unset a few fields that we want to retain from the old record
				foreach (array('group_id', 'status', 'user_id', 'year_started') as $field) {
					unset ($person['Person'][$field]);
				}
				$person['Person']['id'] = $dup_id;

				$saved = $this->Person->save ($person);
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member information', true), 'default', array('class' => 'warning'));
					break;
				} else {
					$transaction->commit();
				}
				Cache::delete("person/$person_id", 'file');
				Cache::delete("person/$dup_id", 'file');

				$this->set(compact('person', 'existing'));

				if (!$this->_sendMail (array (
						'to' => array($person['Person'], $existing['Person']),
						'subject' => Configure::read('organization.name') . ' Account Update',
						'template' => 'account_merge_duplicate',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']), 'default', array('class' => 'error'), 'email');
				}
				break;
		}
	}

	function vcf() {
		$this->layout = 'vcf';
		$id = $this->_arg('person');
		if (!$id) {
			return;
		}

		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('person'));
		$this->set('download_file_name', $person['full_name']);
		$this->set($this->_connections($id));
	}

	// This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	function ical($id) {
		$this->layout = 'ical';
		if (!$id) {
			return;
		}

		// Check that the person has enabled this option
		$this->Person->contain(array(
				'Setting' => array('conditions' => array('name' => 'enable_ical')),
		));
		$person = $this->Person->read(null, $id);
		$enabled = Set::extract ('/Setting/value', $person);
		if (empty ($enabled) || !$enabled[0]) {
			return;
		}

		$team_ids = $this->UserCache->read('TeamIDs', $id);

		if (!empty ($team_ids)) {
			$games = $this->Division->Game->find ('all', array(
				'conditions' => array(
					'OR' => array(
						'HomeTeam.id' => $team_ids,
						'AwayTeam.id' => $team_ids,
					),
					'Game.published' => true,
				),
				'contain' => array(
					'GameSlot' => array('Field' => 'Facility'),
					'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
					'HomeTeam',
					'AwayTeam',
				),
			));

			$events = $this->Division->Team->TeamEvent->find ('all', array(
				'conditions' => array(
					'TeamEvent.team_id' => $team_ids,
				),
				'contain' => array(
					'Team',
				),
			));

			// Game iCal element will handle team_id as an array
			$this->set('team_id', $team_ids);
			$this->set(compact('games', 'events'));
		}

		if (Configure::read('feature.tasks')) {
			$this->set('tasks', $this->UserCache->read('Tasks', $id));
		}

		$this->set ('calendar_type', 'Player Schedule');
		$this->set ('calendar_name', "{$person['Person']['full_name']}'s schedule");

		Configure::write ('debug', 0);
	}

	function registrations() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->Person->contain();
		$this->set('person', $this->Person->read(null, $id));
		$this->paginate['Registration'] = array(
				'contain' => array('Event' => array('EventType', 'Affiliate', 'Division' => array('League', 'Day'))),
				'conditions' => array('Event.affiliate_id' => $affiliates),
				'order' => array('Event.affiliate_id', 'Registration.created' => 'DESC'),
				'limit' => Configure::read('feature.items_per_page'),
		);
		$this->set('registrations', $this->paginate ('Registration', array('person_id' => $id)));
		$this->set(compact('affiliates'));
	}

	function credits() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->Person->contain(array(
				'Credit' => array(
					'Affiliate',
					'conditions' => array('Credit.affiliate_id' => $affiliates),
					'order' => array('Credit.affiliate_id', 'Credit.created'),
				),
		));
		$this->set('person', $this->Person->read(null, $id));
		$this->set(compact('affiliates'));
	}

	function teams() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$this->Person->contain();
		$this->set('person', $this->Person->read(null, $id));
		$this->set('teams', array_reverse($this->Person->Team->readByPlayerId($id, false)));
	}

	function waivers() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('zuluru_person_id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->Person->contain(array('Waiver' => array(
				'Affiliate',
				'conditions' => array('Waiver.affiliate_id' => $affiliates),
				'order' => array('Waiver.affiliate_id', 'WaiversPerson.created' => 'DESC'),
		)));
		$person = $this->Person->read(null, $id);

		if ($id == $my_id) {
			$waivers = array();
			foreach ($affiliates as $affiliate) {
				$signed_names = Set::extract("/Waiver[affiliate_id=$affiliate]/name", $this->UserCache->read('WaiversCurrent'));
				$waivers = array_merge($waivers, $this->Person->Waiver->find('all', array(
						'contain' => array('Affiliate'),
						'conditions' => array(
							'Waiver.active' => true,
							'Waiver.expiry_type !=' => 'event',
							'Waiver.affiliate_id' => $affiliate,
							'NOT' => array('Waiver.name' => $signed_names),
						),
				)));
			}
		}

		$this->set(compact('person', 'affiliates', 'waivers'));
	}

	function cron() {
		$this->layout = 'bare';

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		if (Configure::read('feature.registration')) {
			$types = $this->Person->Registration->Event->EventType->find ('list', array(
					'fields' => 'id',
					'conditions' => array('type' => 'membership'),
			));
			$events = $this->Person->Registration->Event->find ('all', array(
					'contain' => array(),
					'conditions' => array('event_type_id' => $types)
			));

			$now = time();
			$emailed = 0;
			$log = ClassRegistry::init ('ActivityLog');

			foreach ($events as $event) {
				if (array_key_exists('membership_begins', $event['Event']) &&
					strtotime ($event['Event']['membership_begins']) < $now &&
					$now < strtotime ($event['Event']['membership_ends']))
				{
					$year = date('Y', strtotime ($event['Event']['membership_begins']));
					$this->set(compact('event', 'year'));

					$people = $this->Person->find ('all', array(
							'contain' => array($this->Auth->authenticate->name),
							'conditions' => array(
								array("Person.id IN (SELECT DISTINCT person_id FROM registrations WHERE event_id = {$event['Event']['id']} AND payment = 'Paid')"),
								array("Person.id NOT IN (SELECT person_id FROM activity_logs WHERE type = 'email_membership_letter' AND custom = $year)"),
							),
							'limit' => 100,
					));

					foreach ($people as $person) {
						// Send the email
						$this->set(compact('person'));
						if ($this->_sendMail (array (
								'to' => $person,
								'subject' => Configure::read('organization.name') . " $year Membership",
								'template' => 'membership_letter',
								'sendAs' => 'both',
								'header' => array(
									'Auto-Submitted' => 'auto-generated',
									'X-Auto-Response-Suppress' => 'OOF',
								),
						)))
						{
							// Update the activity log
							$log->create();
							$log->save(array(
								'type' => 'email_membership_letter',
								'custom' => $year,
								'person_id' => $person['Person']['id'],
							));
							++ $emailed;
						}
					}
				}
			}

			$this->set(compact ('emailed'));
		}

		$this->Lock->unlock();
	}

	function _hash ($relative, $salt = true) {
		// Build a string of the inputs
		$input = "{$relative['id']}:{$relative['person_id']}:{$relative['relative_id']}:{$relative['created']}";
		if ($salt) {
			$input = $input . ':' . Configure::read('Security.salt');
		}
		return md5($input);
	}
}
?>
