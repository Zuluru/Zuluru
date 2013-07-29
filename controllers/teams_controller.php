<?php
class TeamsController extends AppController {

	var $name = 'Teams';
	var $helpers = array('ZuluruGame', 'Ajax');
	var $components = array('Lock');

	// The PDFize plugin is optional, so we can't rely on it being present and put it in the components array.
	// But, because of how it functions, it does need to be initialized during the __construct phase.
	function __construct(){
		// But, at this time, the configuration hasn't been read from the database yet, so we can't use the
		// feature.pdfize setting to determine whether to include it. So, we check for existence of the file
		// and load it if it's there.
		if (file_exists(APP . 'plugins' . DS . 'pdfize' . DS . 'controllers' . DS . 'components' . DS . 'pdf.php')){
			$this->components['Pdfize.Pdf'] = array(
				'actions' => array(),
				'debug' => false,
				'size' => 'letter',
				'orientation' => 'portrait',
			);
		}
		parent::__construct();
	}

	function publicActions() {
		$actions = array('cron', 'index', 'add', 'letter', 'view', 'tooltip', 'schedule', 'ical',
			// Roster updates may come from emailed links; people might not be logged in
			'roster_accept', 'roster_decline',
		);
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}
		return $actions;
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'join',
				'note',
				'delete_note',
				'stats',
				'past_count',
				'open_count',
		)))
		{
			return true;
		}

		// People can perform these operations on teams they run
		if (in_array ($this->params['action'], array(
				'edit',
				'delete',
				'add_player',
				'add_from_team',
				'roster_role',
				'roster_position',
				'roster_add',
				'numbers',
				'emails',
				'stat_sheet',
		)))
		{
			// If a team id is specified, check if we're a captain of that team
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
				return true;
			}
		}

		// People can perform these operations on their own account
		if (in_array ($this->params['action'], array(
				'roster_role',
				'roster_position',
				'roster_request',
				'numbers',
		)))
		{
			// If a player id is specified, check if it's the logged-in user
			// If no player id is specified, it's always the logged-in user
			$person = $this->_arg('person');
			if (!$person || $person == $this->Auth->user('id')) {
				return true;
			}
		}

		// People can perform these operations on teams they are on
		if (in_array ($this->params['action'], array(
				'attendance',
		)))
		{
			$team = $this->_arg('team');
			if ($team && in_array ($team, $this->Session->read('Zuluru.TeamIDs'))) {
				return true;
			}
			// Check past teams too
			$count = $this->Team->TeamsPerson->find('count', array('conditions' => array(
				'person_id' => $this->Auth->user('id'),
				'team_id' => $team,
			)));
			if ($count) {
				return true;
			}
		}

		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'statistics',
					'unassigned',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if (!$affiliate) {
					// If there's no affiliate id, this is a top-level operation that all managers can perform
					return true;
				} else if (in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
					return true;
				}
			}

			if (in_array ($this->params['action'], array(
				'edit',
				'delete',
				'add_player',
				'add_from_team',
				'add_from_event',
				'roster_role',
				'roster_position',
				'roster_request',
				'roster_add',
				'numbers',
				'emails',
				'stat_sheet',
				'attendance',
				'spirit',
				'move',
			)))
			{
				// If a team id is specified, check if we're a manager of that team's affiliate
				$team = $this->_arg('team');
				if ($team) {
					if (in_array($this->Team->affiliate($team), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		// People can perform these operations on divisions they coordinate, unless it's their team
		if (in_array ($this->params['action'], array(
				'add_player',
				'add_from_event',
				'roster_add',
				'roster_role',
				'roster_position',
				'numbers',
		)))
		{
			// If a team id is specified, check if we're a coordinator of that team's division
			$team = $this->_arg('team');
			if ($team) {
				$this->_limitOverride($team);
				return $this->effective_coordinator;
			}
		}

		// People can perform these operations on divisions they coordinate
		if (in_array ($this->params['action'], array(
				'spirit',
				'stat_sheet',
		)))
		{
			// If a team id is specified, check if we're a coordinator of that team's division
			$team = $this->_arg('team');
			if ($team) {
				$divisions = $this->Session->read('Zuluru.Divisions');
				$teams = Set::extract ('/Team/id', $divisions);
				return in_array ($team, $teams);
			}
		}

		return false;
	}

	function index() {
		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('affiliates', 'affiliate'));

		$conditions = array(
			'Division.is_open' => true,
			'League.affiliate_id' => $affiliates,
		);

		$joins = array(
			array(
				'table' => "{$this->Team->tablePrefix}divisions",
				'alias' => 'Division',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Team.division_id = Division.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}leagues",
				'alias' => 'League',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Division.league_id = League.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}affiliates",
				'alias' => 'Affiliate',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'League.affiliate_id = Affiliate.id',
			),
		);

		$this->paginate = array('Team' => array(
				'conditions' => $conditions,
				'contain' => array(),
				'joins' => $joins,
				'fields' => array('Team.*', 'Division.*', 'League.*', 'Affiliate.*'),
				'order' => array('Affiliate.name', 'Team.name', 'Division.open'),
				'limit' => Configure::read('feature.items_per_page'),
		));
		$this->set('teams', $this->paginate('Team'));
		$this->set('letters', $this->Team->find('all', array(
				'conditions' => $conditions,
				'contain' => array(),
				'joins' => $joins,
				'fields' => array('DISTINCT SUBSTR(Team.name, 1, 1) AS letter'),
				'order' => 'letter',
				// Grouping necessary because Cake adds Team.id to the query, so we get
				// "DISTINCT letter, id", which is more results than just "DISTINCT letter"
				'group' => 'letter',
		)));
	}

	function letter() {
		$letter = up($this->_arg('letter'));
		if (!$letter) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('letter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('letter', 'affiliates', 'affiliate'));

		$conditions = array(
			'Division.is_open' => true,
			'League.affiliate_id' => $affiliates,
		);

		$joins = array(
			array(
				'table' => "{$this->Team->tablePrefix}divisions",
				'alias' => 'Division',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Team.division_id = Division.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}leagues",
				'alias' => 'League',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Division.league_id = League.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}affiliates",
				'alias' => 'Affiliate',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'League.affiliate_id = Affiliate.id',
			),
		);

		$this->set('teams', $this->Team->find('all', array(
				'conditions' => array_merge($conditions, array(
					'Team.name LIKE' => "$letter%",
				)),
				'contain' => array(),
				'joins' => $joins,
				'fields' => array('Team.*', 'Division.*', 'League.*', 'Affiliate.*'),
				'order' => array('Affiliate.name', 'Team.name', 'Division.open'),
		)));

		$this->set('letters', $this->Team->find('all', array(
				'conditions' => $conditions,
				'contain' => array(),
				'joins' => $joins,
				'fields' => array('DISTINCT SUBSTR(Team.name, 1, 1) AS letter'),
				'order' => 'letter',
				// Grouping necessary because Cake adds Team.id to the query, so we get
				// "DISTINCT letter, id", which is more results than just "DISTINCT letter"
				'group' => 'letter',
		)));
	}

	function join() {
		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('affiliates', 'affiliate'));

		$conditions = array(
			'Team.open_roster' => true,
			'Division.is_open' => true,
			'League.affiliate_id' => $affiliates,
		);

		$joins = array(
			array(
				'table' => "{$this->Team->tablePrefix}divisions",
				'alias' => 'Division',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Team.division_id = Division.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}leagues",
				'alias' => 'League',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Division.league_id = League.id',
			),
			array(
				'table' => "{$this->Team->tablePrefix}affiliates",
				'alias' => 'Affiliate',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'League.affiliate_id = Affiliate.id',
			),
		);

		$this->paginate = array('Team' => array(
				'conditions' => $conditions,
				'contain' => array(),
				'joins' => $joins,
				'fields' => array('Team.*', 'Division.*', 'League.*', 'Affiliate.*'),
				'order' => array('Affiliate.name', 'Team.name', 'Division.open'),
				'limit' => Configure::read('feature.items_per_page'),
		));
		$this->set('teams', $this->paginate('Team'));
	}	

	function unassigned() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate['Team'] = array(
			'conditions' => array(
				'Team.affiliate_id' => $affiliates,
				'Team.division_id' => null,
			),
			'contain' => array(),
			'limit' => Configure::read('feature.items_per_page'),
		);
		$this->set('teams', $this->paginate('Team'));
	}

	function statistics() {
		// We need the names here, so that "top 10" lists are sorted by affiliate name
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));

		// Division conditions take precedence over year conditions
		$division = $this->_arg('division');
		$year = $this->_arg('year');
		if ($division !== null) {
			$conditions = array('Division.id' => $division);
		} else if ($year === null) {
			$conditions = array('Division.is_open' => true);
		} else {
			$conditions = array('YEAR(Division.open)' => $year);
		}
		$conditions['League.affiliate_id'] = array_keys($affiliates);

		$divisions = $this->Team->Division->find('all', array(
				'conditions' => $conditions,
				'contain' => array(
					'League' => 'Affiliate',
					'Day',
				),
		));
		$this->Team->Division->addPlayoffs($divisions);
		AppModel::_reindexOuter($divisions, 'Division', 'id');

		// Get the list of how many teams each division has
		$counts = $this->Team->find('all', array(
				'fields' => array(
					'Team.division_id',
					'COUNT(Team.division_id) AS count',
				),
				'conditions' => array('division_id' => array_keys($divisions)),
				'contain' => false,
				'group' => 'Team.division_id',
				'order' => 'Team.division_id DESC',
		));

		// Add division info
		foreach ($counts as $key => $division) {
			$counts[$key] += $divisions[$division['Team']['division_id']];
		}
		usort($counts, array('League', 'compareLeagueAndDivision'));

		// Get the list of teams that are short on players
		$shorts = $this->Team->find('all', array(
				'fields' => array(
					'Team.id', 'Team.name', 'Team.division_id',
					'COUNT(TeamsPerson.person_id) AS count',
				),
				'joins' => array(
					array(
						'table' => "{$this->Team->tablePrefix}teams_people",
						'alias' => 'TeamsPerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'Team.id = TeamsPerson.team_id',
					),
				),
				'conditions' => array(
					'Team.division_id' => array_keys($divisions),
					'TeamsPerson.role' => Configure::read('playing_roster_roles'),
				),
				'contain' => false,
				'group' => 'Team.id HAVING count < 12',
		));
		foreach ($shorts as $key => $short) {
			$shorts[$key][0]['subs'] = $this->Team->TeamsPerson->find('count', array(
					'conditions' => array(
						'TeamsPerson.team_id' => $short['Team']['id'],
						'TeamsPerson.role' => 'substitute',
					),
			));
			$shorts[$key] += $divisions[$short['Team']['division_id']];
		}
		usort($shorts, array('TeamsController', 'compareAffiliateAndCount'));

		$top_rating = $lowest_rating = $top_spirit = $lowest_spirit = array();
		foreach (array_keys($affiliates) as $affiliate) {
			$affiliate_divisions = Set::extract("/League[affiliate_id=$affiliate]/../Division/id", $divisions);

			// Get the list of top-rated teams
			$top_rating = array_merge($top_rating, $this->Team->find('all', array(
					'fields' => array(
						'Team.id', 'Team.name', 'Team.division_id', 'Team.rating',
					),
					'conditions' => array('division_id' => $affiliate_divisions),
					'contain' => false,
					'order' => 'Team.rating DESC',
					'limit' => 10,
			)));

			// Get the list of lowest-rated teams
			$lowest_rating = array_merge($lowest_rating, $this->Team->find('all', array(
					'fields' => array(
						'Team.id', 'Team.name', 'Team.division_id', 'Team.rating',
					),
					'conditions' => array('division_id' => $affiliate_divisions),
					'contain' => false,
					'order' => 'Team.rating ASC',
					'limit' => 10,
			)));

			if (Configure::read('feature.spirit')) {
				// Find the list of unplayed games
				$unplayed = $this->Team->Division->Game->find('list', array(
						'conditions' => array(
							'division_id' => $affiliate_divisions,
							'status' => Configure::read('unplayed_status'),
						),
				));

				// Get the list of top spirited teams
				$top_spirit = array_merge($top_spirit, $this->Team->find('all', array(
						'fields' => array(
							'Team.id', 'Team.name', 'Team.division_id',
							'ROUND( AVG( COALESCE(
								SpiritEntry.entered_sotg,
								SpiritEntry.score_entry_penalty + SpiritEntry.q1 + SpiritEntry.q2 + SpiritEntry.q3 + SpiritEntry.q4 + SpiritEntry.q5 + SpiritEntry.q6 + SpiritEntry.q7 + SpiritEntry.q8 + SpiritEntry.q9 + SpiritEntry.q10 )
							), 2) AS avgspirit',
						),
						'joins' => array(
							array(
								'table' => "{$this->Team->tablePrefix}spirit_entries",
								'alias' => 'SpiritEntry',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'SpiritEntry.team_id = Team.id',
							),
						),
						'conditions' => array(
							'division_id' => $affiliate_divisions,
							'NOT' => array('game_id' => $unplayed),
						),
						'contain' => false,
						'group' => 'Team.id HAVING avgspirit IS NOT NULL',
						'order' => array('avgspirit DESC', 'Team.name'),
						'limit' => 10,
				)));

				// Get the list of lowest spirited teams
				$lowest_spirit = array_merge($lowest_spirit, $this->Team->find('all', array(
						'fields' => array(
							'Team.id', 'Team.name', 'Team.division_id',
							'ROUND( AVG( COALESCE(
								SpiritEntry.entered_sotg,
								SpiritEntry.score_entry_penalty + SpiritEntry.q1 + SpiritEntry.q2 + SpiritEntry.q3 + SpiritEntry.q4 + SpiritEntry.q5 + SpiritEntry.q6 + SpiritEntry.q7 + SpiritEntry.q8 + SpiritEntry.q9 + SpiritEntry.q10 )
							), 2) AS avgspirit',
						),
						'joins' => array(
							array(
								'table' => "{$this->Team->tablePrefix}spirit_entries",
								'alias' => 'SpiritEntry',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'SpiritEntry.team_id = Team.id',
							),
						),
						'conditions' => array(
							'division_id' => $affiliate_divisions,
							'NOT' => array('game_id' => $unplayed),
						),
						'contain' => false,
						'group' => 'Team.id HAVING avgspirit IS NOT NULL',
						'order' => array('avgspirit ASC', 'Team.name'),
						'limit' => 10,
				)));
			}
		}

		// Add division info
		foreach ($top_rating as $key => $team) {
			$top_rating[$key] += $divisions[$team['Team']['division_id']];
		}
		foreach ($lowest_rating as $key => $team) {
			$lowest_rating[$key] += $divisions[$team['Team']['division_id']];
		}
		foreach ($top_spirit as $key => $team) {
			$top_spirit[$key] += $divisions[$team['Team']['division_id']];
		}
		foreach ($lowest_spirit as $key => $team) {
			$lowest_spirit[$key] += $divisions[$team['Team']['division_id']];
		}

		// Get the list of defaulting teams
		$defaulting = $this->Team->Division->Game->find('all', array(
				'fields' => array(
					'Game.division_id',
					'IF(Game.status = "home_default",HomeTeam.id,AwayTeam.id) AS id',
					'IF(Game.status = "home_default",HomeTeam.name,AwayTeam.name) AS name',
					'COUNT(Game.id) AS count',
				),
				'joins' => array(
					array(
						'table' => "{$this->Team->tablePrefix}teams",
						'alias' => 'HomeTeam',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'HomeTeam.id = Game.home_team',
					),
					array(
						'table' => "{$this->Team->tablePrefix}teams",
						'alias' => 'AwayTeam',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AwayTeam.id = Game.away_team',
					),
				),
				'conditions' => array(
					'Game.division_id' => array_keys($divisions),
					'Game.status' => array('home_default', 'away_default'),
				),
				'contain' => false,
				'group' => 'id',
		));

		// Add division info
		foreach ($defaulting as $key => $game) {
			$defaulting[$key] += $divisions[$game['Game']['division_id']];
		}
		usort($defaulting, array('TeamsController', 'compareAffiliateAndCount'));

		// Get the list of non-score-submitting teams
		$no_scores = $this->Team->Division->Game->find('all', array(
				'fields' => array(
					'Game.division_id',
					'IF(Game.approved_by = ' . APPROVAL_AUTOMATIC_HOME . ',HomeTeam.id,AwayTeam.id) AS id',
					'IF(Game.approved_by = ' . APPROVAL_AUTOMATIC_HOME . ',HomeTeam.name,AwayTeam.name) AS name',
					'COUNT(Game.id) AS count',
				),
				'joins' => array(
					array(
						'table' => "{$this->Team->tablePrefix}teams",
						'alias' => 'HomeTeam',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'HomeTeam.id = Game.home_team',
					),
					array(
						'table' => "{$this->Team->tablePrefix}teams",
						'alias' => 'AwayTeam',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AwayTeam.id = Game.away_team',
					),
				),
				'conditions' => array(
					'Game.division_id' => array_keys($divisions),
					'Game.approved_by' => array(APPROVAL_AUTOMATIC_HOME,APPROVAL_AUTOMATIC_AWAY),
				),
				'contain' => false,
				'group' => 'id',
		));

		// Add division info
		foreach ($no_scores as $key => $game) {
			$no_scores[$key] += $divisions[$game['Game']['division_id']];
		}
		usort($no_scores, array('TeamsController', 'compareAffiliateAndCount'));

		$this->Team->Division->contain();
		$years = $this->Team->Division->find('all', array(
			'fields' => 'DISTINCT YEAR(Division.open) AS year',
			'conditions' => array('YEAR(Division.open) !=' => 0),
			'order' => 'Division.open',
		));

		$this->set(compact('counts', 'shorts', 'top_rating', 'lowest_rating',
				'defaulting', 'no_scores', 'top_spirit', 'lowest_spirit',
				'year', 'years', 'divisions'));
	}

	static function compareAffiliateAndCount($a, $b) {
		if ($a['League']['Affiliate']['name'] > $b['League']['Affiliate']['name']) {
			return 1;
		} else if ($a['League']['Affiliate']['name'] < $b['League']['Affiliate']['name']) {
			return -1;
		}

		if ($a[0]['count'] > $b[0]['count']) {
			return 1;
		} else if ($a[0]['count'] < $b[0]['count']) {
			return -1;
		}

		if (array_key_exists('name', $a[0])) {
			if ($a[0]['name'] > $b[0]['name']) {
				return 1;
			} else if ($a[0]['name'] < $b[0]['name']) {
				return -1;
			}
		} else {
			if ($a['Team']['name'] > $b['Team']['name']) {
				return 1;
			} else if ($a['Team']['name'] < $b['Team']['name']) {
				return -1;
			}
		}

		return 0;
	}

	function view() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$contain = array(
			'Division' => array('Day', 'League'),
			'Franchise',
			'Region',
			'Field' => array('Facility'),
		);
		if ($this->is_logged_in || Configure::read('feature.public')) {
			$contain['Person'] = array();
			if (Configure::read('feature.annotations')) {
				$contain['Note'] = array('conditions' => array('created_person_id' => $this->Auth->user('id')));
			}

			if (Configure::read('feature.badges')) {
				$badge_obj = $this->_getComponent('Badge', '', $this);
				$contain['Person']['Badge'] = array('conditions' => array(
					'BadgesPerson.approved' => true,
					'Badge.visibility' => $badge_obj->visibility($this->is_admin || $this->is_manager, BADGE_VISIBILITY_HIGH),
				));
			}
		}
		$this->Team->contain($contain);

		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		if (isset($badge_obj)) {
			foreach (array_keys($team['Person']) as $key) {
				$badge_obj->prepForDisplay($team['Person'][$key]);
			}
		}

		$this->Team->Division->addPlayoffs($team);
		$this->_limitOverride($id);
		$team_days = Set::extract('/Division/Day/id', $team);

		if (!empty($team['Team']['division_id'])) {
			Configure::load("sport/{$team['Division']['League']['sport']}");
			if (Configure::read('feature.registration')) {
				$member_rule = "compare(member_type('{$team['Division']['open']}') != 'none')";
			}
		}

		if ($this->is_logged_in || Configure::read('feature.public')) {
			foreach ($team['Person'] as $key => $person) {
				// Get everything from the user record that the rule might need
				$this->Team->Person->contain(array(
					'Registration' => array(
						'Event' => array(
							'EventType',
						),
						'conditions' => array('Registration.payment' => 'paid'),
					),
					'Team' => array(
						'Division' => array('Day', 'League'),
						'TeamsPerson',
						'conditions' => array('Team.id !=' => $id),
					),
					'Waiver',
				));
				$full_person = $this->Team->Person->read(null, $person['id']);

				if ($person['TeamsPerson']['status'] == ROSTER_APPROVED) {
					$team['Person'][$key]['can_add'] = true;
				} else {
					$team['Person'][$key]['can_add'] = $this->_canAdd ($full_person, $team, $person['TeamsPerson']['role'], $person['TeamsPerson']['status'], true, true);
				}

				// Check if the player is a member, so we can highlight any that aren't
				if (isset ($member_rule)) {
					$rule_obj = AppController::_getComponent ('Rule');
					if (!$rule_obj->init ($member_rule)) {
						return __('Failed to parse the rule', true);
					}
					$team['Person'][$key]['is_a_member'] = $rule_obj->evaluate($team['Division']['League']['affiliate_id'], $full_person, $team);
				} else {
					// Ensure there's no warnings
					$team['Person'][$key]['is_a_member'] = true;
				}

				// Check for any roster conflicts
				$team['Person'][$key]['roster_conflict'] = $team['Person'][$key]['schedule_conflict'] = false;
				$playing_roster_roles = Configure::read('playing_roster_roles');
				foreach ($full_person['Team'] as $other_team) {
					if (in_array($person['TeamsPerson']['role'], $playing_roster_roles)) {
						// If this player is on a roster of another team in the same league...
						if (array_key_exists('league_id', $other_team['Division']) &&
							$team['Division']['league_id'] == $other_team['Division']['league_id'] &&
							// and they're a regular player...
							in_array($other_team['TeamsPerson']['role'], $playing_roster_roles))
						{
							$connected = false;
							if (array_key_exists('season_divisions', $team['Division']) &&
								in_array($other_team['Division']['id'], $team['Division']['season_divisions'])
							)
							{
								$connected = true;
							}
							if (array_key_exists('playoff_divisions', $team['Division']) &&
								in_array($other_team['Division']['id'], $team['Division']['playoff_divisions'])
							)
							{
								$connected = true;
							}

							// and that division doesn't have a regular season/playoff connection with this one...
							if (!$connected) {
								// ... then there's a roster conflict!
								$team['Person'][$key]['roster_conflict'] = true;
							}
						}
					}

					// If this player is on a roster of a team in another league...
					if (array_key_exists('league_id', $other_team['Division']) &&
						!empty ($team_days) && $team['Division']['league_id'] != $other_team['Division']['league_id'])
					{
						// that has a schedule which at least partially overlaps with this division...
						if (($other_team['Division']['open'] <= $team['Division']['open'] && $other_team['Division']['close'] >= $team['Division']['open']) ||
							($team['Division']['open'] <= $other_team['Division']['open'] && $team['Division']['close'] >= $other_team['Division']['open']))
						{
							$other_team_days = Set::extract('/Division/Day/id', $other_team);
							$overlap = array_intersect($team_days, $other_team_days);
							// and they play on the same day of the week...
							if (!empty($overlap)) {
								// ... then there's a possible schedule conflict!
								$team['Person'][$key]['schedule_conflict'] = true;
							}
						}   
					}
				}
			}

			usort ($team['Person'], array('Team', 'compareRoster'));
		}

		$this->set('team', $team);
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('is_coordinator', in_array($team['Team']['division_id'], $this->Session->read('Zuluru.DivisionIDs')));
		$this->_addTeamMenuItems ($team);

		if ($team['Division']['is_playoff']) {
			$affiliate_id = $this->_getAffiliateId($team['Division'], $team);
			if ($affiliate_id !== null) {
				$this->Team->contain(array('Division' => 'League'));
				$affiliate = $this->Team->read(null, $affiliate_id);
				$this->set(compact('affiliate'));
			}
		}
	}

	function numbers() {
		if (!Configure::read('feature.shirt_numbers')) {
			$this->Session->setFlash(__('Shirt numbers are not enabled on this system.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Team->contain(array(
			'Division' => array('League'),
			'Person',
		));

		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$is_captain = in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs'));

		$person_id = $this->_arg('person');
		if ($person_id) {
			$person = Set::extract("/Person[id=$person_id]/.", $team);
			if (empty($person)) {
				$this->Session->setFlash(__('That player is not on this team.', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'view', 'team' => $id));
			}
			$person = array_shift($person);
		} else if (!$this->effective_admin && !$this->effective_coordinator && !$is_captain) {
			$this->Session->setFlash(__('You do not have permission to set shirt numbers for this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$this->_limitOverride($id);
		usort ($team['Person'], array('Team', 'compareRoster'));

		$this->set(compact('team', 'is_captain', 'person_id', 'person'));

		if (!empty($this->data)) {
			if ($this->RequestHandler->isAjax()) {
				$this->action = 'numbers_ajax';
			}

			// Find the database IDs that go with the specified people. We don't trust putting
			// them in the form, as data could be spoofed to override other numbers.
			foreach ($this->data['TeamsPerson'] as $key => $record) {
				if ($person_id) {
					$roster_id = Set::extract("/Person/TeamsPerson[person_id={$person_id}]/id", $team);
				} else {
					$roster_id = Set::extract("/Person/TeamsPerson[person_id={$record['person_id']}]/id", $team);
				}
				if (empty($roster_id)) {
					$this->Session->setFlash(__('You cannot set shirt numbers for someone not on this team.', true), 'default', array('class' => 'info'));
					if ($this->RequestHandler->isAjax()) {
						return;
					}
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
				$this->data['TeamsPerson'][$key]['id'] = $roster_id[0];
			}
			if ($this->Team->TeamsPerson->saveAll($this->data['TeamsPerson'], array('validate' => 'first'))) {
				if ($this->RequestHandler->isAjax()) {
					$this->set('number', $this->data['TeamsPerson'][0]['number']);
					return;
				}
				if ($person_id) {
					$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('number', true)), 'default', array('class' => 'success'));
				} else {
					$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('numbers', true)), 'default', array('class' => 'success'));
				}
				$this->redirect(array('action' => 'view', 'team' => $id));
			} else {
				if ($this->RequestHandler->isAjax()) {
					return;
				}
				if ($person_id) {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('number', true)), 'default', array('class' => 'warning'));
				} else {
					$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('numbers', true)), 'default', array('class' => 'warning'));
				}
			}
		}

		$this->_addTeamMenuItems ($team);
	}

	function stats() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$contain = array(
			'Division' => array(
				'League' => array('StatType' => array('conditions' => array('StatType.type' => Configure::read('stat_types.team')))),
				'Day',
			),
			'Person' => array('conditions' => array('TeamsPerson.role' => Configure::read('extended_playing_roster_roles'))),
		);
		if (Configure::read('feature.annotations') && $this->params['url']['ext'] != 'csv') {
			$contain['Note'] = array('conditions' => array('created_person_id' => $this->Auth->user('id')));
		}
		$this->Team->contain($contain);

		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (!League::hasStats($team['Division']['League'])) {
			$this->Session->setFlash(__('This league does not have stat tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		$this->_limitOverride($id);

		if (!empty($team['Team']['division_id'])) {
			Configure::load("sport/{$team['Division']['League']['sport']}");
		}
		$sport_obj = $this->_getComponent ('Sport', $team['Division']['League']['sport'], $this);

		// Hopefully, everything we need is already cached
		$cache_file = CACHE . 'queries' . DS . "team_stats_{$id}.data";
		if (file_exists($cache_file)) {
			$team += unserialize(file_get_contents($cache_file));
		} else {
			// Calculate some stats. We need to get stats from any team in this
			// division, so that it properly handles subs and people who move teams.
			$teams = $this->Team->find('list', array(
					'conditions' => array('division_id' => $team['Division']['id']),
			));
			$stats = $this->Team->Stat->find('all', array(
					'conditions' => array(
						'person_id' => Set::extract('/Person/id', $team),
						'team_id' => array_keys($teams),
					),
					'contain' => array(),
			));
			$team['Stat'] = array();
			foreach ($stats as $stat) {
				$team['Stat'][] = $stat['Stat'];
			}
			$team['Calculated'] = array();

			foreach ($team['Division']['League']['StatType'] as $stat_type) {
				switch ($stat_type['type']) {
					case 'season_total':
						$sport_obj->_season_total($stat_type, $team);
						break;
					case 'season_avg':
						$sport_obj->_season_avg($stat_type, $team);
						break;
					case 'season_calc':
						$func = "{$stat_type['handler']}_season";
						if (method_exists($sport_obj, $func)) {
							$sport_obj->$func($stat_type, $team);
						} else {
							trigger_error("Season stat handler {$stat_type['handler']} was not found in the {$stat_type['sport']} component!", E_USER_ERROR);
						}
						break;
				}
			}
			file_put_contents($cache_file, serialize(array(
					'Stat' => $team['Stat'],
					'Calculated' => $team['Calculated'],
			)));
		}

		usort ($team['Person'], array('Team', 'compareRoster'));

		$this->set(compact('team', 'sport_obj'));
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('is_coordinator', in_array($team['Team']['division_id'], $this->Session->read('Zuluru.DivisionIDs')));
		$this->_addTeamMenuItems ($team);

		if ($this->params['url']['ext'] == 'csv') {
			$this->set('download_file_name', "Stats - {$team['Team']['name']}");
			Configure::write ('debug', 0);
		}
	}

	function stat_sheet() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$contain = array(
			'Division' => array(
				'League' => array('StatType' => array('conditions' => array('StatType.type' => 'entered'))),
			),
			'Person',
		);
		$this->Team->contain($contain);

		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (!League::hasStats($team['Division']['League'])) {
			$this->Session->setFlash(__('This league does not have stat tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		if (Configure::read('feature.pdfize') && isset($this->Pdf)) {
			$this->Pdf->actionsToPdf = array($this->action);
		}

		$this->set(compact('team'));
	}

	function tooltip() {
		$id = $this->_arg('team');
		if (!$id) {
			return;
		}
		$contain = array(
			// Get the list of captains
			'Person' => array(
				'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
				'fields' => array('id', 'first_name', 'last_name'),
			),
			'Division' => array('League'),
		);
		if (Configure::read('feature.annotations') && $this->is_logged_in) {
			$contain['Note'] = array('conditions' => array('created_person_id' => $this->Auth->user('id')));
		}
		$this->Team->contain($contain);

		$team = $this->Team->read(null, $id);
		if (!$team) {
			return;
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->set(compact('team'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function add() {
		if (!$this->is_admin && Configure::read('feature.registration')) {
			$this->Session->setFlash (__('This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email ' . Configure::read('email.admin_email') . ' with the details, or call the office.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty($this->data)) {
			$this->Team->create();
			if (!$this->is_admin && (!empty($this->data['Team']['affiliate_id']) && !in_array($this->data['Team']['affiliate_id'], $this->Session->read('Zuluru.ManagedAffiliateIDs')))) {
				$this->data['Person'] = array(array(
					'person_id' => $this->Auth->user('id'),
					'role' => 'captain',
					'status' => ROSTER_APPROVED,
				));
			}

			if ($this->Team->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved, but will not be visible until approved', true), __('team', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Team']['affiliate_id']);
			}
		}
		$affiliates = $this->_applicableAffiliates();
		$regions = $this->Team->Division->Game->GameSlot->Field->Facility->Region->find('list', array(
				'conditions' => array('affiliate_id' => array_keys($affiliates)),
		));
		$fields = $this->Team->Division->Game->GameSlot->Field->find('all', array(
				'contain' => 'Facility',
				'conditions' => array(
					'Facility.region_id' => array_keys($regions),
					'Facility.is_open' => true,
					'Field.is_open' => true,
				),
				'order' => array('Facility.name', 'Field.num'),
		));
		$fields = Set::combine($fields, '{n}.Field.id', '{n}.Field.long_name');
		$this->set(compact('affiliates', 'regions', 'fields'));

		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('team');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Team->save($this->data)) {
				if (in_array ($this->data['Team']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
					$this->_deleteTeamSessionData();
				}
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('team', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Team->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Team->contain(array('Division' => 'League'));
			$this->data = $this->Team->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Division']['League']['affiliate_id']);
		}
		$division_id = $this->Team->field('division_id', array('id' => $id));
		$league_id = $this->Team->Division->field('league_id', array('id' => $division_id));
		$sport = $this->Team->Division->League->field('sport', array('id' => $league_id));
		Configure::load("sport/$sport");

		$affiliates = $this->_applicableAffiliates();
		$regions = $this->Team->Division->Game->GameSlot->Field->Facility->Region->find('list', array(
				'conditions' => array('affiliate_id' => array_keys($affiliates)),
		));
		$fields = $this->Team->Division->Game->GameSlot->Field->find('all', array(
				'contain' => 'Facility',
				'conditions' => array(
					'Facility.region_id' => array_keys($regions),
					'Facility.is_open' => true,
					'Field.is_open' => true,
				),
				'order' => array('Facility.name', 'Field.num'),
		));
		$fields = Set::combine($fields, '{n}.Field.id', '{n}.Field.long_name');
		$this->set(compact('affiliates', 'regions', 'fields'));
	}

	function note() {
		$id = $this->_arg('team');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->set(compact('id', 'my_id'));

		if (!empty($this->data)) {
			// Check that this user is allowed to edit this note
			if (!empty($this->data['Note'][0]['id'])) {
				$created = $this->Team->Note->field('created_person_id', array('id' => $this->data['Note'][0]['id']));
				if ($created != $my_id) {
					$this->Session->setFlash(sprintf(__('You are not allowed to edit that %s.', true), __('note', true)), 'default', array('class' => 'error'));
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
			}

			$this->data['Note'][0]['team_id'] = $id;
			$this->data['Note'][0]['visibility'] = VISIBILITY_PRIVATE;
			if (empty($this->data['Note'][0]['note'])) {
				if (!empty($this->data['Note'][0]['id'])) {
					if ($this->Team->Note->delete($this->data['Note'][0]['id'])) {
						$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
					} else {
						$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
					}
				} else {
					$this->Session->setFlash(__('You entered no text, so no note was added.', true), 'default', array('class' => 'warning'));
				}
				$this->redirect(array('action' => 'view', 'team' => $id));
			} else if ($this->Team->Note->save($this->data['Note'][0])) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('note', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'view', 'team' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('note', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Team->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Team->contain(array(
					'Note' => array('conditions' => array('created_person_id' => $my_id)),
					'Division' => 'League',
			));

			$this->data = $this->Team->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Division']['League']['affiliate_id']);
		}

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete_note() {
		$id = $this->_arg('team');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$note_id = $this->Team->Note->field('id', array('team_id' => $id, 'created_person_id' => $my_id));
		if (!$note_id) {
			$this->Session->setFlash(sprintf(__('You do not have a note on that %s.', true), __('team', true)), 'default', array('class' => 'warning'));
		} else if ($this->Team->Note->delete($note_id)) {
			$this->Session->setFlash(sprintf(__('The %s has been deleted', true), __('note', true)), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Note', true)), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'team' => $id));
	}

	function delete() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		$dependencies = $this->Team->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this team, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Team->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Team', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Team', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	// TODO: Method for moving multiple teams at once; jQuery "left and right" boxes?
	function move() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array ('Division' => 'League'));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		if (!empty($this->data)) {
			$this->Team->Division->contain('League');
			$division = $this->Team->Division->read(null, $this->data['Team']['to']);
			// Don't do division comparisons when the team being moved is not in a division
			if ($team['Division']['id']) {
				if ($team['Division']['league_id'] != $division['Division']['league_id']) {
					$this->Session->setFlash(__('Cannot move a team to a different league', true), 'default', array('class' => 'info'));
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
				if ($division['Division']['ratio'] != $team['Division']['ratio']) {
					$this->Session->setFlash(__('Destination division must have the same gender ratio', true), 'default', array('class' => 'info'));
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
			}
			if ($this->Team->saveField ('division_id', $this->data['Team']['to'])) {
				$this->Session->setFlash(sprintf (__('Team has been moved to %s', true), $division['Division']['full_league_name']), 'default', array('class' => 'success'));
			} else {
				$this->Session->setFlash(__('Failed to move the team!', true), 'default', array('class' => 'warning'));
			}
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$conditions = array('OR' => array(
				'Division.is_open' => true,
				'Division.open > CURDATE()',
			),
			'League.affiliate_id' => $affiliates,
		);
		if ($team['Division']['id']) {
			$conditions += array(
					'Division.id !=' => $team['Division']['id'],
					'Division.league_id' => $team['Division']['league_id'],
					'Division.ratio' => $team['Division']['ratio'],
			);
		}
		$divisions = $this->Team->Division->find ('all', array(
			'conditions' => $conditions,
			'contain' => 'League',
		));

		// Make sure there's somewhere to move it to
		if (empty ($divisions)) {
			$this->Session->setFlash(__('No similar division found to move this team to!', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$this->set(compact('team', 'divisions'));
	}

	function schedule() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array('Division' => 'League'));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		Configure::load("sport/{$team['Division']['League']['sport']}");
		$this->Team->Division->Game->contain(array(
				'GameSlot' => array('Field' => 'Facility'),
				'ScoreEntry',
				'SpiritEntry',
				'HomeTeam',
				'HomePoolTeam' => 'DependencyPool',
				'AwayTeam',
				'AwayPoolTeam' => 'DependencyPool',
				'Attendance' => array(
					'Person' => array(
						'fields' => array('gender'),
					),
					'conditions' => array('Attendance.team_id' => $id, 'Attendance.status' => ATTENDANCE_ATTENDING),
				),
		));
		$team['Game'] = $this->Team->Division->Game->find('all', array(
				'conditions' => array(
					array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
					)),
					array('OR' => array(
						'Game.home_dependency_type !=' => 'copy',
						'Game.home_dependency_type' => null,
					)),
				),
		));

		// Find any non-game team events
		if (in_array ($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
			$team['Game'] = array_merge ($team['Game'], $this->Team->TeamEvent->_read_attendance($team));
		}

		if (empty ($team['Game'])) {
			$this->Session->setFlash(__('This team has no games scheduled yet.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		// Sort games by date, time and field
		usort ($team['Game'], array ('Game', 'compareDateAndField'));

		$this->set(compact('team'));
		$this->set('is_coordinator', in_array($team['Team']['division_id'], $this->Session->read('Zuluru.DivisionIDs')));
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $team['Division']['League']['sotg_questions'], $this));
		$this->set('display_attendance', $team['Team']['track_attendance'] && in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')));
		$this->set('annotate', Configure::read('feature.annotations') && in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')));
		$this->_addTeamMenuItems ($this->Team->data);
	}

	// This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	function ical($id) {
		$this->layout = 'ical';
		if (!$id) {
			return;
		}

		$this->Team->contain(array ('Division' => 'League'));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			return;
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->Team->Division->Game->contain(array(
				'GameSlot' => array('Field' => 'Facility'),
				'HomeTeam',
				'AwayTeam',
		));
		$team['Game'] = $this->Team->Division->Game->find('all', array(
				'conditions' => array(
					array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
					)),
					'Game.published' => true,
					array('OR' => array(
						'Game.home_dependency_type !=' => 'copy',
						'Game.home_dependency_type' => null,
					)),
				),
		));

		// Sort games by date, time and field
		usort ($team['Game'], array ('Game', 'compareDateAndField'));
		// Outlook only accepts the first event in a file, so we put the last game first
		$team['Game'] = array_reverse ($team['Game']);

		$this->set ('calendar_type', 'Team Schedule');
		$this->set ('calendar_name', "{$team['Team']['name']} schedule");
		$this->set('team_id', $id);
		$this->set('games', $team['Game']);

		Configure::write ('debug', 0);
	}

	function spirit() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array ('Division' => 'League'));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->Team->Division->Game->contain(array(
				'GameSlot',
				'HomeTeam',
				'AwayTeam',
				'SpiritEntry',
		));
		$team['Game'] = $this->Team->Division->Game->find('all', array(
				'conditions' => array(
					array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
					)),
					array('OR' => array(
						'Game.home_dependency_type !=' => 'copy',
						'Game.home_dependency_type' => null,
					)),
				),
		));
		if (empty ($team['Game'])) {
			$this->Session->setFlash(__('This team has no games scheduled yet.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		// Sort games by date, time and field
		usort ($team['Game'], array ('Game', 'compareDateAndField'));

		$this->set(compact('team'));
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $team['Division']['League']['sotg_questions'], $this));
		$this->_addTeamMenuItems ($this->Team->data);
	}

	function attendance() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Team->contain(array(
			'Division' => array('Day', 'League'),
			'Person' => array(
				'conditions' => array('TeamsPerson.status' => ROSTER_APPROVED),
			),
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		if (!$team['Team']['track_attendance']) {
			$this->Session->setFlash(__('That team does not have attendance tracking enabled.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// Find the list of holidays to avoid
		$holiday = ClassRegistry::init('Holiday');
		$holidays = $holiday->find('list', array('fields' => array('Holiday.date', 'Holiday.name')));
		$this->set(compact('holidays'));

		$dates = array();
		$days = Set::extract('/Division/Day/id', $team);
		$play_day = min($days);
		for ($date = strtotime ($team['Division']['open']); $date <= strtotime ($team['Division']['close']) + DAY - 1; $date += DAY) {
			$day = date('w', $date) + 1;
			if ($day == $play_day && !array_key_exists(date('Y-m-d', $date), $holidays)) {
				$dates[] = date('Y-m-d', $date);
			}
		}
		$attendance = $this->Team->Division->Game->_read_attendance($team, $days, null, $dates);
		$event_attendance = $this->Team->TeamEvent->_read_attendance($team);

		$this->Team->Division->Game->contain(array(
			'GameSlot' => array('Field' => 'Facility'),
			'HomeTeam',
			'AwayTeam',
		));
		$games = $this->Team->Division->Game->find('all', array(
				'conditions' => array(
					array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
					)),
					'Game.published' => true,
					array('OR' => array(
						'Game.home_dependency_type !=' => 'copy',
						'Game.home_dependency_type' => null,
					)),
				),
				'order' => array('GameSlot.game_date', 'GameSlot.game_start'),
		));

		$this->set(compact('team', 'attendance', 'event_attendance', 'dates', 'days', 'games'));
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
	}

	function emails() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array (
			'Person' => array(
				'fields' => array(
					'Person.first_name', 'Person.last_name', 'Person.email',
				),
				'order' => array(
					'TeamsPerson.role', 'Person.gender DESC', 'Person.last_name', 'Person.first_name',
				),
				'conditions' => array(
					'Person.id !=' => $this->Auth->User('id'),
					'TeamsPerson.status' => ROSTER_APPROVED,
				),
			),
			'Division' => 'League',
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		$this->set(compact('team'));
	}

	function add_player() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array('Division' => 'League'));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}

		// To avoid abuses, whether intentional or accidental, we limit the permissions
		// of admins and coordinators when managing teams they are on.
		$this->_limitOverride($id);
		$this->set('is_coordinator', $this->effective_coordinator);

		if (!$this->effective_admin && Division::rosterDeadlinePassed($team['Division'])) {
			$this->Session->setFlash(__('The roster deadline for this division has already passed.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$this->set(compact('team'));

		$params = $url = $this->_extractSearchParams();
		unset ($params['team']);
		$this->_handlePersonSearch($params, $url, $this->Team->Person);

		$this->Team->Person->contain(array (
			'Team' => array(
				'Division' => 'League',
				'order' => 'Team.id desc',
			),
		));
		$teams = $this->Team->Person->read(null, $this->Auth->User('id'));
		// Only show teams from divisions that have some schedule type
		// TODO: May need to change this once we can schedule playoffs
		$teams = Set::extract("/Division[id!={$team['Team']['division_id']}][schedule_type!=none]/..", $teams['Team']);
		$this->set(compact('teams'));

		// Admins and coordinators get to add people based on registration events
		if ($this->effective_admin || $this->effective_coordinator) {
			$this->Team->Person->Registration->Event->contain();
			$events = $this->Team->Person->Registration->Event->find('all', array(
					'conditions' => array(
						'Event.open < NOW()',
						'Event.close > DATE_ADD(CURDATE(), INTERVAL -30 DAY)',
					),
					'order' => array('Event.event_type_id', 'Event.open', 'Event.close', 'Event.id'),
			));
			$this->set(compact('events'));
		}
	}

	function add_from_team() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (empty ($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		// Read the current team roster, just need the ids
		$this->Team->contain(array (
			'Person' => array(
				'fields' => array(
					'Person.id',
					'Person.gender',
				),
			),
			'Franchise',
			// We need league information for sending out invites, may as well read it now
			'Division' => array(
				'Day',
				'League',
			),
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->Team->Division->addPlayoffs($team);
		$this->_limitOverride($id);

		// Read the old team roster
		$this->Team->contain(array (
			'Division' => 'League',
			'Person' => array(
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.status',
					'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
					'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
				),
				'order' => array(
					'Person.gender DESC', 'Person.last_name', 'Person.first_name',
				),
				// Only include people that aren't yet on the new roster
				'conditions' => array('NOT' => array('Person.id' => Set::extract('/Person/id', $team))),
			),
		));
		$old_team = $this->Team->read(null, $this->data['team']);
		if (!$old_team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		// If this is a form submission, set the role to 'player' for each player
		if (array_key_exists ('player', $this->data)) {
			$success = $failure = array();
			foreach ($this->data['player'] as $player => $bool) {
				$person = array_shift (Set::extract("/Person[id=$player]", $old_team));
				unset ($person['Person']['TeamsPerson']);
				if ($this->_setRosterRole ($person, $team, 'player', ROSTER_INVITED)) {
					$success[] = $person['Person']['full_name'];
				} else {
					$failure[] = $person['Person']['full_name'];
				}
			}
			$msg = array();
			if (!empty ($success)) {
				$msg[] = __((count($success) > 1 ? 'Invitations have' : 'Invitation has') . ' been sent to ', true) . implode (', ', $success) . '.';
				$class = 'success';
			}
			if (!empty ($failure)) {
				$msg[] .= __('Failed to send invitation' . (count($success) > 1 ? 's' : '') . ' to ', true) . implode (', ', $failure) . '.';
				$class = 'warning';
			}
			$this->Session->setFlash(implode (' ', $msg), 'default', array('class' => $class));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		foreach ($old_team['Person'] as $key => $person) {
			$old_team['Person'][$key]['can_add'] = $this->_canAdd (array('Person' => $person), $team, 'player', null, false, true);
		}

		$this->set(compact('team', 'old_team'));
	}

	function add_from_event() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		if (empty ($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		// Read the event
		$this->Team->Person->Registration->Event->contain();
		$event = $this->Team->Person->Registration->Event->read(null, $this->data['event']);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain(array (
			// We need league information for sending out invites, may as well read it now
			'Division' => array(
				'Day',
				'League',
			),
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->Team->Division->addPlayoffs($team);
		$this->_limitOverride($id);

		// Find all divisions in the same league
		$this->Team->Division->contain();
		$divisions = $this->Team->Division->find('all', array(
				'conditions' => array(
					'Division.league_id' => $team['Division']['league_id'],
				),
		));

		$this->Team->contain(array(
				'Person' => array(
					'fields' => array(
						'Person.id',
					),
				),
		));
		$teams = $this->Team->find('all', array('conditions' => array('Team.division_id' => Set::extract('/Division/id', $divisions))));
		$current = Set::extract('/Person/id', $teams);

		// Only include people that aren't yet on the new roster
		// or the roster of another team in the same league
		$conditions = array(
			'Registration.event_id' => $this->data['event'],
			'Registration.payment' => 'Paid',
			'NOT' => array('Person.id' => $current),
		);

		// Read the list of registrations
		$this->Team->Person->Registration->contain(array (
			'Person' => array(
				'fields' => array(
					'Person.id', 'Person.gender', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.status',
					'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
					'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
				),
				'conditions' => $conditions,
			),
		));
		$event['Registration'] = $this->Team->Person->Registration->find('all', array(
				'conditions' => $conditions,
		));
		usort ($event['Registration'], array('Person', 'comparePerson'));

		// If this is a form submission, set the role to 'player' for each player
		if (array_key_exists ('player', $this->data)) {
			$success = $failure = array();
			foreach ($this->data['player'] as $player => $bool) {
				$person = array_shift (Set::extract("/Registration/Person[id=$player]", $event));
				unset ($person['Person']['TeamsPerson']);
				// Only admins have this option, typically used for building hat teams,
				// so their adds are always approved
				if ($this->_setRosterRole ($person, $team, 'player', ROSTER_APPROVED)) {
					$success[] = $person['Person']['full_name'];
				} else if ($this->_setRosterRole ($person, $team, 'player', ROSTER_INVITED)) {
					$success[] = $person['Person']['full_name'];
				} else {
					$failure[] = $person['Person']['full_name'];
				}
			}
			$msg = array();
			if (!empty ($success)) {
				$msg[] = __((count($success) > 1 ? 'Invitations have' : 'Invitation has') . ' been sent to ', true) . implode (', ', $success) . '.';
				$class = 'success';
			}
			if (!empty ($failure)) {
				$msg[] .= __('Failed to send invitation' . (count($success) > 1 ? 's' : '') . ' to ', true) . implode (', ', $failure) . '.';
				$class = 'warning';
			}
			$this->Session->setFlash(implode (' ', $msg), 'default', array('class' => $class));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		foreach ($event['Registration'] as $key => $registration) {
			// People that are already on the roster will have an empty Person array
			if (empty($registration['Person'])) {
				unset ($event['Registration'][$key]);
			} else {
				$event['Registration'][$key]['can_add'] = $this->_canAdd (array('Person' => $registration['Person']), $team, 'player', null, false, true);
			}
		}

		$this->set(compact('team', 'event'));
	}

	function roster_role() {
		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		list ($team, $person) = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if ($this->RequestHandler->isAjax()) {
			$this->action = 'roster_role_ajax';
			$this->set('success', false);
		}

		if (empty ($person)) {
			$this->Session->setFlash(__('This player is not on this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		$role = $person['Person']['TeamsPerson']['role'];
		$roster_role_options = $this->_rosterRoleOptions ($role, $team, $person_id);
		$this->set(compact('person', 'team', 'role', 'roster_role_options'));

		if ($person['Person']['TeamsPerson']['status'] != ROSTER_APPROVED) {
			$this->Session->setFlash(__('A player\'s role on a team cannot be changed until they have been approved on the roster.', true), 'default', array('class' => 'info'));
			if ($this->RequestHandler->isAjax()) {
				return;
			}
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Check if this user is the only approved captain on the team
		if ($role == 'captain') {
			if (count (Set::extract ('/Person/TeamsPerson[role=captain][status=' . ROSTER_APPROVED . ']', $team)) == 1) {
				$this->Session->setFlash(__('All teams must have at least one player as captain.', true), 'default', array('class' => 'info'));
			if ($this->RequestHandler->isAjax()) {
				return;
			}
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['role'], $roster_role_options)) {
				$this->Session->setFlash(__('You do not have permission to set that role.', true), 'default', array('class' => 'info'));
			} else {
				if ($this->_setRosterRole ($person, $team, $this->data['Person']['role'], ROSTER_APPROVED)) {
					if ($person_id == $my_id) {
						$this->_deleteTeamSessionData();
					}
					if ($this->RequestHandler->isAjax()) {
						$this->set(array(
							'success' => true,
							'role' => $this->data['Person']['role'],
						));
						// These get overwritten by _setRosterRole and need to be reset
						$this->set(compact('person', 'team'));
					} else {
						$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
					}
				}
			}
		}
	}

	function roster_position() {
		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		list ($team, $person) = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if ($this->RequestHandler->isAjax()) {
			$this->action = 'roster_position_ajax';
			$this->set('success', false);
		}

		if (empty ($person)) {
			$this->Session->setFlash(__('This player is not on this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		if (!empty($team['Team']['division_id'])) {
			Configure::load("sport/{$team['Division']['League']['sport']}");
		}
		$position = $person['Person']['TeamsPerson']['position'];
		$roster_position_options = Configure::read('sport.positions');
		$this->set(compact('person', 'team', 'position', 'roster_position_options'));

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['position'], $roster_position_options)) {
				$this->Session->setFlash(__('That is not a valid position.', true), 'default', array('class' => 'info'));
			} else {
				$this->Roster = ClassRegistry::init ('TeamsPerson');
				$this->Roster->id = $person['Person']['TeamsPerson']['id'];
				if ($this->Roster->saveField ('position', $this->data['Person']['position'])) {
					if ($person_id == $my_id) {
						$this->_deleteTeamSessionData();
						$this->_initSessionData($my_id);
					}
					if ($this->RequestHandler->isAjax()) {
						$this->set(array(
							'success' => true,
							'position' => $this->data['Person']['position'],
						));
					} else {
						$this->Session->setFlash(__('Changed the player\'s position.', true), 'default', array('class' => 'success'));
						$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
					}
				} else {
					$this->Session->setFlash(__('Failed to change the player\'s position.', true), 'default', array('class' => 'warning'));
				}
			}
		}
	}

	function roster_add() {
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		list ($team, $player) = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (!empty ($player)) {
			$this->Session->setFlash(__('This player is already on this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Read the bare player record
		$this->Team->Person->contain();
		$person = $this->Team->Person->read(null, $person_id);

		// If a role was submitted, try to set it. Whether it succeeds or fails,
		// we'll go back to the team view page, and the flash message will tell the
		// user why. It should only fail in the case of malicious form tinkering, so
		// we don't try hard to let them correct the error.
		if (!empty($this->data)) {
			$this->_setRosterRole ($person, $team, $this->data['Person']['role'], ROSTER_INVITED);
			$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd ($person, $team, null, null, false);
		if ($can_add !== true) {
			// If not, we may still allow the invitation, but give the captain a warning
			$can_invite = $this->_canInvite ($person, $team);
			if ($can_invite !== true) {
				$this->Session->setFlash($can_invite, 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		$roster_role_options = $this->_rosterRoleOptions ('none', $team, $person_id);
		$adding = ($can_add === true &&
			($team['Division']['roster_method'] == 'add' || $this->effective_admin));

		$this->set(compact('person', 'team', 'roster_role_options', 'can_add', 'adding'));
	}

	function roster_request() {
		$my_id = $this->Auth->user('id');

		list ($team, $person) = $this->_initTeamForRosterChange($my_id);
		$team_id = $team['Team']['id'];

		if (!empty ($person)) {
			$this->Session->setFlash(__('You are already on this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Read the bare player record
		$this->Team->Person->contain();
		$person = $this->Team->Person->read(null, $my_id);

		// Check if this person can even be added
		$can_add = $this->_canAdd ($person, $team);
		if ($can_add !== true) {
			$this->Session->setFlash($can_add, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We're not already on this team, so the "effective" calculations won't
		// have blocked us, but we still don't want to give overrides for joining.
		$this->effective_admin = $this->effective_coordinator = false;
		$roster_role_options = $this->_rosterRoleOptions ('none', $team, $my_id);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['role'], $roster_role_options)) {
				$this->Session->setFlash(__('You are not allowed to request that role.', true), 'default', array('class' => 'info'));
			} else {
				if ($this->_setRosterRole ($person, $team, $this->data['Person']['role'], ROSTER_REQUESTED)) {
					$this->_deleteTeamSessionData();
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'roster_role_options'));
	}

	function roster_accept() {
		$my_id = $this->Auth->user('id');
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		list ($team, $person) = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (empty ($person)) {
			$this->Session->setFlash(__('This player has neither been invited nor requested to join this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		if ($person['Person']['TeamsPerson']['status'] == ROSTER_APPROVED) {
			$this->Session->setFlash(__('This player has already been added to the roster.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($person['Person']['TeamsPerson']);
			// Temporary addition during hash conversion period
			$hash2 = $this->_hash($person['Person']['TeamsPerson'], false);
			if ($hash != $code && $hash2 != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		} else {
			// Check for coordinator or admin override
			if (!$this->effective_admin && !$this->effective_coordinator &&
				// Players can accept when they are invited
				!($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED && $person_id == $this->Auth->user('id')) &&
				// Captains can accept requests to join their teams
				!($person['Person']['TeamsPerson']['status'] == ROSTER_REQUESTED && in_array ($team_id, $this->Session->read('Zuluru.OwnedTeamIDs')))
			)
			{
				$this->Session->setFlash(sprintf (__('You are not allowed to accept this roster %s.', true),
						__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
					'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd ($person, $team, $person['Person']['TeamsPerson']['role'], $person['Person']['TeamsPerson']['status']);
		if ($can_add !== true) {
			if ($this->is_logged_in && !empty($this->can_add_rule_obj->redirect)) {
				$this->redirect(array_merge($this->can_add_rule_obj->redirect, array('return' => true)), $this->here);
			}
			$this->Session->setFlash($can_add, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		$this->Roster = ClassRegistry::init ('TeamsPerson');
		$this->Roster->id = $person['Person']['TeamsPerson']['id'];
		if ($this->Roster->saveField ('status', ROSTER_APPROVED)) {
			$this->Session->setFlash(sprintf (__('You have accepted this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
				'default', array('class' => 'success'));

			// Send email to the affected people
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendAccept($person, $team, $person['Person']['TeamsPerson']['role'], $person['Person']['TeamsPerson']['status']);
			}

			if ($person_id == $my_id) {
				$this->_deleteTeamSessionData();
			}
		} else {
			$this->Session->setFlash(sprintf (__('The database failed to save the acceptance of this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
				'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'team' => $team_id));
	}

	function roster_decline() {
		$my_id = $this->Auth->user('id');
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		list ($team, $person) = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (empty ($person)) {
			$this->Session->setFlash(__('This player has neither been invited nor requested to join this team.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		if ($person['Person']['TeamsPerson']['status'] == ROSTER_APPROVED) {
			$this->Session->setFlash(__('This player has already been added to the roster.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($person['Person']['TeamsPerson']);
			// Temporary addition during hash conversion period
			$hash2 = $this->_hash($person['Person']['TeamsPerson'], false);
			if ($hash != $code && $hash2 != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		} else {
			// Check for coordinator or admin override
			if (!$this->effective_admin && !$this->effective_coordinator &&
				// Players or captains can either decline an invite or request from the other,
				// or remove one that they made themselves.
				!($person_id == $this->Auth->user('id')) &&
				!(in_array ($team_id, $this->Session->read('Zuluru.OwnedTeamIDs')))
			)
			{
				$this->Session->setFlash(sprintf (__('You are not allowed to decline this roster %s.', true),
						__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
					'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		$this->Roster = ClassRegistry::init ('TeamsPerson');
		if ($this->Roster->delete ($person['Person']['TeamsPerson']['id'])) {
			$this->Session->setFlash(sprintf (__('You have declined this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
				'default', array('class' => 'success'));

			// Send email to the affected people
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendDecline($person, $team, $person['Person']['TeamsPerson']['role'], $person['Person']['TeamsPerson']['status']);
			}

			if ($person_id == $my_id) {
				$this->_deleteTeamSessionData();
				$this->redirect('/');
			}
		} else {
			$this->Session->setFlash(sprintf (__('The database failed to save the removal of this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)),
				'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
	}

	function _initTeamForRosterChange($person_id) {
		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		// Read the team record
		$this->Team->contain(array(
			'Person' => array(
				'fields' => array(
					'Person.id',
					'Person.first_name',
					'Person.last_name',
					'Person.email',
					'Person.gender',
					'Person.status',
					'Person.complete',
				),
			),
			'Franchise',
			// We need league information for sending out invites, may as well read it now
			'Division' => array(
				'Day',
				'League',
			),
		));
		$team = $this->Team->read(null, $team_id);
		if (empty($team['Division']['id'])) {
			$this->Configuration->loadAffiliate($team['Team']['affiliate_id']);
		} else {
			$this->Configuration->loadAffiliate($team['Division']['League']['affiliate_id']);
		}
		$this->Team->Division->addPlayoffs($team);

		// Pull out the player record from the team, and make
		// it look as if we just read it
		$person = Set::extract ("/Person[id=$person_id]/.", $team);
		if (!empty ($person)) {
			$person = array('Person' => array_shift ($person));
		}

		// To avoid abuses, whether intentional or accidental, we limit the permissions
		// of admins when managing teams they are on.
		$this->_limitOverride($team_id);

		if (!$this->effective_admin && Division::rosterDeadlinePassed($team['Division'])) {
			$this->Session->setFlash(__('The roster deadline for this division has already passed.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		return array($team, $person);
	}

	function _rosterRoleOptions ($role, $team, $person_id) {
		// Some special handling for playoff teams
		if ($team['Division']['is_playoff']) {
			$roster_role_options = $this->_playoffRosterRoleOptions ($role, $team, $person_id);
		} else {
			$roster_role_options = Configure::read('options.roster_role');
		}

		// People that aren't on the team can't be "changed to" not on the team
		if ($role == 'none' || $role === null) {
			unset ($roster_role_options['none']);
		}

		// Admins, coordinators and captains can make anyone anything
		if ($this->effective_admin || $this->effective_coordinator ||
			in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs')))
		{
			return $roster_role_options;
		}

		// Non-captains are not allowed to promote themselves to captainly roles
		foreach (Configure::read('privileged_roster_roles') as $cap) {
			unset ($roster_role_options[$cap]);
		}

		switch ($role) {
			case 'substitute':
				// Subs can't make themselves regular players
				unset ($roster_role_options['player']);
				break;

			case 'none':
				if (!$team['Team']['open_roster']) {
					$this->Session->setFlash(__('Sorry, this team is not open for new players to join.', true), 'default', array('class' => 'info'));
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
		}

		// Whatever is left is okay
		return $roster_role_options;
	}

	function _playoffRosterRoleOptions ($role, $team, $person_id) {
		$roster_role_options = Configure::read('options.roster_role');

		$affiliate_id = $this->_getAffiliateId($team['Division'], $team);
		if ($affiliate_id !== null) {
			$this->Team->contain(array(
					'Person' => array('conditions' => array('Person.id' => $person_id))
			));
			$affiliate = $this->Team->read(null, $affiliate_id);

			// If the person wasn't on the affiliated team roster, then
			// they cannot take a "normal" role on the playoff roster.
			if (empty ($affiliate['Person'])) {
				foreach (Configure::read('playing_roster_roles') as $role) {
					unset ($roster_role_options[$role]);
				}
			}
		}
		return $roster_role_options;
	}

	function _setRosterRole ($person, $team, $role, $status) {
		if (!isset($this->Roster)) {
			$this->Roster = ClassRegistry::init ('TeamsPerson');
		}

		// We can always remove people from rosters
		if ($role == 'none') {
			$transaction = new DatabaseTransaction($this->Roster);
			if ($this->Roster->delete ($person['Person']['TeamsPerson']['id'])) {
				// Delete any future attendance records
				if ($this->Team->Attendance->deleteAll (array(
						'Attendance.team_id' => $team['Team']['id'],
						'Attendance.person_id' => $person['Person']['id'],
						'Attendance.game_date > CURDATE()',
				)))
				{
					$transaction->commit();
					$this->Session->setFlash(__('Removed the player from the team.', true), 'default', array('class' => 'success'));
					if (Configure::read('feature.generate_roster_email')) {
						$this->_sendRemove($person, $team);
					}
					return true;
				}
			}
			$this->Session->setFlash(__('Failed to remove the player from the team.', true), 'default', array('class' => 'warning'));
			return false;
		}

		$can_add = $this->_canAdd ($person, $team, $role, $status);
		if ($can_add === true) {
			// Under certain circumstances, an invite is changed to an add
			if ($status === ROSTER_INVITED &&
				($team['Division']['roster_method'] == 'add' || $this->effective_admin) &&
				// TODO: Rather than this, maybe somehow check if they were on the affiliate roster
				in_array($role, Configure::read('playing_roster_roles')))
			{
				$status = ROSTER_APPROVED;
			}
		} else {
			if ($status === ROSTER_INVITED) {
				// Redo the test, without being strict
				$can_add = $this->_canAdd ($person, $team, $role, $status, false, false, true);

				if ($can_add !== true) {
					// Set the reason that they can't be added for the email
					$this->set('accept_warning', $can_add);
					$can_add = $this->_canInvite ($person, $team, $role);
				}
			}
			if ($can_add !== true) {
				$this->Session->setFlash($can_add, 'default', array('class' => 'warning'));
				return false;
			}
		}

		if (empty ($person['Person']['TeamsPerson'])) {
			$this->Roster->create();
		} else {
			$this->Roster->id = $person['Person']['TeamsPerson']['id'];
		}

		$success = $this->Roster->save (array(
				'team_id' => $team['Team']['id'],
				'person_id' => $person['Person']['id'],
				'role' => $role,
				'status' => $status,
		));

		// If we were successful in the update, there may be emails to send
		if ($success) {
			if (!Configure::read('feature.generate_roster_email')) {
				return true;
			}

			$this->set('code', $this->_hash (array(
					'id' => $this->Roster->id,
					'team_id' => $team['Team']['id'],
					'person_id' => $person['Person']['id'],
					'role' => $role,
					'created' => date('Y-m-d'),
			)));

			if (empty ($person['Person']['TeamsPerson'])) {
				switch ($status) {
					case ROSTER_APPROVED:
						return $this->_sendAdd($person, $team, $role);

					case ROSTER_INVITED;
						return $this->_sendInvite($person, $team, $role);

					case ROSTER_REQUESTED:
						return $this->_sendRequest($person, $team, $role);
				}
			} else {
				return $this->_sendChange($person, $team, $role);
			}
		} else {
			$this->Session->setFlash(__('Failed to set player to that state.', true), 'default', array('class' => 'warning'));
			return false;
		}
	}

	function _canAdd ($person, $team, $role = null, $status = null, $strict = true, $text_reason = false, $absolute_url = false) {
		if ($person['Person']['status'] != 'active') {
			return __('New players must be approved by an administrator before they can be added to a team; this normally happens within one business day.', true);
		}
		if (array_key_exists ('complete', $person['Person']) && !$person['Person']['complete']) {
			return __('This player has not yet completed their profile.  Please contact this player directly to have them complete their profile.', true);
		}

		// Maybe use the rules engine to decide if this person can be added to this roster
		if (array_key_exists ('roster_rule', $team['Division']) && !empty ($team['Division']['roster_rule'])) {
			if (!isset($this->can_add_rule_obj)) {
				$this->can_add_rule_obj = AppController::_getComponent ('Rule', '', $this, true);
				if (!$this->can_add_rule_obj->init ($team['Division']['roster_rule'])) {
					return __('Failed to parse the rule', true);
				}
			}

			if (!array_key_exists('Registration', $person['Person']) || !array_key_exists('Team', $person['Person']) || !array_key_exists('Waiver', $person['Person'])) {
				// Get everything from the user record that the rule might need
				$this->Team->Person->contain(array(
					'Registration' => array(
						'Event' => array(
							'EventType',
						),
						'conditions' => array('Registration.payment' => 'paid'),
					),
					'Team' => array(
						'Division' => 'League',
						'TeamsPerson',
						'Franchise',
						'conditions' => array('Team.id !=' => $team['Team']['id']),
					),
					'Waiver',
				));

				$person = $this->Team->Person->read(null, $person['Person']['id']);
			}
			if (!$this->can_add_rule_obj->evaluate($team['Division']['League']['affiliate_id'], $person, $team, $strict, $text_reason, true, $absolute_url)) {
				switch ($this->can_add_rule_obj->reason_type) {
					case REASON_TYPE_PLAYER_ACTIVE:
						$prolog = 'To be added to this team, this player must first';
						break;

					case REASON_TYPE_PLAYER_PASSIVE:
						$prolog = 'This player';
						break;

					case REASON_TYPE_TEAM:
						$prolog = 'This team';
						break;
				}
				return __($prolog, true) . ' ' . $this->can_add_rule_obj->reason . '.';
			}
		}

		if ($role !== null && $status != ROSTER_INVITED) {
			$roster_role_options = $this->_rosterRoleOptions (null, $team, $person['Person']['id']);
			if (!array_key_exists ($role, $roster_role_options)) {
				return __('You are not allowed to invite someone to that role.', true);
			}
		}

		return true;
	}

	// TODO: Placeholder function for limiting who can even be invited onto rosters,
	// for example denying non-members the ability to be invited onto rosters
	function _canInvite ($person, $team, $role = null) {
		if ($role !== null) {
			$roster_role_options = $this->_rosterRoleOptions (null, $team, $person['Person']['id']);
			if (!array_key_exists ($role, $roster_role_options)) {
				return __('You are not allowed to invite someone to that role.', true);
			}
		}
		return true;
	}

	function _hash ($roster, $salt = true) {
		// Build a string of the inputs
		$input = "{$roster['id']}:{$roster['team_id']}:{$roster['person_id']}:{$roster['role']}:{$roster['created']}";
		if ($salt) {
			$input = $input . ':' . Configure::read('Security.salt');
		}
		return md5($input);
	}

	function _sendAdd ($person, $team, $role) {
		$this->_initRosterEmail($person, $team, $role);
		$this->set (array(
			'reply' => $this->Session->read('Zuluru.Person.email'),
		));

		if (!$this->_sendMail (array (
				'to' => $person,
				'replyTo' => $this->Session->read('Zuluru.Person'),
				'subject' => "You have been added to {$team['Team']['name']}",
				'template' => 'roster_add',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
			return false;
		}

		return true;
	}

	function _sendInvite ($person, $team, $role) {
		$this->_initRosterEmail($person, $team, $role);
		$this->set (array(
			'captain' => $this->Session->read('Zuluru.Person.full_name'),
		));

		if (!$this->_sendMail (array (
				'to' => $person,
				'replyTo' => $this->Session->read('Zuluru.Person'),
				'subject' => "Invitation to join {$team['Team']['name']}",
				'template' => 'roster_invite',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
			return false;
		}

		return true;
	}

	function _sendRequest ($person, $team, $role) {
		$this->_initRosterEmail($person, $team, $role);
		$captains = $this->_initRosterCaptains ($team);

		if (!$this->_sendMail (array (
				'to' => $captains,
				'replyTo' => $person,
				'subject' => "{$person['Person']['full_name']} requested to join {$team['Team']['name']}",
				'template' => 'roster_request',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
			return false;
		}

		return true;
	}

	function _sendAccept ($person, $team, $role, $status) {
		$this->_initRosterEmail($person, $team, $role);

		if ($status == ROSTER_INVITED) {
			// A player has accepted an invitation
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "{$person['Person']['full_name']} accepted your invitation to join {$team['Team']['name']}",
					'template' => 'roster_accept_invite',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
				return false;
			}
		} else {
			// A captain has accepted a request
			$this->set (array(
				'captain' => $this->Session->read('Zuluru.Person.full_name'),
			));

			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Session->read('Zuluru.Person'),
					'subject' => "Request to join {$team['Team']['name']} was accepted",
					'template' => 'roster_accept_request',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
				return false;
			}
		}
		return true;
	}

	function _sendDecline ($person, $team, $role, $status) {
		$this->_initRosterEmail($person, $team, $role);

		if ($status == ROSTER_INVITED) {
			$is_player = ($this->_arg('code') !== null || $person['Person']['id'] == $this->Auth->user('id'));
			$is_captain = in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));

			if ($is_player || $this->effective_admin || $this->effective_coordinator) {
				// A player or admin has declined an invitation
				$captains = $this->_initRosterCaptains ($team);

				if (!$this->_sendMail (array (
						'to' => $captains,
						'replyTo' => $person,
						'subject' => "{$person['Person']['full_name']} declined your invitation to join {$team['Team']['name']}",
						'template' => 'roster_decline_invite',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
					return false;
				}
			}
			if ($is_captain || $this->effective_admin || $this->effective_coordinator) {
				// A captain or admin has removed an invitation
				$this->set (array(
					'captain' => $this->Session->read('Zuluru.Person.full_name'),
				));

				if (!$this->_sendMail (array (
						'to' => $person,
						'replyTo' => $this->Session->read('Zuluru.Person'),
						'subject' => "Invitation to join {$team['Team']['name']} was removed",
						'template' => 'roster_remove_invite',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
					return false;
				}
			}
		} else {
			// A captain has declined a request
			$this->set (array(
				'captain' => $this->Session->read('Zuluru.Person.full_name'),
			));
			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Session->read('Zuluru.Person'),
					'subject' => "Request to join {$team['Team']['name']} was declined",
					'template' => 'roster_decline_request',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
				return false;
			}
		}
		return true;
	}

	function _sendChange ($person, $team, $role) {
		if ($role == $person['Person']['TeamsPerson']['role']) {
			return true;
		}
		$this->_initRosterEmail($person, $team, $role);

		$this->set (array(
			'reply' => $this->Session->read('Zuluru.Person.email'),
			'old_role' => $person['Person']['TeamsPerson']['role'],
		));

		if ($person['Person']['id'] == $this->Auth->user('id')) {
			// A player has changed themselves
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "{$person['Person']['full_name']} role change on {$team['Team']['name']} roster",
					'template' => 'roster_change_by_player',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
				return false;
			}
		} else {
			$this->set (array(
				'captain' => $this->Session->read('Zuluru.Person.full_name'),
			));

			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Session->read('Zuluru.Person'),
					'subject' => "Change of roster role on {$team['Team']['name']}",
					'template' => 'roster_change_by_captain',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
				return false;
			}
		}

		return true;
	}

	function _sendRemove ($person, $team) {
		$this->_initRosterEmail($person, $team);

		$this->set (array(
			'reply' => $this->Session->read('Zuluru.Person.email'),
			'old_role' => $person['Person']['TeamsPerson']['role'],
		));

		if ($person['Person']['id'] == $this->Auth->user('id')) {
			// A player has removed themselves
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "{$person['Person']['full_name']} removed from {$team['Team']['name']} roster",
					'template' => 'roster_remove_by_player',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), __('team captains.', true)), 'default', array('class' => 'error'), 'email');
				return false;
			}
		} else {
			$this->set (array(
				'captain' => $this->Session->read('Zuluru.Person.full_name'),
			));

			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Session->read('Zuluru.Person'),
					'subject' => "Removal from {$team['Team']['name']} roster",
					'template' => 'roster_remove_by_captain',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']), 'default', array('class' => 'error'), 'email');
				return false;
			}
		}

		return true;
	}

	function _initRosterEmail ($person, $team, $role = null) {
		$this->set (array(
			'person' => $person['Person'],
			'team' => $team['Team'],
			'division' => $team['Division'],
			'league' => $team['Division']['League'],
			'role' => $role,
		));
		Configure::load("sport/{$team['Division']['League']['sport']}");
	}

	function _initRosterCaptains ($team) {
		// Find the list of captains and assistants for the team
		$this->Team->contain(array(
			'Person' => array(
				'conditions' => array(
					'TeamsPerson.role' => Configure::read('privileged_roster_roles'),
					'TeamsPerson.status' => ROSTER_APPROVED,
				),
				'fields' => array('id', 'first_name', 'last_name', 'email'),
			),
		));
		$captains = $this->Team->read (null, $team['Team']['id']);
		$this->set ('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));

		return $captains;
	}

	function past_count() {
		return $this->Team->TeamsPerson->find('count', array(
				'conditions' => array('person_id' => $this->Auth->user('id')),
				'contain' => array(),
		)) - count($this->Session->read('Zuluru.TeamIDs'));
	}

	function open_count() {
		return $this->Team->find('count', array(
			'conditions' => array(
				'League.affiliate_id' => $this->_applicableAffiliateIDs(),
				'Team.open_roster' => true,
				'OR' => array(
					'Division.is_open',
					'Division.open > CURDATE()',
				),
			),
			'contain' => array(),
			'joins' => array(
				array(
					'table' => "{$this->Team->tablePrefix}divisions",
					'alias' => 'Division',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'Team.division_id = Division.id',
				),
				array(
					'table' => "{$this->Team->tablePrefix}leagues",
					'alias' => 'League',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'Division.league_id = League.id',
				),
			),
		));
	}

	function cron() {
		$this->layout = 'bare';

		if (!$this->Lock->lock ('cron')) {
			return false;
		}

		if (Configure::read('feature.generate_roster_email')) {
			$this->Roster = ClassRegistry::init ('TeamsPerson');

			$people = $this->Roster->find ('all', array(
					'conditions' => array(
						'TeamsPerson.status' => array(ROSTER_INVITED, ROSTER_REQUESTED),
						'TeamsPerson.created < DATE_ADD(CURDATE(), INTERVAL -7 DAY)',
					),
					'contain' => array(
						'Team' => array(
							'Division' => array(
								'Day',
								'League' => array(
									'fields' => array(
										'League.id', 'League.name', 'League.sport',
									),
								),
								'fields' => array(
									'Division.id', 'Division.name', 'Division.open', 'Division.ratio', 'Division.roster_deadline',
								),
							),
							'Person' => array(
								'conditions' => array('TeamsPerson.role' => Configure::read('privileged_roster_roles')),
								'fields' => array('Person.id', 'Person.first_name', 'Person.last_name', 'Person.email'),
								'order' => 'TeamsPerson.id',
							),
							'fields' => array('Team.id', 'Team.name'),
						),
						'Person' => array(
							'fields' => array('Person.id', 'Person.first_name', 'Person.last_name', 'Person.email'),
						),
					),
			));

			$log = ClassRegistry::init ('ActivityLog');
			$emailed = $reminded = $expired = $outstanding = 0;
			$activity = array();

			// Second reminder for people that have had reminders sent more than 5.5 days ago
			$second = 5.5 * DAY;
			// Expire invites that have had reminders sent more than 7.5 days ago
			$expire = 7.5 * DAY;

			foreach ($people as $person) {
				$conditions = array(
					'type' => ($person['TeamsPerson']['status'] == ROSTER_INVITED ? 'roster_invite_reminder' : 'roster_request_reminder'),
					'team_id' => $person['Team']['id'],
					'person_id' => $person['Person']['id'],
				);
				$sent = $log->find('all', array('conditions' => $conditions, 'order' => 'ActivityLog.created'));
				if (!empty ($sent)) {
					$age = time() - strtotime ($sent[0]['ActivityLog']['created']);
					if ($age > $expire) {
						$success = $this->_rosterExpire($person['Person'], $person['Team']['Person'], $person['Team'], $person['Team']['Division'], $person['TeamsPerson']);
						if ($success) {
							$activity[] = $conditions;
							++$expired;
						}
					} else if ($age > $second && count($sent) < 2) {
						$success = $this->_rosterRemind($person['Person'], $person['Team']['Person'], $person['Team'], $person['Team']['Division'], $person['TeamsPerson'], true);
						if ($success) {
							$activity[] = $conditions;
							++$reminded;
						}
					} else {
						++$outstanding;
					}
				} else {
					$success = $this->_rosterRemind($person['Person'], $person['Team']['Person'], $person['Team'], $person['Team']['Division'], $person['TeamsPerson']);
					if ($success) {
						$activity[] = $conditions;
						++$emailed;
					}
				}
			}

			$this->set(compact('emailed', 'reminded', 'expired', 'outstanding'));
			// Update the activity log
			if (!empty ($activity)) {
				$log->saveAll ($activity);
			}
		}

		$this->Lock->unlock();
	}

	function _rosterRemind($person, $captains, $team, $division, $roster, $second = false) {
		$code = $this->_hash($roster);
		if (!empty($division)) {
			$league = $division['League'];
			// TODO: Does this work when we have multiple sports?
			Configure::load("sport/{$league['sport']}");
		}
		$this->set(compact('person', 'team', 'division', 'league', 'roster', 'code'));
		$this->set ('captains', implode (', ', Set::extract ('/first_name', $captains)));
		$this->set ('days', ($second ? 2 : 7));

		if ($roster['status'] == ROSTER_INVITED) {
			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $captains[0],
					'subject' => "Reminder of invitation to join {$team['name']}",
					'template' => 'roster_invite_reminder',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
			)))
			{
				return false;
			}

			// If this is the second reminder, we also tell the captain(s)
			if ($second) {
				if (!$this->_sendMail (array (
						'to' => $captains,
						'replyTo' => $person,
						'subject' => "{$person['full_name']} has not answered invitation to join {$team['name']}",
						'template' => 'roster_invite_captain_reminder',
						'sendAs' => 'both',
						'ignore_empty_address' => true,
				)))
				{
					return false;
				}
			}
		} else {
			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "Reminder of {$person['full_name']} request to join {$team['name']}",
					'template' => 'roster_request_reminder',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
			)))
			{
				return false;
			}

			// If this is the second reminder, we also tell the player
			if ($second) {
				if (!$this->_sendMail (array (
						'to' => $person,
						'replyTo' => $captains[0],
						'subject' => "Unanswered request to join {$team['name']}",
						'template' => 'roster_request_player_reminder',
						'sendAs' => 'both',
						'ignore_empty_address' => true,
				)))
				{
					return false;
				}
			}
		}

		return true;
	}

	function _rosterExpire($person, $captains, $team, $division, $roster) {
		// Delete the invite/request
		if (!$this->Roster->delete($roster['id'], false)) {
			return false;
		}

		$this->set(compact('person', 'team', 'division', 'roster'));
		$this->set ('captains', implode (', ', Set::extract ('/first_name', $captains)));

		if ($roster['status'] == ROSTER_INVITED) {
			if (!$this->_sendMail (array (
					'to' => $captains,
					'cc' => $person,
					'replyTo' => $person,
					'subject' => "{$person['full_name']} invitation to join {$team['name']} expired",
					'template' => 'roster_invite_expire',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
			)))
			{
				return false;
			}
		} else {
			if (!$this->_sendMail (array (
					'to' => $person,
					'cc' => $captains,
					'replyTo' => $captains[0],
					'subject' => "{$person['full_name']} request to join {$team['name']} expired",
					'template' => 'roster_request_expire',
					'sendAs' => 'both',
					'ignore_empty_address' => true,
			)))
			{
				return false;
			}
		}

		return true;
	}
}
?>
