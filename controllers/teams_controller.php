<?php
class TeamsController extends AppController {

	var $name = 'Teams';
	var $helpers = array('ZuluruGame', 'Ajax');

	function isAuthorized() {
		// People can perform these operations on teams they run
		if (in_array ($this->params['action'], array(
				'edit',
				'delete',
				'add_player',
				'add_from',
				'roster_status',
				'emails',
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
				'roster_status',
		)))
		{
			// If a player id is specified, check if it's the logged-in user
			// If no player id is specified, it's always the logged-in user
			$person = $this->_arg('person');
			if (!$person || $person == $this->Auth->user('id')) {
				return true;
			}
		}

		return false;
	}

	function index() {
		$this->Team->recursive = 0;
		$this->set('teams', $this->paginate(array('League.is_open' => true)));
		$this->set('letters', $this->Team->find('all', array(
				'contain' => 'League',
				'fields' => array('DISTINCT SUBSTR(Team.name, 1, 1) AS letter'),
				'order' => 'letter',
				'conditions' => array('League.is_open' => true),
				// Grouping necessary because Cake adds Team.id to the query, so we get
				// "DISTINCT letter, id", which is more results than just "DISTINCT letter"
				'group' => 'letter',
		)));
	}

	function letter() {
		$letter = up($this->_arg('letter'));
		if (!$letter) {
			$this->Session->setFlash(__('Invalid letter', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact('letter'));
		$this->set('teams', $this->Team->find('all', array(
				'contain' => 'League',
				'conditions' => array(
					'League.is_open' => true,
					'Team.name LIKE' => "$letter%",
				),
				'order' => array('Team.name', 'League.open'),
		)));
		$this->set('letters', $this->Team->find('all', array(
				'contain' => 'League',
				'fields' => array('DISTINCT SUBSTR(Team.name, 1, 1) AS letter'),
				'order' => 'letter',
				'conditions' => array('League.is_open' => true),
				// Grouping necessary because Cake adds Team.id to the query, so we get
				// "DISTINCT letter, id", which is more results than just "DISTINCT letter"
				'group' => 'letter',
		)));
	}

	function unassigned() {
		$this->Team->recursive = -1;
		$this->set('teams', $this->paginate(array('Team.league_id' => null)));
	}

	function statistics() {
		$year = $this->_arg('year');
		if ($year === null) {
			$conditions = array('League.is_open' => true);
		} else {
			$conditions = array('YEAR(League.open)' => $year);
			$leagues = $this->Team->League->findSortByDay('all', array(
					'conditions' => array(
						'YEAR(League.open)' => $year,
						'League.schedule_type !=' => 'none',
					),
			));
			$leagues = Set::combine($leagues, '{n}.League.id', '{n}.League.long_name');
		}

		// League conditions take precedence over year conditions
		$league = $this->_arg('league');
		if ($league !== null) {
			$conditions = array('League.id' => $league);
		}

		// Get the list of open leagues and how many teams each has
		$this->Team->contain (array(
			'League' => array('Day'),
		));
		$counts = $this->Team->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'COUNT(Team.league_id) AS count',
				),
				'conditions' => array_merge ($conditions, array(
					'League.schedule_type !=' => 'none',
				)),
				'group' => 'Team.league_id',
				'order' => 'League.open',
		));

		// Get the list of teams that are short on players
		$this->Team->contain (array(
				'League',
				'TeamsPerson',
		));
		$shorts = $this->Team->find('all', array(
				'fields' => array(
					'Team.id', 'Team.name',
					'League.id', 'League.name', 'League.open',
					'COUNT(TeamsPerson.person_id) AS size',
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
				'conditions' => array_merge ($conditions, array(
					'League.schedule_type !=' => 'none',
					'TeamsPerson.status' => Configure::read('playing_roster_positions'),
				)),
				'group' => 'Team.id HAVING size < 12',
				'order' => array('size DESC', 'Team.name'),
		));
		foreach ($shorts as $key => $short) {
			$shorts[$key][0]['subs'] = $this->Team->TeamsPerson->find('count', array(
					'conditions' => array(
						'TeamsPerson.team_id' => $short['Team']['id'],
						'TeamsPerson.status' => 'substitute',
					),
			));
		}

		// Get the list of top-rated teams
		$this->Team->contain (array(
			'League' => array('Day'),
		));
		$top_rating = $this->Team->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'Team.id', 'Team.name', 'Team.rating',
				),
				'conditions' => array_merge ($conditions, array(
					'League.schedule_type !=' => 'none',
				)),
				'order' => 'Team.rating DESC',
				'limit' => 10,
		));

		// Get the list of lowest-rated teams
		$this->Team->contain (array(
			'League' => array('Day'),
		));
		$lowest_rating = $this->Team->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'Team.id', 'Team.name', 'Team.rating',
				),
				'conditions' => array_merge ($conditions, array(
					'League.schedule_type !=' => 'none',
				)),
				'order' => 'Team.rating ASC',
				'limit' => 10,
		));

		// Get the list of defaulting teams
		$this->Team->League->Game->contain (array(
			'League' => array('Day'),
		));
		$defaulting = $this->Team->League->Game->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'IF(Game.status = "home_default",HomeTeam.id,AwayTeam.id) AS team_id',
					'IF(Game.status = "home_default",HomeTeam.name,AwayTeam.name) AS team_name',
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
				'conditions' => array_merge ($conditions, array(
					'Game.status' => array('home_default', 'away_default'),
				)),
				'group' => 'team_id',
				'order' => 'count DESC',
		));

		// Get the list of non-score-submitting teams
		$this->Team->League->Game->contain (array(
			'League' => array('Day'),
		));
		$no_scores = $this->Team->League->Game->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'IF(Game.approved_by = -3,HomeTeam.id,AwayTeam.id) AS team_id',
					'IF(Game.approved_by = -3,HomeTeam.name,AwayTeam.name) AS team_name',
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
				'conditions' => array_merge ($conditions, array(
					'Game.approved_by' => array(-2,-3),
				)),
				'group' => 'team_id',
				'order' => 'count DESC',
		));

		// Get the list of top spirited teams
		$this->Team->contain (array(
			'League' => array('Day'),
		));
		$top_spirit = $this->Team->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'Team.id', 'Team.name',
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
				'conditions' => $conditions,
				'group' => 'Team.id HAVING avgspirit IS NOT NULL',
				'order' => array('avgspirit DESC', 'Team.name'),
				'limit' => 10,
		));

		// Get the list of lowest spirited teams
		$this->Team->contain (array(
			'League' => array('Day'),
		));
		$lowest_spirit = $this->Team->find('all', array(
				'fields' => array(
					'League.id', 'League.name', 'League.open',
					'Team.id', 'Team.name',
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
				'conditions' => $conditions,
				'group' => 'Team.id HAVING avgspirit IS NOT NULL',
				'order' => array('avgspirit ASC', 'Team.name'),
				'limit' => 10,
		));

		$this->Team->League->recursive = -1;
		$years = $this->Team->League->find('all', array(
			'fields' => 'DISTINCT YEAR(open) AS year',
			'conditions' => array('YEAR(open) !=' => 0),
			'order' => 'open',
		));

		$this->set(compact('counts', 'shorts', 'top_rating', 'lowest_rating',
				'defaulting', 'no_scores', 'top_spirit', 'lowest_spirit',
				'year', 'years', 'leagues'));
	}

	function view() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Team->contain (array(
				'Person',
				'League',
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		usort ($team['Person'], array('Team', 'compareRoster'));
		$this->set('team', $team);
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('is_coordinator', in_array($this->Team->data['Team']['league_id'], $this->Session->read('Zuluru.LeagueIDs')));
		$this->_addTeamMenuItems ($this->Team->data);
	}

	function add() {
		if (!$this->is_admin && Configure::read('feature.registration')) {
			$this->Session->setFlash (__('This system creates teams through the registration process. Team creation through Zuluru is disabled. If you need a team created for some other reason (e.g. a touring team), please email ' . Configure::read('email.admin_email') . ' with the details, or call the office.', true));
			$this->redirect('/');
		}

		if (!empty($this->data)) {
			$this->Team->create();
			if ($this->Team->save($this->data)) {
				$this->Session->setFlash(__('The team has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The team could not be saved. Please correct the errors below and try again.', true));
			}
		}

		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('team');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Team->save($this->data)) {
				$this->Session->setFlash(__('The team has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The team could not be saved. Please correct the errors below and try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Team->read(null, $id);
		}
	}

	function delete() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect(array('action'=>'index'));
		}

		// TODO Handle deletions
		$this->Session->setFlash(__('Deletions are not currently supported', true));
		$this->redirect('/');

		if ($this->Team->delete($id)) {
			$this->Session->setFlash(__('Team deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Team was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}

	// TODO: Method for moving multiple teams at once; jQuery "left and right" boxes?
	function move() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain (array ('League' => array('Day')));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		if (!empty($this->data)) {
			$this->Team->League->contain('Day');
			$league = $this->Team->League->read(null, $this->data['Team']['to']);
			// Don't do league comparisons when the team being moved is not in a league
			if ($team['League']['id']) {
				if ($league['Day'][0]['id'] != $team['League']['Day'][0]['id']) {
					$this->Session->setFlash(__('Cannot move a team to a different day', true));
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
				if ($league['League']['ratio'] != $team['League']['ratio']) {
					$this->Session->setFlash(__('Destination league must have the same gender ratio', true));
					$this->redirect(array('action' => 'view', 'team' => $id));
				}
			}
			if ($this->Team->saveField ('league_id', $this->data['Team']['to'])) {
				$this->Session->setFlash(sprintf (__('Team has been moved to %s', true), $league['League']['long_name']));
			} else {
				$this->Session->setFlash(__('Failed to move the team!', true));
			}
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		if ($team['League']['id']) {
			// TODO: How to handle leagues on multiple days?
			$this->Team->League->Day->contain (array(
					'League' => array(
						'conditions' => array(
							'League.id !=' => $team['League']['id'],
							'League.is_open' => true,
							'League.ratio' => $team['League']['ratio'],
						),
					),
			));
			$leagues = $this->Team->League->Day->read (null, $team['League']['Day'][0]['id']);
			$leagues = $leagues['League'];
		} else {
			$this->Team->League->recursive = -1;
			$leagues = $this->Team->League->find ('all', array(
				'conditions' => array('OR' => array(
					'League.is_open' => true,
					'League.open > CURDATE()',
				)),
			));
			$leagues = Set::extract ('/League/.', $leagues);
		}

		// Make sure there's somewhere to move it to
		if (empty ($leagues)) {
			$this->Session->setFlash(__('No similar league found to move this team to!', true));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$this->set(compact('team', 'leagues'));
	}

	function schedule() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain (array ('League'));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Team->League->Game->contain (array(
				'GameSlot' => array('Field' => array('ParentField')),
				'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $this->Session->read('Zuluru.TeamIDs'))),
				'SpiritEntry',
				'HomeTeam',
				'AwayTeam',
		));
		$team['Game'] = $this->Team->League->Game->find('all', array(
				'conditions' => array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
				)),
		));
		if (empty ($team['Game'])) {
			$this->Session->setFlash(__('This team has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}

		// Sort games by date, time and field
		usort ($team['Game'], array ('League', 'compareDateAndField'));

		$this->set(compact('team'));
		$this->set('is_coordinator', in_array($team['Team']['league_id'], $this->Session->read('Zuluru.LeagueIDs')));
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $team['League']['sotg_questions'], $this));
		$this->_addTeamMenuItems ($this->Team->data);
	}

	// This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	function ical($id) {
		$this->layout = 'ical';
		if (!$id) {
			return;
		}

		$this->Team->contain (array ('League'));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			return;
		}
		$this->Team->League->Game->contain (array(
				'GameSlot' => array('Field' => array('ParentField')),
				'HomeTeam',
				'AwayTeam',
		));
		$team['Game'] = $this->Team->League->Game->find('all', array(
				'conditions' => array(
					'Game.published' => true,
					'OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
					),
				),
		));

		// Sort games by date, time and field
		usort ($team['Game'], array ('League', 'compareDateAndField'));
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
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain (array ('League'));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->Team->League->Game->contain (array(
				'GameSlot',
				'HomeTeam',
				'AwayTeam',
				'SpiritEntry',
		));
		$team['Game'] = $this->Team->League->Game->find('all', array(
				'conditions' => array('OR' => array(
						'Game.home_team' => $id,
						'Game.away_team' => $id,
				)),
		));
		if (empty ($team['Game'])) {
			$this->Session->setFlash(__('This team has no games scheduled yet.', true));
			$this->redirect(array('action' => 'index'));
		}

		// Sort games by date, time and field
		usort ($team['Game'], array ('League', 'compareDateAndField'));

		$this->set(compact('team'));
		$this->set('spirit_obj', $this->_getComponent ('Spirit', $team['League']['sotg_questions'], $this));
		$this->_addTeamMenuItems ($this->Team->data);
	}

	function emails() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain (array (
			'Person' => array(
				'fields' => array(
					'Person.first_name', 'Person.last_name', 'Person.email',
				),
				'order' => array(
					'TeamsPerson.status', 'Person.gender DESC', 'Person.last_name', 'Person.first_name',
				),
				'conditions' => array(
					'Person.id !=' => $this->Auth->User('id'),
					'TeamsPerson.status !=' => 'captain_request',
				),
			),
		));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact('team'));
	}

	function add_player() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		$this->Team->contain('League');
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		// To avoid abuses, whether intentional or accidental, we limit the permissions
		// of admins when managing teams they are on.
		$this->effective_admin = false;
		if ($this->is_admin) {
			$on_team = in_array ($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs'));
			if (!$on_team) {
				$this->effective_admin = true;
			}
		}

		if (!$this->effective_admin && $team['League']['roster_deadline'] < date('Y-m-d')) {
			$this->Session->setFlash(__('The roster deadline for this league has already passed.', true));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		$this->set(compact('team'));

		$params = $url = $this->_extractSearchParams();
		unset ($params['team']);
		if (!empty($params)) {
			$test = trim (@$params['first_name'], ' *') . trim (@$params['last_name'], ' *');
			if (strlen ($test) < 2) {
				$this->set('short', true);
			} else {
				// This pagination needs the model at the top level
				$this->Person = $this->Team->Person;
				$this->Person->recursive = 0;
				$this->_mergePaginationParams();
				$this->set('people', $this->paginate('Person', $this->_generateSearchConditions($params, 'Person')));
			}
		}
		$this->set(compact('url'));

		$this->Team->Person->contain (array (
			'Team' => array(
				'League',
				'order' => 'Team.id desc',
			),
		));
		$teams = $this->Team->Person->read(null, $this->Auth->User('id'));
		// Only show teams from leagues that are closed and have some schedule type
		// TODO: May need to change this once we can schedule playoffs
		$teams = Set::extract("/League[id!={$team['Team']['league_id']}][schedule_type!=none]/..", $teams['Team']);
		$this->set(compact('teams'));
	}

	function add_from() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		if (empty ($this->data)) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		// Read the current team roster, just need the ids
		$this->Team->contain (array (
			'Person' => array(
				'fields' => array(
					'Person.id',
				),
			),
			// We need league information for sending out invites, may as well read it now
			'League' => array(
				'Day',
			),
		));
		$team = $this->Team->read(null, $id);
		if ($team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		// Only include people that aren't yet on the new roster
		$current = Set::extract('/Person/id', $team);
		if (count ($current) == 1) {
			$conditions = array('Person.id !=' => array_shift ($current));
		} else {
			$conditions = array('Person.id NOT' => $current);
		}
		// Read the old team roster
		$this->Team->contain (array (
			'League',
			'Person' => array(
				'fields' => array(
					'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.status',
				),
				'order' => array(
					'Person.gender DESC', 'Person.last_name', 'Person.first_name',
				),
				'conditions' => $conditions,
			),
		));
		$old_team = $this->Team->read(null, $this->data['team']);
		if ($old_team === false) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		// If this is a form submission, set the status to 'captain_request' for each player
		if (array_key_exists ('player', $this->data)) {
			// We need this model for updating status.
			$this->Roster = ClassRegistry::init ('TeamsPerson');

			$success = $failure = array();
			foreach ($this->data['player'] as $player => $bool) {
				$person = array_shift (Set::extract("/Person[id=$player]", $old_team));
				unset ($person['Person']['TeamsPerson']);
				if ($this->_setRosterStatus ('captain_request', $person, $team)) {
					$success[] = $person['Person']['full_name'];
				} else {
					$failure[] = $person['Person']['full_name'];
				}
			}
			$msg = array();
			if (!empty ($success)) {
				$msg[] = __((count($success) > 1 ? 'Invitations have' : 'Invitation has') . ' been sent to ', true) . implode (', ', $success) . '.';
			}
			if (!empty ($failure)) {
				$msg[] .= __('Failed to send invitation' . (count($success) > 1 ? 's' : '') . ' to ', true) . implode (', ', $failure) . '.';
			}
			$this->Session->setFlash(implode (' ', $msg));
			$this->redirect(array('action' => 'view', 'team' => $id));
		}

		foreach ($old_team['Person'] as $key => $person) {
			$old_team['Person'][$key]['can_add'] = $this->_canAdd (array('Person' => $person), $team);
		}

		$this->set(compact('team', 'old_team'));
	}

	function roster_status() {
		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(__('Invalid id for player', true));
				$this->redirect('/');
			}
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect('/');
		}

		// Read the team record, along with the specified player's current status, if any
		$this->Team->contain (array(
			'Person' => array(
				'conditions' => array('Person.id' => $person_id),
			),
			// We need league information for sending out invites, may as well read it now
			'League' => array(
				'Day',
			),
		));
		$team = $this->Team->read(null, $team_id);

		// To avoid abuses, whether intentional or accidental, we limit the permissions
		// of admins when managing teams they are on.
		$this->effective_admin = false;
		if ($this->is_admin) {
			$on_team = in_array ($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs'));
			if (!$on_team) {
				$this->effective_admin = true;
			}
		}

		if (!$this->effective_admin && $team['League']['roster_deadline'] < date('Y-m-d')) {
			$this->Session->setFlash(__('The roster deadline for this league has already passed.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		if (empty ($team['Person'])) {
			// Read the bare player record
			$this->Team->Person->recursive = -1;
			$person = $this->Team->Person->read(null, $person_id);

			$status = 'none';
		} else {
			// Pull out the player record from the team, and make
			// it look as if we just read it
			$person = array('Person' => array_shift ($team['Person']));
			$status = $person['Person']['TeamsPerson']['status'];
		}

		// We need this model for checking how many captains, and for updating status.
		$this->Roster = ClassRegistry::init ('TeamsPerson');

		// Check if this user is the only captain on the team
		if ($status == 'captain') {
			$captains = $this->Roster->find ('count', array('conditions' => array(
					'status' => 'captain',
					'team_id' => $team_id,
			)));
			if ($captains == 1) {
				$this->Session->setFlash(__('All teams must have at least one player with captain status.', true));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		$roster_options = $this->_rosterOptions ($status, $team_id, $team['Team']['open_roster']);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['status'], $roster_options)) {
				$this->Session->setFlash(__('You do not have permission to set that status.', true));
			} else {
				if ($this->_setRosterStatus ($this->data['Person']['status'], $person, $team)) {
					if ($person['Person']['id'] == $my_id) {
						$this->_deleteTeamSessionData();
					}
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'status', 'roster_options'));
	}

	function _rosterOptions ($status, $team, $open) {
		$roster_options = $full_roster_options = Configure::read('options.roster_position');

		// Can never set anyone to their current status
		unset ($roster_options[$status]);

		// Admins can move anyone to anything
		if ($this->effective_admin) {
			return $roster_options;
		}

		// Captain request and player request are special cases, not in the main list
		// TODO: Handle these with a separate column in the database?
		unset ($roster_options['captain_request']);
		unset ($roster_options['player_request']);

		// Captains are limited when it comes to players that aren't on the roster
		if (in_array($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
			switch ($status) {
				case 'captain_request':
					return array('none' => $roster_options['none']);

				case 'none':
					return array('captain_request' => $full_roster_options['captain_request']);

				default:
					// Otherwise, they can make anyone into anything
					return $roster_options;
			}
		}

		// Non-captains are not allowed to promote themselves to captainly roles
		unset ($roster_options['captain']);
		unset ($roster_options['coach']);
		unset ($roster_options['assistant']);

		switch ($status) {
			case 'substitute':
				// Subs can't make themselves regular players
				unset ($roster_options['player']);
				break;

			case 'player_request':
				// Players that requested to join can only remove themselves
				return array('none' => $roster_options['none']);

			case 'none':
				if ($open) {
					return array('player_request' => $full_roster_options['player_request']);
				} else {
					$this->Session->setFlash(__('Sorry, this team is not open for new players to join.', true));
					$this->redirect(array('action' => 'view', 'team' => $team));
				}
		}

		// Whatever is left is okay
		return $roster_options;
	}

	function _setRosterStatus ($status, $person, $team) {
		// We can always remove people from rosters
		if ($status == 'none') {
			if ($this->Roster->delete ($person['Person']['TeamsPerson']['id'])) {
				$this->Session->setFlash(__('Removed the player from the team.', true));
				return true;
			} else {
				$this->Session->setFlash(__('Failed to remove the player from the team.', true));
				return false;
			}
		}

		$result = $this->_canAdd ($person, $team);
		if ($result !== true)
		{
			$this->Session->setFlash($result);
			return false;
		}

		if (empty ($person['Person']['TeamsPerson'])) {
			$this->Roster->create();
		} else {
			$this->Roster->id = $person['Person']['TeamsPerson']['id'];
		}

		$success = $this->Roster->save (array(
				'team_id' => $team['Team']['id'],
				'person_id' => $person['Person']['id'],
				'status' => $status,
		));

		// If we were successful in the update, there may be emails to send
		if ($success) {
			return $this->_sendInvitation($status, $person, $team);
		} else {
			$this->Session->setFlash(__('Failed to set player to that state.', true));
			return false;
		}
	}

	function _canAdd ($person, $team) {
		if ($person['Person']['status'] != 'active') {
			return __('New players must be approved by an administrator before they can be added to a team; this normally happens within one business day.', true);
		}
		if (array_key_exists ('complete', $person['Person']) && !$person['Person']['complete']) {
			return __('This player has not yet completed their profile.  Please contact this player directly to have them complete their profile.', true);
		}

		// Maybe use the rules engine to decide if this person can be added to this roster
		if (array_key_exists ('roster_rule', $team['League']) && !empty ($team['League']['roster_rule'])) {
			$rule_obj = AppController::_getComponent ('Rule');
			if (!$rule_obj->init ($team['League']['roster_rule'])) {
				return __('Failed to parse the rule', true);
			}

			// Get everything from the user record that the rule might need
			$this->Team->Person->contain (array (
				'Registration' => array(
					'Event' => array(
						'EventType',
					),
				),
				'Team' => array(
					'League',
					'TeamsPerson',
					'conditions' => array('Team.id !=' => $team['Team']['id']),
				),
			));

			$full_person = $this->Team->Person->read(null, $person['Person']['id']);
			if (!$rule_obj->evaluate ($full_person)) {
				return __('Cannot add this player to this team', true) . ': ' . $rule_obj->reason;
			}
		}

		return true;
	}

	function _sendInvitation ($status, $person, $team) {
		if (!Configure::read('feature.generate_roster_email')) {
			return true;
		}

		$variables = array( 
			'%fullname' => $person['Person']['full_name'],
			'%userid' => $person['Person']['id'],
			'%teamurl' => Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), true),
			'%team' => $team['Team']['name'],
			'%league' => $team['League']['long_name'],
			'%day' => implode (' and ', Set::extract ('/Day/name', $team['League'])),
		);

		if ($status == 'captain_request') {
			$variables['%captain'] = $this->Session->read('Zuluru.Person.full_name');

			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Auth->user(),
					'config_subject' => 'captain_request_subject',
					'config_body' => 'captain_request_body',
					'variables' => $variables,
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
				return false;
			}
		}
		else if( $status == 'player_request') {
			// Find the list of captains and assistants for the team
			$this->Team->contain (array(
				'Person' => array(
					'conditions' => array('TeamsPerson.status' => Configure::read('privileged_roster_positions')),
					'fields' => array('first_name', 'last_name', 'email'),
				),
			));
			$captains = $this->Team->read (null, $team['Team']['id']);
			$variables['%captains'] = implode (', ', Set::extract ('/Person/full_name', $captains));
			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'config_subject' => 'player_request_subject',
					'config_body' => 'player_request_body',
					'variables' => $variables,
			)))
			{
				$this->Session->setFlash(__('Error sending email to team captains.', true));
				return false;
			}
		}

		return true;
	}
}
?>
