<?php
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.controller
 */
class AppController extends Controller {
	var $components = array('Session', 'Auth', 'Cookie', 'RequestHandler');
	var $uses = array('Person', 'Configuration');
	var $helpers = array('Session', 'Html', 'ZuluruHtml', 'Form', 'ZuluruForm', 'Time', 'ZuluruTime', 'Number', 'Text', 'Js');
	var $view = 'Theme';

	var $menu_items = array();

	function beforeFilter() {
		parent::beforeFilter();

		// Use the configured model for handling hashing of passwords, and configure
		// the Auth field names using it
		$this->Auth->userModel = Configure::read('security.auth_model');
		$this->Auth->authenticate = ClassRegistry::init($this->Auth->userModel);
		$this->Auth->fields = array(
				'username'	=> $this->Auth->authenticate->userField,
				'password' => $this->Auth->authenticate->pwdField,
		);

		// Save a couple of bits of information from the selected auth model in the
		// configuration, so that it's accessible from anywhere instead of just places
		// that can access the model
		Configure::write('feature.manage_accounts', $this->Auth->authenticate->manageAccounts);
		Configure::write('feature.manage_name', $this->Auth->authenticate->manageName);

		$this->_setPermissions();

		// Load configuration from database
		if (isset($this->Configuration) && !empty($this->Configuration->table))
		{
			$this->Configuration->load($this->Auth->user('id'));
		}

		// Set up various URLs to use
		// TODO: Read these from site configuration
		if (! $this->Session->read('Zuluru.external_login')) {
			$this->Auth->loginAction = array('controller' => 'users', 'action' => 'login');
		} else {
			$this->Auth->loginAction = array('controller' => 'leagues', 'action' => 'index');
		}
		$this->Auth->logoutAction = array('controller' => 'users', 'action' => 'logout');
		$this->Auth->autoRedirect = false;
		$this->Auth->loginRedirect = '/';
		$this->Auth->logoutRedirect = '/';
		if ($this->is_logged_in) {
			$this->Auth->authError = __('You do not have permission to access that page.', true);
		} else {
			$this->Auth->authError = __('You must login to access full site functionality.', true);
		}

		$this->_initSessionData($this->Auth->user('id'));

		// Check if we need to redirect logged-in users for some required step first
		if ($this->is_member && $this->action != 'logout') {
			$email = $this->Session->read('Zuluru.Person.email');
			if (($this->name != 'People' || $this->action != 'edit') && empty ($email)) {
				$this->Session->setFlash(__('Last time we tried to contact you, your email bounced. We require a valid email address as part of your profile. You must update it before proceeding.', true));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			if (($this->name != 'People' || $this->action != 'edit') && $this->Session->read('Zuluru.Person.complete') == 0) {
				$this->Session->setFlash(__('Your player profile is incomplete. You must update it before proceeding.', true));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			// Force response to roster requests, if enabled
			if (Configure::read('feature.force_roster_request')) {
				$teams = Set::extract ('/TeamsPerson[status=' . ROSTER_INVITED . ']/..', $this->Session->read('Zuluru.Teams'));
				$response_required = array();
				foreach ($teams as $team) {
					// Only force responses to leagues that have started play, but the roster deadline hasn't passed
					if ($team['League']['open'] < date('Y-m-d') && $team['League']['roster_deadline'] >= date('Y-m-d')) {
						$response_required[] = $team['Team']['id'];
					}
				}
				if (!empty ($response_required) &&
					// We will let people look at information about teams that they've been invited to
					($this->name != 'Teams' || !in_array ($this->_arg('team'), $response_required)))
				{
					$this->Session->setFlash(__('You have been invited to join a team, and must either accept or decline this invitation before proceeding. Before deciding, you have the ability to look at this team\'s roster, schedule, etc.', true));
					$this->redirect (array('controller' => 'teams', 'action' => 'view', 'team' => array_shift($response_required), 'person' => $this->Auth->user('id')));
				}
			}
		}

		$this->_initMenu();
	}

	function beforeRender() {
		parent::beforeRender();

		// Set the theme, if any
		$this->theme = Configure::read('theme');

		// Set view variables for the menu
		// TODO: Get the menu element name from some configuration, maybe per-user?
		$this->set('menu_element', 'flyout');
		$this->set('menu_items', $this->menu_items);
	}

	/**
	 * Read and set variables for the database-based address options.
	 */
	function _loadAddressOptions() {
		if (!isset ($this->Province)) {
			$this->Province = ClassRegistry::init('Province');
		}
		$provinces = $this->Province->find('all');
		$provinces = Set::combine($provinces, '{n}.Province.name', '{n}.Province.name');
		$this->set('provinces', $provinces);

		if (!isset ($this->Country)) {
			$this->Country = ClassRegistry::init('Country');
		}
		$countries = $this->Country->find('all');
		$countries = Set::combine($countries, '{n}.Country.name', '{n}.Country.name');
		$this->set('countries', $countries);
	}

	/**
	 * Read and set variables for the database-based group options.
	 */
	function _loadGroupOptions() {
		$groups = $this->Group->find('all');
		$groups = Set::combine($groups, '{n}.Group.id', '{n}.Group.name');
		$this->set('groups', $groups);
	}

	/**
	 * Basic check for authorization, based solely on the person's login group.
	 * Set some "is_" variables for the views to use (is_admin, is_member, etc.).
	 *
	 * This allows admins access to anything.  Individual controllers should override
	 * this to control access to their individual actions.
	 *
	 * TODO: This should all be replaced with some group-based permission scheme. The
	 * good news is that those changes will mainly be localized to this function.
	 *
	 * @access public
	 */
	function _setPermissions() {
		$this->is_admin = $this->is_volunteer = $this->is_member = $this->is_logged_in = false;
		$auth =& $this->Auth->authenticate;
		$user = $this->Auth->user();

		// If this is a no-authentication-required page, and the user has no session
		// but does have "remember me" login information saved in a cookie, we want
		// to redirect to the login page, which will just log them in automatically
		// and send them right back here. If we don't do that, then their permissions
		// aren't set up correctly, and menus and views will be wrong.
		if (!$user && $this->Cookie->read('Auth.User')) {
			$login = array('controller' => 'users', 'action' => 'login');
			if ($this->here != Router::url($login)) {
				$this->Session->write('Auth.redirect', $this->here);
				$this->redirect ($login);
			}
		}

		// Perform any additional login processing that may be required;
		// the Auth user information may be updated by this process
		$login = $this->_getComponent ('Login', $auth->loginComponent, $this);
		if ($login->expire() || !$user) {
			$this->Session->delete ('Zuluru');
			$login->login();
			$user = $this->Auth->user();

			if ($user && method_exists ($auth, 'merge_user_record')) {
				$auth->merge_user_record($user);
				$this->Session->delete('Zuluru.Person');
			}
		}

		// Some user models don't include an "id" field; copy over from the primary key
		// in these cases
		if ($user) {
			if (!array_key_exists ('id', $user[$auth->alias])) {
				$id = $user[$auth->alias][$auth->primaryKey];
				$this->Session->write("{$this->Auth->sessionKey}.id", $id);
			} else {
				$id = $user[$auth->alias]['id'];
			}

			// Make sure the person and group records are in the session
			$person = $this->_findSessionData('Person', $this->Person, $id);
			$group = $this->_findSessionData('Group', $this->Person->Group, $person['group_id']);
		} else {
			$group = null;
		}

		if (is_array ($group)) {
			if (array_key_exists ('name', $group)) {
				$group = $group['name'];
			} else {
				$group = 'Non-player account';
			}
		}

		// We intentionally fall through from the higher groups to the lower.
		switch ($group) {
			case 'Administrator':
				$this->is_admin = true;

			case 'Volunteer':
				$this->is_volunteer = true;

			case 'Player':
				$this->is_member = true;

			case 'Non-player account':
				$this->is_logged_in = true;
		}

		// Set these in convenient locations for views to use
		$this->set('is_admin', $this->is_admin);
		$this->set('is_volunteer', $this->is_volunteer);
		$this->set('is_member', $this->is_member);
		$this->set('is_logged_in', $this->is_logged_in);
		$this->set('my_id', $this->Auth->user('id'));

		// While the options above steadily decrease the output available,
		// is_visitor is instead used to add output not shown to anyone else,
		// like 'Why not become a member and enjoy extra benefits?'
		// In other words, the amount of output generated is like this:
		// admin > volunteer > member > not logged in < visitor
		$this->is_visitor = ! $this->is_member;
		$this->set('is_visitor', $this->is_visitor);

		if ($this->is_admin) {
			// Admins have permission to do anything.
			$this->Auth->allow('*');
		} else {
			// Allow anyone (logged on or not) to log in or log out, list and view things.
			// 'View' goes by various names.
			// Roster and attendance updates may come from emailed links; people might not be logged in
			// 'Payment' comes from the payment processor.
			$this->Auth->allow(array('login', 'logout', 'create_account', 'reset_password', 'index', 'wizard',
					'view', 'display', 'schedule', 'standings', 'ical', 'letter',
					'roster_accept', 'roster_decline', 'attendance_change',
					'payment', 'cron'));
		}

		// Other authentication is handled through the isAuthorized function of
		// the individual controllers.
		$this->Auth->authorize = 'controller';
	}

	// By default, we allow the actions listed above (in the Auth->allow calls) and
	// nothing else. Any controller with special permissions must override this function.
	function isAuthorized() {
		return false;
	}

	function _arg($key) {
		if (array_key_exists ($key, $this->passedArgs)) {
			return $this->passedArgs[$key];
		} else {
			return null;
		}
	}

	function _limitOverride($team_id) {
		$on_team = in_array ($team_id, $this->Session->read('Zuluru.TeamIDs'));

		$this->effective_admin = ($this->is_admin && !$on_team);
		$this->set('is_effective_admin', $this->effective_admin);

		$leagues = $this->Session->read('Zuluru.Leagues');
		$teams = Set::extract ('/Team/id', $leagues);
		$this->effective_coordinator = (in_array ($team_id, $teams) && !$on_team);
		$this->set('is_effective_coordinator', $this->effective_coordinator);
	}

	/**
	 * Put basic items on the menu, some based on configuration settings.
	 * Other items like specific teams and leagues are added elsewhere.
	 */
	function _initMenu()
	{
		// Initialize the menu
		$this->menu_items = array();
		if ($this->is_logged_in) {
			$this->_addMenuItem ('Home', array('controller' => 'all', 'action' => 'splash'));
			$this->_addMenuItem ('My Profile', array('controller' => 'people', 'action' => 'view'));
			$this->_addMenuItem ('Edit', array('controller' => 'people', 'action' => 'edit'), 'My Profile');
			$this->_addMenuItem ('Preferences', array('controller' => 'people', 'action' => 'preferences'), 'My Profile');
			if (Configure::read('feature.manage_accounts')) {
				$this->_addMenuItem ('Change password', array('controller' => 'users', 'action' => 'change_password'), 'My Profile');
			}
			$this->_addMenuItem ('Upload photo', array('controller' => 'people', 'action' => 'photo_upload'), 'My Profile');
		}

		if (Configure::read('feature.registration')) {
			$this->_addMenuItem ('Registration', array('controller' => 'events', 'action' => 'wizard'));
			$this->_addMenuItem ('All events', array('controller' => 'events', 'action' => 'index'), 'Registration');
			if ($this->is_logged_in) {
				$this->_addMenuItem ('My history', array('controller' => 'people', 'action' => 'registrations'), 'Registration');
			}
			if ($this->is_admin) {
				$this->_addMenuItem ('Preregistrations', array('controller' => 'events', 'action' => 'preregistrations'), 'Registration');
				$this->_addMenuItem ('Unpaid', array('controller' => 'registrations', 'action' => 'unpaid'), 'Registration');
				$this->_addMenuItem ('Report', array('controller' => 'registrations', 'action' => 'report'), 'Registration');
				$this->_addMenuItem ('Create event', array('controller' => 'events', 'action' => 'add'), 'Registration');
				$this->_addMenuItem ('Questionnaires', array('controller' => 'questionnaires', 'action' => 'index'), 'Registration');
				$this->_addMenuItem ('Questions', array('controller' => 'questions', 'action' => 'index'), array('Registration', 'Questionnaires'));
				$this->_addMenuItem ('Deactivated', array('controller' => 'questionnaires', 'action' => 'deactivated'), array('Registration', 'Questionnaires'));
				$this->_addMenuItem ('Deactivated', array('controller' => 'questions', 'action' => 'deactivated'), array('Registration', 'Questionnaires', 'Questions'));
			}
		}

		// Add the personal menu items next, so that specific teams and leagues
		// are the first sub-menus in the Teams and Leagues menus, rather than
		// the generic operations.
		if ($this->is_logged_in) {
			$this->_initPersonalMenu();
		}

		if ($this->is_logged_in) {
			$this->_addMenuItem ('Teams', array('controller' => 'teams', 'action' => 'index'));
			// If registrations are enabled, it takes care of team creation
			if ($this->is_admin || !Configure::read('feature.registration')) {
				$this->_addMenuItem ('Create team', array('controller' => 'teams', 'action' => 'add'), 'Teams');
			}
			if ($this->is_admin) {
				$this->_addMenuItem ('Unassigned teams', array('controller' => 'teams', 'action' => 'unassigned'), 'Teams');
			}
			$this->_addMenuItem ('My history', array('controller' => 'people', 'action' => 'teams'), 'Teams');
		}

		$this->_addMenuItem ('Leagues', array('controller' => 'leagues', 'action' => 'index'));
		if ($this->is_admin) {
			$this->_addMenuItem ('League summary', array('controller' => 'leagues', 'action' => 'summary'), 'Leagues');
			$this->_addMenuItem ('Create league', array('controller' => 'leagues', 'action' => 'add'), 'Leagues');
		}

		$this->_addMenuItem ('Fields', array('controller' => 'fields', 'action' => 'index'));
		if ($this->is_admin) {
			$this->_addMenuItem ('Closed fields', array('controller' => 'fields', 'action' => 'closed'), 'Fields');
			$this->_addMenuItem ('Create field', array('controller' => 'fields', 'action' => 'add'), 'Fields');
			$this->_addMenuItem ('Add bulk gameslots', array('controller' => 'game_slots', 'action' => 'add'), 'Fields');
		}

		if ($this->is_logged_in) {
			$this->_addMenuItem ('Search', array('controller' => 'people', 'action' => 'search'), 'Players');
		}

		if ($this->is_admin) {
			if (!isset ($this->Person)) {
				$this->Person = ClassRegistry::init ('Person');
			}
			$new = $this->Person->find ('count', array(
				'conditions' => array(
					'status' => 'new',
					'complete' => 1,
				),
			));
			if ($new > 0) {
				$this->_addMenuItem ("Approve new accounts ($new pending)", array('controller' => 'people', 'action' => 'list_new'), 'Players');
			}

			if (!isset ($this->Person->Upload)) {
				$this->Person->Upload = ClassRegistry::init ('Upload');
			}
			$new = $this->Person->Upload->find ('count', array(
				'conditions' => array(
					'approved' => 0,
					'type' => 'person',
				),
			));
			if ($new > 0) {
				$this->_addMenuItem ("Approve new photos ($new pending)", array('controller' => 'people', 'action' => 'approve_photos'), 'Players');
			}
		}

		if ($this->is_admin) {
			$this->_addMenuItem ('Settings');
			$this->_addMenuItem ('Organization', array('controller' => 'settings', 'action' => 'organization'), 'Settings');
			$this->_addMenuItem ('Features', array('controller' => 'settings', 'action' => 'feature'), 'Settings');
			$this->_addMenuItem ('Email', array('controller' => 'settings', 'action' => 'email'), 'Settings');
			$this->_addMenuItem ('Scoring', array('controller' => 'settings', 'action' => 'scoring'), 'Settings');
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem ('Registration', array('controller' => 'settings', 'action' => 'registration'), 'Settings');
				if (Configure::read('registration.online_payments')) {
					$this->_addMenuItem ('Payment', array('controller' => 'settings', 'action' => 'payment'), 'Settings');
				}
			}

			$this->_addMenuItem ('Statistics');
			$this->_addMenuItem ('Player', array('controller' => 'people', 'action' => 'statistics'), 'Statistics');
			$this->_addMenuItem ('Team', array('controller' => 'teams', 'action' => 'statistics'), 'Statistics');
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem ('Registration', array('controller' => 'registrations', 'action' => 'statistics'), 'Statistics');
			}
		}

		if (! $this->Session->read('Zuluru.external_login')) {
			if ($this->is_logged_in) {
				$this->_addMenuItem ('Logout', array('controller' => 'users', 'action' => 'logout'));
			} else {
				$this->_addMenuItem ('Login', array('controller' => 'users', 'action' => 'login'));
			}
		}

		if (Configure::read('feature.manage_accounts')) {
			if (!$this->is_logged_in) {
				$this->_addMenuItem ('Reset password', array('controller' => 'users', 'action' => 'reset_password'));
			}
			if (!$this->is_logged_in) {
				$this->_addMenuItem ('Create account', array('controller' => 'users', 'action' => 'create_account'));
			} else if ($this->is_admin) {
				$this->_addMenuItem ('Create account', array('controller' => 'users', 'action' => 'create_account'), 'Players');
			}
		}

		$this->_addMenuItem ('Help', array('controller' => 'help'));
	}

	function _initSessionData($my_id) {
		if (!$my_id) {
			foreach (array('Unpaid', 'Teams', 'TeamIDs', 'OwnedTeams', 'OwnedTeamIDs', 'Leagues', 'LeagueIDs') as $key) {
				$this->Session->write("Zuluru.$key", array());
			}
			return;
		}

		$unpaid = $this->Session->read('Zuluru.Unpaid');
		if (empty($unpaid)) {
			if (!isset ($this->Registration)) {
				if (!class_exists ('Registration')) {
					App::import ('Model', 'Registration');
				}
				$this->Registration = new Registration();
			}
			$this->_findSessionData('Unpaid', $this->Registration, array(
					'recursive' => -1,
					'conditions' => array(
						'person_id' => $my_id,
						'payment' => array('Unpaid', 'Pending'),
					),
			));
		}

		$teams = $this->Session->read('Zuluru.Teams');
		if (empty($teams)) {
			if (!isset ($this->Team)) {
				$this->Team = ClassRegistry::init ('Team');
			}
			$teams = $this->Team->readByPlayerId ($my_id);

			$this->Session->write ('Zuluru.Teams', $teams);
			$this->Session->write ('Zuluru.TeamIDs', Set::extract ('/Team/id', $teams));

			$positions = Configure::read('privileged_roster_positions');
			$owned_teams = array();
			foreach ($teams as $team) {
				if (in_array ($team['TeamsPerson']['position'], $positions) &&
					$team['TeamsPerson']['status'] == ROSTER_APPROVED)
				{
					$owned_teams[] = $team;
				}
			}

			$this->Session->write ('Zuluru.OwnedTeams', $owned_teams);
			$this->Session->write ('Zuluru.OwnedTeamIDs', Set::extract ('/Team/id', $owned_teams));
		}

		$leagues = $this->Session->read('Zuluru.Leagues');
		if (empty($leagues)) {
			if (!isset ($this->League)) {
				if (!class_exists ('League')) {
					App::import ('Model', 'League');
				}
				$this->League = new League();
			}
			$leagues = $this->League->readByPlayerId ($my_id, true, true);

			$this->Session->write ('Zuluru.Leagues', $leagues);
			$this->Session->write ('Zuluru.LeagueIDs', Set::extract ('/League/id', $leagues));
		}
	}

	/**
	 * Delete all of the cached session information related to teams.
	 */
	function _deleteTeamSessionData() {
		$this->Session->delete('Zuluru.Teams');
		$this->Session->delete('Zuluru.TeamIDs');
		$this->Session->delete('Zuluru.OwnedTeams');
		$this->Session->delete('Zuluru.OwnedTeamIDs');
	}

	/**
	 * Put personalized items like specific teams and leagues on the menu.
	 */
	function _initPersonalMenu() {
		if (Configure::read('feature.registration')) {
			$unpaid = $this->Session->read('Zuluru.Unpaid');
			if (!empty ($unpaid)) {
				$this->_addMenuItem ('Checkout', array('controller' => 'registrations', 'action' => 'checkout'), 'Registration');
			}
		}

		$teams = $this->Session->read('Zuluru.Teams');
		foreach ($teams as $team) {
			$this->_addTeamMenuItems ($team);
		}

		$leagues = $this->Session->read('Zuluru.Leagues');
		foreach ($leagues as $league) {
			$this->_addLeagueMenuItems ($league);
		}
	}

	/**
	 * Add all the links for a team to the menu.
	 */
	function _addTeamMenuItems($team) {
		$is_captain = in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
		$this->_limitOverride($team['Team']['id']);

		$this->_addMenuItem ($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), 'Teams', "{$team['Team']['name']}::{$team['Team']['id']}");
		$this->_addMenuItem ('Schedule', array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		$this->_addMenuItem ('Standings', array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		if ($team['Team']['track_attendance'] &&
			in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			$this->_addMenuItem ('Attendance', array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		}
		if ($this->is_logged_in && $team['Team']['open_roster'] && $team['League']['roster_deadline'] >= date('Y-m-d') &&
			!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			$this->_addMenuItem ('Join team', array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		}
		if ($this->is_admin || $is_captain) {
			$this->_addMenuItem ('Delete', array('controller' => 'teams', 'action' => 'delete', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
			$this->_addMenuItem ('Edit', array('controller' => 'teams', 'action' => 'edit', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
			$this->_addMenuItem ('Player emails', array('controller' => 'teams', 'action' => 'emails', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		}
		if ($this->effective_admin ||
			(($is_captain || $this->effective_coordinator) && $team['League']['roster_deadline'] >= date('Y-m-d')))
		{
			$this->_addMenuItem ('Add player', array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		}
		if ($this->effective_admin) {
			$this->_addMenuItem ('Move', array('controller' => 'teams', 'action' => 'move', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
			$this->_addMenuItem ('Spirit', array('controller' => 'teams', 'action' => 'spirit', 'team' => $team['Team']['id']), array('Teams', "{$team['Team']['name']}::{$team['Team']['id']}"));
		}
	}

	/**
	 * Add all the links for a league to the menu.
	 */
	function _addLeagueMenuItems($league) {
		$is_coordinator = in_array($league['League']['id'], $this->Session->read('Zuluru.LeagueIDs'));

		$this->_addMenuItem ($league['League']['name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']), 'Leagues');
		$this->_addMenuItem ('Schedule', array('controller' => 'leagues', 'action' => 'schedule', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		$this->_addMenuItem ('Standings', array('controller' => 'leagues', 'action' => 'standings', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		$this->_addMenuItem ('Scores', array('controller' => 'leagues', 'action' => 'scores', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		if ($this->is_admin || $is_coordinator) {
			$this->_addMenuItem ('Add Games', array('controller' => 'schedules', 'action' => 'add', 'league' => $league['League']['id']), array('Leagues', $league['League']['name'], 'Schedule'));
			$this->_addMenuItem ('Approve scores', array('controller' => 'leagues', 'action' => 'approve_scores', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Edit', array('controller' => 'leagues', 'action' => 'edit', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Field distribution', array('controller' => 'leagues', 'action' => 'fields', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Available fields', array('controller' => 'leagues', 'action' => 'slots', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Status report', array('controller' => 'leagues', 'action' => 'status', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Validate ratings', array('controller' => 'leagues', 'action' => 'validate_ratings', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('All stars', array('controller' => 'leagues', 'action' => 'allstars', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Captain emails', array('controller' => 'leagues', 'action' => 'emails', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Spirit Report', array('controller' => 'leagues', 'action' => 'spirit', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
			$this->_addMenuItem ('Download', array('controller' => 'leagues', 'action' => 'spirit', 'league' => $league['League']['id'], 'ext' => 'csv'), array('Leagues', $league['League']['name'], 'Spirit Report'));
		}
		if ($this->is_admin) {
			$this->_addMenuItem ('Add coordinator', array('controller' => 'leagues', 'action' => 'add_coordinator', 'league' => $league['League']['id']), array('Leagues', $league['League']['name']));
		}

		// Some items are only applicable depending on league configuration
		if (!empty ($league['League']['schedule_type'])) {
			$league_obj = $this->_getComponent ('LeagueType', $league['League']['schedule_type'], $this);
			$league_obj->addMenuItems ($league, $is_coordinator);
		}
	}

	/**
	 * Add all the links for a field to the menu.
	 */
	function _addFieldMenuItems($field) {
		$this->_addMenuItem ($field['Field']['name'], array('controller' => 'fields', 'action' => 'view', 'field' => $field['Field']['id']), 'Fields');
		$this->_addMenuItem ('View bookings', array('controller' => 'fields', 'action' => 'bookings', 'field' => $field['Field']['id']), array('Fields', $field['Field']['name']));
		if ($this->is_admin) {
			$this->_addMenuItem ('Add Game Slot', array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id']), array('Fields', $field['Field']['name']));
		}
		if ($this->is_admin || $this->is_volunteer) {
			$this->_addMenuItem ('Edit Field', array('controller' => 'fields', 'action' => 'edit', 'field' => $field['Field']['id']), array('Fields', $field['Field']['name']));
			$this->_addMenuItem ('Edit Layout', array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id']), array('Fields', $field['Field']['name']));
		}
	}

	/**
	 * Helper function used for managing various pluggable components.
	 *
	 * @param mixed $type The component type (LeagueType, SpiritQuestionnaire, etc.).
	 *					  This is the name of the component base class, and also part
	 *					  of the name of each extension class.
	 * @param mixed $specific The name of the component implementation. The name of
	 *					  the component class includes both $type and $specific. Leave
	 *					  empty to get the base component.
	 * @param mixed $controller The controller to initialize the component with.
	 * @return mixed An object of the specified type. This will be cached, so multiple
	 *					  objects are not created unnecessarily.
	 *
	 */
	static function &_getComponent ($type, $specific = '', &$controller = null, $unique = false, $config = null) {
		static $component_cache = array();

		if (!array_key_exists ($type, $component_cache)) {
			App::import ('Component', $type);
			$component_cache[$type] = array();
		}

		$class = $type . Inflector::camelize (low($specific));
		$full_class = $class . 'Component';
		if (!class_exists ($full_class)) {
			App::import ('Component', $class);
		}
		if (!class_exists ($full_class)) {
			echo("cannot find the class $full_class");
			exit;
		}

		if ($unique) {
			$obj = new $full_class($controller);
			$obj->name = $full_class;
			return $obj;
		}

		if (!array_key_exists ($specific, $component_cache[$type])) {
			$component_cache[$type][$specific] =& new $full_class($controller);
			$component_cache[$type][$specific]->name = $full_class;
		} else {
			// We might have initialized this without a controller (from a model), in
			// which case we'll update the controller now to the current one.
			if ($component_cache[$type][$specific]->_controller === null) {
				$component_cache[$type][$specific]->_controller =& $controller;
			}
		}

		if ($config) {
			$component_cache[$type][$specific]->configure ($config);
		}

		return $component_cache[$type][$specific];
	}

	/**
	 * Add a single item to the menu.
	 */
	function _addMenuItem($name, $url = null, $path = array(), $sort = null) {
		if ($sort === null)
			$sort = $name;
		if (!is_array ($path))
			$path = array($path);
		$parent =& $this->menu_items;
		foreach ($path as $element) {
			if (!array_key_exists ($element, $parent)) {
				$parent[$element] = array('items' => array(), 'name' => $element);
			}
			$parent =& $parent[$element]['items'];
		}

		if (!array_key_exists ($name, $parent)) {
			$parent[$sort] = array('items' => array(), 'name' => $name);
		}

		if ($url) {
			$parent[$sort]['url'] = $url;
		}
	}

	/**
	 * Wrapper around the email component, simplifying sending the kinds of emails we want to send.
	 *
	 * @param mixed $opts Array of options controlling the email.
	 * @return mixed true if the email was sent, false otherwise.
	 *
	 */
	function _sendMail ($opts) {
		App::import('Component', 'Email');
		$email = new EmailComponent();

		// Set up default values where applicable
		if (!array_key_exists ('from', $opts)) {
			$opts['from'] = Configure::read('email.admin_name') . ' <' . Configure::read('email.admin_email') . '>';
		}

		// Set some details from the configuration
		if (Configure::read('email.use_smtp')) {
			$opts['delivery'] = 'smtp';
			$opts['smtpOptions'] = array_merge ($email->smtpOptions, Configure::read('email.smtp_options'));
		}
		if (Configure::read('email.debug')) {
			$opts['delivery'] = 'debug';
		}

		// We may have been given complex Person arrays that the sender wants us to extract details from
		foreach (array('to' => true, 'cc' => true, 'bcc' => true, 'from' => false, 'replyTo' => false) as $key => $array) {
			if (array_key_exists ($key, $opts)) {
				$opts[$key] = $this->_extractEmails($opts[$key], $array);
			}
		}
		// TODO: Make sure there's only one From or ReplyTo address

		// Check if a configurable email was requested; if not, there is no content
		// here, it is generated by the specified template instead
		$content = null;
		if (array_key_exists ('config_subject', $opts) && array_key_exists ('variables', $opts)) {
			// Add a couple of common variables
			$opts['variables'] = array_merge ($opts['variables'], array(
					'%adminname' => Configure::read('email.admin_name'),
					'%adminemail' => Configure::read('email.admin_email'),
					'%site' => Configure::read('organization.name'),
					'%url' => Router::url ('/', true),
			));

			$opts['subject'] = strtr (Configure::read("email.{$opts['config_subject']}"), $opts['variables']);
			$content = strtr (Configure::read("email.{$opts['config_body']}"), $opts['variables']);
			unset ($opts['config']);
			unset ($opts['variables']);
		}

		// Get ready and send it
		$email->initialize ($this, $opts);
		$success = $email->send($content);

		if (! empty ($email->smtpError))
		{
			$this->log("smtp-errors: {$email->smtpError}");
		}

		return $success;
	}

	function _extractEmails($input, $array = false) {
		if (is_array ($input)) {
			$emails = Set::extract ('/email_formatted', $input);
			if (empty ($emails)) {
				$emails = Set::extract ('/Person/email_formatted', $input);
			}
			if (empty ($emails)) {
				$emails = Set::extract ('/email', $input);
			}
			if (empty ($emails)) {
				$emails = Set::extract ('/Person/email', $input);
			}
			if (empty ($emails)) {
				$model = Configure::read('security.auth_model');
				$emails = Set::extract ("/$model/email_formatted", $input);
			}
			if (count ($emails) == 1 && !$array) {
				return array_shift ($emails);
			}
			return $emails;
		}
		// Anything else, return as-is and hope for the best!
		if ($array) {
			return array($input);
		} else {
			return $input;
		}
	}

	function _extractSearchParams() {
		if (is_array($this->data)) {
			$params = array_merge ($this->data, $this->params['named']);
		} else {
			$params = $this->params['named'];
		}
		foreach (array('sort', 'direction', 'page') as $pagination)
			unset ($params[$pagination]);

		return $params;
	}

	function _mergePaginationParams() {
		if (is_array($this->data)) {
			foreach (array('sort', 'direction', 'page') as $pagination) {
				if (array_key_exists ($pagination, $this->data)) {
					$this->passedArgs[$pagination] = $this->data[$pagination];
				}
			}
		}
	}

	function _generateSearchConditions($params, $model = null) {
		$conditions = array();
		if ($model == null) {
			$model = Inflector::singularize($this->name);
		}
		$model_obj = $this->{$model};

		foreach ($params as $field => $value) {
			if (!array_key_exists ($field, $model_obj->_schema))
				continue;

			// Add each element of the search string one by one
			foreach(split (' ', $value) as $str)
			{
				$term = "$model.$field";
				if ($str)
				{
					if (strpos ($str, '*') !== false) {
						$term .= ' LIKE';
						$str = strtr ($str, '*', '%');
					}
					$conditions[] = array($term => $str);
				}
			}
		}

		return $conditions;
	}

	function _findSessionData($key, &$model, $find = null) {
		$data = $this->Session->read("Zuluru.$key");
		if ($data === null) {
			if (is_numeric ($find)) {
				$model->recursive = -1;
				$data = $model->read(null, $find);
				$data = $data[$model->alias];
			} else {
				if ($find === null) {
					$find = array(
						'recursive' => -1,
						'conditions' => array(
							'person_id' => $this->Auth->user('id'),
						),
					);
				}
				$data = $model->find('all', $find);
				$data = Set::extract("/{$model->alias}/.", $data);
			}
			$this->Session->write("Zuluru.$key", $data);

			// We don't want this data hanging around in $model->data to mess up later saves
			$model->data = null;
		}
		return $data;
	}

	function _findWaiver($waivers, $date) {
		foreach ($waivers as $waiver) {
			if ($waiver['created'] <= $date && $waiver['expires'] >= $date) {
				return $waiver;
			}
		}
		return null;
	}

	function _checkWaiver($event) {
		$type = $event['waiver_type'];
		switch ($type) {
			case 'membership':
				$check_date = $event['membership_ends'];
				break;

			case 'event':
				// TODO: There must be a better date than the registration closing
				$check_date = $event['close'];
				break;

			default:
				return false;
		}

		$waivers = $this->_findSessionData('Waivers', $this->Person->Waiver, array(
				'recursive' => -1,
				'conditions' => array(
					'person_id' => $this->Auth->user('id'),
				),
		));
		$waiver = $this->_findWaiver($waivers, $check_date);
		if ($waiver === null) {
			$this->Session->setFlash(__('You must sign the waiver before proceeding.', true));
			$this->redirect (array('controller' => 'people', 'action' => 'sign_waiver', 'type' => $type, 'year' => $this->membershipYear ($check_date), 'event' => $event['id']));
		} else {
			return $this->membershipYear ($check_date);
		}
	}

	function membershipYear($date = null) {
		if ($date === null) {
			$date = time();
		} else {
			$date = strtotime ($date);
		}

		$year = date('Y', $date);
		$month = $this->membershipYearEndMonth();
		$day = $this->membershipYearEndDay();
		if ($month <= '06' && (date('m', $date) < $month ||
			(date('m', $date) == $month && date('d', $date) <= $day)))
		{
			-- $year;
		}
		if ($month > '06' && (date('m', $date) > $month ||
			(date('m', $date) == $month && date('d', $date) > $day)))
		{
			++ $year;
		}
		return $year;
	}

	function membershipEnd($year) {
		$month = $this->membershipYearEndMonth();
		$day = $this->membershipYearEndDay();
		if ($month <= '06') {
			++ $year;
		}
		return "$year-$month-$day";
	}

	function membershipYearEndMonth() {
		return Configure::read('organization.year_end');
	}

	function membershipYearEndDay() {
		return '31';
	}
}
?>
