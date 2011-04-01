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
				'add_from_team',
				'roster_position',
				'roster_invite',
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
				'roster_position',
				'roster_request',
		)))
		{
			// If a player id is specified, check if it's the logged-in user
			// If no player id is specified, it's always the logged-in user
			$person = $this->_arg('person');
			if (!$person || $person == $this->Auth->user('id')) {
				return true;
			}
		}

		// People can perform these operations on leagues they coordinate
		if (in_array ($this->params['action'], array(
				'add_player',
				'add_from_event',
				'roster_add',
				'roster_position',
		)))
		{
			// If a team id is specified, check if we're a coordinator of that team's league
			$team = $this->_arg('team');
			if ($team) {
				$this->_limitOverride($team);
				return $this->effective_coordinator;
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
					'TeamsPerson.position' => Configure::read('playing_roster_positions'),
				)),
				'group' => 'Team.id HAVING size < 12',
				'order' => array('size DESC', 'Team.name'),
		));
		foreach ($shorts as $key => $short) {
			$shorts[$key][0]['subs'] = $this->Team->TeamsPerson->find('count', array(
					'conditions' => array(
						'TeamsPerson.team_id' => $short['Team']['id'],
						'TeamsPerson.position' => 'substitute',
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
				'Person' => array('Upload'),
				'League',
		));
		$team = $this->Team->read(null, $id);
		if (!$team) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		foreach ($team['Person'] as $key => $person) {
			if ($person['TeamsPerson']['status'] == ROSTER_APPROVED) {
				$team['Person'][$key]['can_add'] = true;
			} else {
				$team['Person'][$key]['can_add'] = $this->_canAdd (array('Person' => $person), $team);
			}
		}

		usort ($team['Person'], array('Team', 'compareRoster'));

		$this->set('team', $team);
		$this->set('is_captain', in_array($id, $this->Session->read('Zuluru.OwnedTeamIDs')));
		$this->set('is_coordinator', in_array($this->Team->data['Team']['league_id'], $this->Session->read('Zuluru.LeagueIDs')));
		$this->_addTeamMenuItems ($this->Team->data);

		// Set up a couple more variables that the player popup block needs
		$this->set('my_id', $this->Auth->user('id'));
		$captain_in_league_ids = Set::extract ('/Team/league_id', $this->Session->read('Zuluru.OwnedTeams'));
		$this->set('is_league_captain', in_array ($team['Team']['league_id'], $captain_in_league_ids));
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
					'TeamsPerson.position', 'Person.gender DESC', 'Person.last_name', 'Person.first_name',
				),
				'conditions' => array(
					'Person.id !=' => $this->Auth->User('id'),
					'TeamsPerson.status' => ROSTER_APPROVED,
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
		// of admins and coordinators when managing teams they are on.
		$this->_limitOverride($id);
		$this->set('is_coordinator', $this->effective_coordinator);

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
				$this->_mergePaginationParams();
				$this->paginate['Person'] = array(
					'conditions' => $this->_generateSearchConditions($params, 'Person'),
					'contain' => array('Upload'),
				);
				$this->set('people', $this->paginate('Person'));
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
		// Only show teams from leagues that have some schedule type
		// TODO: May need to change this once we can schedule playoffs
		$teams = Set::extract("/League[id!={$team['Team']['league_id']}][schedule_type!=none]/..", $teams['Team']);
		$this->set(compact('teams'));

		// Admins and coordinators get to add people based on registration events
		if ($this->effective_admin || $this->effective_coordinator) {
			$this->Team->Person->Registration->Event->recursive = -1;
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
					'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
					'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
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

		// If this is a form submission, set the position to 'player' for each player
		if (array_key_exists ('player', $this->data)) {
			// We need this model for updating position.
			$this->Roster = ClassRegistry::init ('TeamsPerson');

			$success = $failure = array();
			foreach ($this->data['player'] as $player => $bool) {
				$person = array_shift (Set::extract("/Person[id=$player]", $old_team));
				unset ($person['Person']['TeamsPerson']);
				if ($this->_setRosterPosition ($person, $team, 'player', ROSTER_INVITED)) {
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

	function add_from_event() {
		$id = $this->_arg('team');
		if (!$id) {
			$this->Session->setFlash(__('Invalid team', true));
			$this->redirect(array('action' => 'index'));
		}

		if (empty ($this->data)) {
			$this->Session->setFlash(__('Invalid event', true));
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
		// Read the list of registrations
		$this->Team->Person->Registration->Event->contain (array (
			'Registration' => array(
				'Person' => array(
					'fields' => array(
						'Person.id', 'Person.first_name', 'Person.last_name', 'Person.email', 'Person.status',
						'Person.home_phone', 'Person.work_phone', 'Person.work_ext', 'Person.mobile_phone',
						'Person.publish_email', 'Person.publish_home_phone', 'Person.publish_work_phone', 'Person.publish_mobile_phone',
					),
					'order' => array(
						'Person.gender DESC', 'Person.last_name', 'Person.first_name',
					),
					'conditions' => $conditions,
				),
				'conditions' => array('Payment' => 'Paid'),
			),
		));
		$event = $this->Team->Person->Registration->Event->read(null, $this->data['event']);
		if ($event === false) {
			$this->Session->setFlash(__('Invalid event', true));
			$this->redirect(array('action' => 'index'));
		}

		// If this is a form submission, set the position to 'player' for each player
		if (array_key_exists ('player', $this->data)) {
			// We need this model for updating position.
			$this->Roster = ClassRegistry::init ('TeamsPerson');

			$success = $failure = array();
			foreach ($this->data['player'] as $player => $bool) {
				$person = array_shift (Set::extract("/Registration/Person[id=$player]", $event));
				unset ($person['Person']['TeamsPerson']);
				// Only admins have this option, typically used for building hat teams,
				// so their adds are always approved
				if ($this->_setRosterPosition ($person, $team, 'player', ROSTER_APPROVED)) {
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

		foreach ($event['Registration'] as $key => $registration) {
			$event['Registration'][$key]['can_add'] = $this->_canAdd (array('Person' => $registration['Person']), $team);
		}

		$this->set(compact('team', 'event'));
	}

	function roster_position() {
		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(__('Invalid id for player', true));
				$this->redirect('/');
			}
		}

		$team = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (empty ($team['Person'])) {
			$this->Session->setFlash(__('This player is not on this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Pull out the player record from the team, and make
		// it look as if we just read it
		$person = array('Person' => array_shift ($team['Person']));
		$position = $person['Person']['TeamsPerson']['position'];
		if ($person['Person']['TeamsPerson']['status'] != ROSTER_APPROVED) {
			$this->Session->setFlash(__('A player\'s position on a team cannot be changed until they are been approved on the roster.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Check if this user is the only approved captain on the team
		if ($position == 'captain') {
			$captains = $this->Roster->find ('count', array('conditions' => array(
					'position' => 'captain',
					'status' => ROSTER_APPROVED,
					'team_id' => $team_id,
			)));
			if ($captains == 1) {
				$this->Session->setFlash(__('All teams must have at least one player as captain.', true));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		$roster_options = $this->_rosterOptions ($position, $team['Team']);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['position'], $roster_options)) {
				$this->Session->setFlash(__('You do not have permission to set that position.', true));
			} else {
				if ($this->_setRosterPosition ($person, $team, $this->data['Person']['position'], ROSTER_APPROVED)) {
					if ($person_id == $my_id) {
						$this->_deleteTeamSessionData();
					}
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'position', 'roster_options'));
	}

	function roster_add() {
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(__('Invalid id for player', true));
			$this->redirect('/');
		}

		$team = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (!empty ($team['Person'])) {
			$this->Session->setFlash(__('This player is already on this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Read the bare player record
		$this->Team->Person->recursive = -1;
		$person = $this->Team->Person->read(null, $person_id);

		// Check if this person can even be added
		// If not, we still allow the invitation, but give the captain a warning
		$can_add = $this->_canAdd ($person, $team);
		if ($can_add !== true) {
			$this->Session->setFlash($can_add);
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		$roster_options = $this->_rosterOptions ('none', $team['Team']);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['position'], $roster_options)) {
				$this->Session->setFlash(__('You are not allowed to invite someone to that position.', true));
			} else {
				if ($this->_setRosterPosition ($person, $team, $this->data['Person']['position'], ROSTER_APPROVED)) {
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'roster_options'));
	}

	function roster_invite() {
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(__('Invalid id for player', true));
			$this->redirect('/');
		}

		$team = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (!empty ($team['Person'])) {
			$this->Session->setFlash(__('This player is already on this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Read the bare player record
		$this->Team->Person->recursive = -1;
		$person = $this->Team->Person->read(null, $person_id);

		// Check if this person can even be added
		// If not, we still allow the invitation, but give the captain a warning
		$can_add = $this->_canAdd ($person, $team);

		$roster_options = $this->_rosterOptions ('none', $team['Team']);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['position'], $roster_options)) {
				$this->Session->setFlash(__('You are not allowed to invite someone to that position.', true));
			} else {
				if ($this->_setRosterPosition ($person, $team, $this->data['Person']['position'], ROSTER_INVITED)) {
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'roster_options', 'can_add'));
	}

	function roster_request() {
		$my_id = $this->Auth->user('id');

		$team = $this->_initTeamForRosterChange($my_id);
		$team_id = $team['Team']['id'];

		if (!empty ($team['Person'])) {
			$this->Session->setFlash(__('You are already on this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Read the bare player record
		$this->Team->Person->recursive = -1;
		$person = $this->Team->Person->read(null, $my_id);

		// Check if this person can even be added
		$can_add = $this->_canAdd ($person, $team);
		if ($can_add !== true) {
			$this->Session->setFlash($can_add);
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We're not already on this team, so the "effective" calculations won't
		// have blocked us, but we still don't want to give overrides for joining.
		$this->effective_admin = $this->effective_coordinator = false;
		$roster_options = $this->_rosterOptions ('none', $team['Team']);

		if (!empty($this->data)) {
			if (!array_key_exists ($this->data['Person']['position'], $roster_options)) {
				$this->Session->setFlash(__('You are not allowed to request that position.', true));
			} else {
				if ($this->_setRosterPosition ($person, $team, $this->data['Person']['position'], ROSTER_REQUESTED)) {
					$this->_deleteTeamSessionData();
					$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
				}
			}
		}

		$this->set(compact('person', 'team', 'roster_options'));
	}

	function roster_accept() {
		$my_id = $this->Auth->user('id');
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(__('Invalid id for player', true));
				$this->redirect('/');
			}
		}

		$team = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (empty ($team['Person'])) {
			$this->Session->setFlash(__('This player has neither been invited nor requested to join this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Pull out the player record from the team, and make
		// it look as if we just read it
		$person = array('Person' => array_shift ($team['Person']));
		if ($person['Person']['TeamsPerson']['status'] == ROSTER_APPROVED) {
			$this->Session->setFlash(__('This player has already been added to the roster.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($person['Person']['TeamsPerson']);
			if ($hash != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true));
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
						__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd ($person, $team);
		if ($can_add !== true) {
			$this->Session->setFlash($can_add);
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		$this->Roster->id = $person['Person']['TeamsPerson']['id'];
		if ($this->Roster->saveField ('status', ROSTER_APPROVED)) {
			$this->Session->setFlash(sprintf (__('You have accepted this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));

			// Send email to the affected people
			$this->_sendAccept($person, $team, $person['Person']['TeamsPerson']['position'], $person['Person']['TeamsPerson']['status']);

			if ($person_id == $my_id) {
				$this->_deleteTeamSessionData();
				$this->redirect('/');
			}
		} else {
			$this->Session->setFlash(sprintf (__('The database failed to save the acceptance of this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));
		}
		$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
	}

	function roster_decline() {
		$my_id = $this->Auth->user('id');
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(__('Invalid id for player', true));
				$this->redirect('/');
			}
		}

		$team = $this->_initTeamForRosterChange($person_id);
		$team_id = $team['Team']['id'];

		if (empty ($team['Person'])) {
			$this->Session->setFlash(__('This player has neither been invited nor requested to join this team.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// Pull out the player record from the team, and make
		// it look as if we just read it
		$person = array('Person' => array_shift ($team['Person']));
		if ($person['Person']['TeamsPerson']['status'] == ROSTER_APPROVED) {
			$this->Session->setFlash(__('This player has already been added to the roster.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code) {
			// Authenticate the hash code
			$hash = $this->_hash($person['Person']['TeamsPerson']);
			if ($hash != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true));
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
						__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));
				$this->redirect(array('action' => 'view', 'team' => $team_id));
			}
		}

		if ($this->Roster->delete ($person['Person']['TeamsPerson']['id'])) {
			$this->Session->setFlash(sprintf (__('You have declined this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));

			// Send email to the affected people
			$this->_sendDecline($person, $team, $person['Person']['TeamsPerson']['position'], $person['Person']['TeamsPerson']['status']);

			if ($person_id == $my_id) {
				$this->_deleteTeamSessionData();
				$this->redirect('/');
			}
		} else {
			$this->Session->setFlash(sprintf (__('The database failed to save the removal of this roster %s.', true),
					__(($person['Person']['TeamsPerson']['status'] == ROSTER_INVITED) ? 'invitation' : 'request', true)));
		}
		$this->redirect(array('action' => 'view', 'team' => $team['Team']['id']));
	}

	function _initTeamForRosterChange($person_id) {
		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(__('Invalid id for team', true));
			$this->redirect('/');
		}

		// Read the team record, along with the specified player's current position, if any
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
		$this->_limitOverride($team_id);

		if (!$this->effective_admin && $team['League']['roster_deadline'] < date('Y-m-d')) {
			$this->Session->setFlash(__('The roster deadline for this league has already passed.', true));
			$this->redirect(array('action' => 'view', 'team' => $team_id));
		}

		// We need this model for checking how many captains, and for updating position.
		$this->Roster = ClassRegistry::init ('TeamsPerson');

		return $team;
	}

	function _rosterOptions ($position, $team) {
		$roster_options = Configure::read('options.roster_position');

		// People that aren't on the team can't be "changed to" not on the team
		if ($position == 'none') {
			unset ($roster_options['none']);
		}

		// Admins, coordinators and captains can make anyone anything
		if ($this->effective_admin || $this->effective_coordinator ||
			in_array($team['id'], $this->Session->read('Zuluru.OwnedTeamIDs')))
		{
			return $roster_options;
		}

		// Non-captains are not allowed to promote themselves to captainly roles
		unset ($roster_options['captain']);
		unset ($roster_options['coach']);
		unset ($roster_options['assistant']);

		switch ($position) {
			case 'substitute':
				// Subs can't make themselves regular players
				unset ($roster_options['player']);
				break;

			case 'none':
				if (!$team['open_roster']) {
					$this->Session->setFlash(__('Sorry, this team is not open for new players to join.', true));
					$this->redirect(array('action' => 'view', 'team' => $team['id']));
				}
		}

		// Whatever is left is okay
		return $roster_options;
	}

	function _setRosterPosition ($person, $team, $position, $status) {
		// We can always remove people from rosters
		if ($position == 'none') {
			if ($this->Roster->delete ($person['Person']['TeamsPerson']['id'])) {
				$this->Session->setFlash(__('Removed the player from the team.', true));
				return $this->_sendRemove($person, $team);
			} else {
				$this->Session->setFlash(__('Failed to remove the player from the team.', true));
				return false;
			}
		}

		$result = $this->_canAdd ($person, $team);
		if ($result !== true && $status != ROSTER_INVITED) {
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
				'position' => $position,
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
					'position' => $position,
					'created' => date('Y-m-d'),
			)));

			if (empty ($person['Person']['TeamsPerson'])) {
				switch ($status) {
					case ROSTER_APPROVED:
						return $this->_sendAdd($person, $team, $position);

					case ROSTER_INVITED;
						return $this->_sendInvite($person, $team, $position);

					case ROSTER_REQUESTED:
						return $this->_sendRequest($person, $team, $position);
				}
			} else {
				return $this->_sendChange($person, $team, $position);
			}
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
					'conditions' => array('Registration.payment' => 'paid'),
				),
				'Team' => array(
					'League',
					'TeamsPerson',
					'conditions' => array('Team.id !=' => $team['Team']['id']),
				),
			));

			$full_person = $this->Team->Person->read(null, $person['Person']['id']);
			if (!$rule_obj->evaluate ($full_person)) {
				return __('To be added to this team, this player must first', true) . ' ' . $rule_obj->reason . '.';
			}
		}

		return true;
	}

	function _hash ($roster) {
		// Build a string of the inputs
		$input = "{$roster['id']}:{$roster['team_id']}:{$roster['person_id']}:{$roster['position']}:{$roster['created']}";
		return md5($input);
	}

	function _sendAdd ($person, $team, $position) {
		$this->_initRosterEmail($person, $team, $position);
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
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
			return false;
		}

		return true;
	}

	function _sendInvite ($person, $team, $position) {
		$this->_initRosterEmail($person, $team, $position);
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
			$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
			return false;
		}

		return true;
	}

	function _sendRequest ($person, $team, $position) {
		$this->_initRosterEmail($person, $team, $position);
		$captains = $this->_initRosterCaptains ($team);

		if (!$this->_sendMail (array (
				'to' => $captains,
				'replyTo' => $person,
				'subject' => "Request to join {$team['Team']['name']}",
				'template' => 'roster_request',
				'sendAs' => 'both',
		)))
		{
			$this->Session->setFlash(__('Error sending email to team captains.', true));
			return false;
		}

		return true;
	}

	function _sendAccept ($person, $team, $position, $status) {
		$this->_initRosterEmail($person, $team, $position);

		if ($status == ROSTER_INVITED) {
			// A player has accepted an invitation
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "Invitation to join {$team['Team']['name']} was accepted",
					'template' => 'roster_accept_invite',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(__('Error sending email to team captains.', true));
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
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
				return false;
			}
		}
		return true;
	}

	function _sendDecline ($person, $team, $position, $status) {
		$this->_initRosterEmail($person, $team, $position);

		if ($status == ROSTER_INVITED) {
			if ($this->_arg('code') !== null || $person['Person']['id'] == $this->Auth->user('id')) {
				// A player has declined an invitation
				$captains = $this->_initRosterCaptains ($team);

				if (!$this->_sendMail (array (
						'to' => $captains,
						'replyTo' => $person,
						'subject' => "Invitation to join {$team['Team']['name']} was declined",
						'template' => 'roster_decline_invite',
						'sendAs' => 'both',
				)))
				{
					$this->Session->setFlash(__('Error sending email to team captains.', true));
					return false;
				}
			} else {
				// A captain has removed an invitation
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
					$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
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
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
				return false;
			}
		}
		return true;
	}

	function _sendChange ($person, $team, $position) {
		$this->_initRosterEmail($person, $team, $position);

		$this->set (array(
			'reply' => $this->Session->read('Zuluru.Person.email'),
			'old_position' => $person['Person']['TeamsPerson']['position'],
		));

		if ($person['Person']['id'] == $this->Auth->user('id')) {
			// A player has changed themselves
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "Removal from {$team['Team']['name']} roster",
					'template' => 'roster_change_by_player',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(__('Error sending email to team captains.', true));
				return false;
			}
		} else {
			$this->set (array(
				'captain' => $this->Session->read('Zuluru.Person.full_name'),
			));

			if (!$this->_sendMail (array (
					'to' => $person,
					'replyTo' => $this->Session->read('Zuluru.Person'),
					'subject' => "Change of roster position on {$team['Team']['name']}",
					'template' => 'roster_change_by_captain',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
				return false;
			}
		}

		return true;
	}

	function _sendRemove ($person, $team) {
		$this->_initRosterEmail($person, $team);

		$this->set (array(
			'reply' => $this->Session->read('Zuluru.Person.email'),
			'old_position' => $person['Person']['TeamsPerson']['position'],
		));

		if ($person['Person']['id'] == $this->Auth->user('id')) {
			// A player has removed themselves
			$captains = $this->_initRosterCaptains ($team);

			if (!$this->_sendMail (array (
					'to' => $captains,
					'replyTo' => $person,
					'subject' => "Removal from {$team['Team']['name']} roster",
					'template' => 'roster_remove_by_player',
					'sendAs' => 'both',
			)))
			{
				$this->Session->setFlash(__('Error sending email to team captains.', true));
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
				$this->Session->setFlash(sprintf (__('Error sending email to %s.', true), $person['Person']['full_name']));
				return false;
			}
		}

		return true;
	}

	function _initRosterEmail ($person, $team, $position = null) {
		$this->set (array(
			'person' => $person['Person'],
			'team' => $team['Team'],
			'league' => $team['League'],
			'position' => $position,
		));
	}

	function _initRosterCaptains ($team) {
		// Find the list of captains and assistants for the team
		$this->Team->contain (array(
			'Person' => array(
				'conditions' => array(
					'TeamsPerson.position' => Configure::read('privileged_roster_positions'),
					'TeamsPerson.status' => ROSTER_APPROVED,
				),
				'fields' => array('id', 'first_name', 'last_name', 'email'),
			),
		));
		$captains = $this->Team->read (null, $team['Team']['id']);
		$this->set ('captains', implode (', ', Set::extract ('/Person/first_name', $captains)));

		return $captains;
	}
}
?>
