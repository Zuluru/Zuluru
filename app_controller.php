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

		// Requests made through requestAction don't need any of the rest of this
		if (array_key_exists('requested', $this->params) && $this->params['requested']) {
			return;
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
				$this->Session->setFlash(__('Last time we tried to contact you, your email bounced. We require a valid email address as part of your profile. You must update it before proceeding.', true), 'default', array('class' => 'warning'));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			if (($this->name != 'People' || $this->action != 'edit') && $this->Session->read('Zuluru.Person.complete') == 0) {
				$this->Session->setFlash(__('Your player profile is incomplete. You must update it before proceeding.', true), 'default', array('class' => 'warning'));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			// Force response to roster requests, if enabled
			if (Configure::read('feature.force_roster_request')) {
				$teams = Set::extract ('/TeamsPerson[status=' . ROSTER_INVITED . ']/..', $this->Session->read('Zuluru.Teams'));
				$response_required = array();
				foreach ($teams as $team) {
					// Only force responses to leagues that have started play, but the roster deadline hasn't passed
					if ($team['Division']['open'] < date('Y-m-d') && $team['Division']['roster_deadline'] >= date('Y-m-d')) {
						$response_required[] = $team['Team']['id'];
					}
				}
				if (!empty ($response_required) &&
					// We will let people look at information about teams that they've been invited to
					($this->name != 'Teams' || !in_array ($this->_arg('team'), $response_required)))
				{
					$this->Session->setFlash(__('You have been invited to join a team, and must either accept or decline this invitation before proceeding. Before deciding, you have the ability to look at this team\'s roster, schedule, etc.', true), 'default', array('class' => 'info'));
					$this->redirect (array('controller' => 'teams', 'action' => 'view', 'team' => array_shift($response_required), 'person' => $this->Auth->user('id')));
				}
			}
		}

		$this->_initMenu();

		// Set the theme, if any. Must be done before processing, in order for the theme to affect emails.
		$this->theme = Configure::read('theme');
	}

	function beforeRender() {
		parent::beforeRender();

		// Set the theme, if any
		$this->theme = Configure::read('theme');

		// Seems that beforeFilter is not called for error pages
		if (!isset ($this->is_logged_in)) {
			$this->is_logged_in = false;
			$this->set('is_logged_in', $this->is_logged_in);
		}

		if (isset($this->RequestHandler)) {
			$this->set('is_mobile', $this->RequestHandler->isMobile());
		} else {
			$this->set('is_mobile', false);
		}

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
			$this->Session->delete('Zuluru');
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
			// Check what actions anyone (logged on or not) is allowed in this controller.
			$allowed = $this->publicActions();

			// An empty array here means the controller has *no* public actions, but an empty
			// array passed to Auth->allow means *everything* is public.
			if (!empty($allowed)) {
				$this->Auth->allow($allowed);
			}
		}

		// Other authentication is handled through the isAuthorized function of
		// the individual controllers.
		$this->Auth->authorize = 'controller';
	}

	// By default, nothing is public. Any controller with special permissions
	// must override this function.
	function publicActions() {
		return null;
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

		$divisions = $this->Session->read('Zuluru.Divisions');
		$teams = Set::extract ('/Team/id', $divisions);
		$this->effective_coordinator = (in_array ($team_id, $teams) && !$on_team);
		$this->set('is_effective_coordinator', $this->effective_coordinator);
	}

	/**
	 * Put basic items on the menu, some based on configuration settings.
	 * Other items like specific teams and divisions are added elsewhere.
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
				$this->_addMenuItem ('Preregistrations', array('controller' => 'preregistrations', 'action' => 'index'), 'Registration');
				$this->_addMenuItem ('Add', array('controller' => 'preregistrations', 'action' => 'add'), array('Registration', 'Preregistrations'));
				$this->_addMenuItem ('Unpaid', array('controller' => 'registrations', 'action' => 'unpaid'), 'Registration');
				$this->_addMenuItem ('Report', array('controller' => 'registrations', 'action' => 'report'), 'Registration');
				$this->_addMenuItem ('Create event', array('controller' => 'events', 'action' => 'add'), 'Registration');
				$this->_addMenuItem ('Questionnaires', array('controller' => 'questionnaires', 'action' => 'index'), 'Registration');
				$this->_addMenuItem ('Questions', array('controller' => 'questions', 'action' => 'index'), array('Registration', 'Questionnaires'));
				$this->_addMenuItem ('Deactivated', array('controller' => 'questionnaires', 'action' => 'deactivated'), array('Registration', 'Questionnaires'));
				$this->_addMenuItem ('Deactivated', array('controller' => 'questions', 'action' => 'deactivated'), array('Registration', 'Questionnaires', 'Questions'));
			}
		}

		// Add the personal menu items next, so that specific teams and divisions
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

		if ($this->is_logged_in && Configure::read('feature.franchises')) {
			$this->_addMenuItem ('Franchises', array('controller' => 'franchises', 'action' => 'index'), 'Teams');
			$this->_addMenuItem ('Create franchise', array('controller' => 'franchises', 'action' => 'add'), array('Teams', 'Franchises'));
		}

		$this->_addMenuItem ('Leagues', array('controller' => 'leagues', 'action' => 'index'));
		if ($this->is_admin) {
			$this->_addMenuItem ('League summary', array('controller' => 'leagues', 'action' => 'summary'), 'Leagues');
			$this->_addMenuItem ('Create league', array('controller' => 'leagues', 'action' => 'add'), 'Leagues');
		}

		$this->_addMenuItem ('Fields', array('controller' => 'facilities', 'action' => 'index'));
		$this->_addMenuItem ('Map of all fields', array('controller' => 'maps', 'action' => 'index'), 'Fields');
		if ($this->is_admin) {
			$this->_addMenuItem ('Closed facilities', array('controller' => 'facilities', 'action' => 'closed'), 'Fields');
			$this->_addMenuItem ('Create facility', array('controller' => 'facilities', 'action' => 'add'), 'Fields');
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
			$this->_addMenuItem ('Holidays', array('controller' => 'holidays', 'action' => 'index'), 'Settings');

			$this->_addMenuItem ('Statistics');
			$this->_addMenuItem ('Player', array('controller' => 'people', 'action' => 'statistics'), 'Statistics');
			$this->_addMenuItem ('Participation', array('controller' => 'people', 'action' => 'participation'), array('Statistics', 'Player'));
			$this->_addMenuItem ('Retention', array('controller' => 'people', 'action' => 'retention'), array('Statistics', 'Player'));
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
		$session_keys = array('Unpaid', 'Teams', 'TeamIDs', 'OwnedTeams', 'OwnedTeamIDs', 'Divisions', 'DivisionIDs');
		if (!$my_id) {
			foreach ($session_keys as $key) {
				$this->Session->write("Zuluru.$key", array());
			}
			return;
		}

		// Schema changes often break cached session information
		$schema = $this->Session->read('Zuluru.Schema');
		if ($schema != SCHEMA_VERSION) {
			foreach ($session_keys as $key) {
				$this->Session->delete("Zuluru.$key");
			}
			$this->Session->write('Zuluru.Schema', SCHEMA_VERSION);
		}

		$unpaid = $this->Session->read('Zuluru.Unpaid');
		if (empty($unpaid)) {
			if (!isset ($this->Registration)) {
				$this->Registration = ClassRegistry::init ('Registration');
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

		$franchises = $this->Session->read('Zuluru.Franchises');
		if (empty($franchises)) {
			if (!isset ($this->Franchise)) {
				if (!class_exists ('Franchise')) {
					App::import ('Model', 'Franchise');
				}
				$this->Franchise = new Franchise();
			}
			$franchises = $this->Franchise->readByPlayerId ($my_id, true, true);

			$this->Session->write ('Zuluru.Franchises', $franchises);
			$this->Session->write ('Zuluru.FranchiseIDs', Set::extract ('/Franchise/id', $franchises));
		}

		$divisions = $this->Session->read('Zuluru.Divisions');
		if (empty($divisions)) {
			if (!isset ($this->Division)) {
				$this->Division = ClassRegistry::init('Division');
			}
			$divisions = $this->Division->readByPlayerId ($my_id, true, true);

			$this->Session->write ('Zuluru.Divisions', $divisions);
			$this->Session->write ('Zuluru.DivisionIDs', Set::extract ('/Division/id', $divisions));
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
	 * Delete all of the cached session information related to franchises.
	 */
	function _deleteFranchiseSessionData() {
		$this->Session->delete('Zuluru.Franchises');
		$this->Session->delete('Zuluru.FranchiseIDs');
	}

	/**
	 * Put personalized items like specific teams and divisions on the menu.
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

		if (Configure::read('feature.franchises')) {
			$franchises = $this->Session->read('Zuluru.Franchises');
			foreach ($franchises as $franchise) {
				$this->_addFranchiseMenuItems ($franchise);
			}
		}

		$divisions = $this->Session->read('Zuluru.Divisions');
		foreach ($divisions as $division) {
			$this->_addDivisionMenuItems ($division['Division'], $division['League']);
		}
	}

	/**
	 * Add all the links for a team to the menu.
	 */
	function _addTeamMenuItems($team) {
		$is_captain = in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
		$this->_limitOverride($team['Team']['id']);
		$key = "{$team['Team']['name']}::{$team['Team']['id']}";

		$this->_addMenuItem ("{$team['Team']['name']} ({$team['Division']['long_league_name']})", array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), 'Teams', $key);
		$this->_addMenuItem ('Schedule', array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id']), array('Teams', $key));
		$this->_addMenuItem ('Standings', array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Division']['id'], 'team' => $team['Team']['id']), array('Teams', $key));
		if ($team['Team']['track_attendance'] &&
			in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			$this->_addMenuItem ('Attendance', array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['Team']['id']), array('Teams', $key));
		}
		if ($this->is_logged_in && $team['Team']['open_roster'] && $team['Division']['roster_deadline'] >= date('Y-m-d') &&
			!in_array($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs')))
		{
			$this->_addMenuItem ('Join team', array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['Team']['id']), array('Teams', $key));
		}
		if ($this->is_admin || $is_captain) {
			$this->_addMenuItem ('Edit', array('controller' => 'teams', 'action' => 'edit', 'team' => $team['Team']['id']), array('Teams', $key));
			$this->_addMenuItem ('Player emails', array('controller' => 'teams', 'action' => 'emails', 'team' => $team['Team']['id']), array('Teams', $key));
			$this->_addMenuItem ('Delete', array('controller' => 'teams', 'action' => 'delete', 'team' => $team['Team']['id']), array('Teams', $key));
		}
		if ($this->effective_admin ||
			(($is_captain || $this->effective_coordinator) && $team['Division']['roster_deadline'] >= date('Y-m-d')))
		{
			$this->_addMenuItem ('Add player', array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']), array('Teams', $key));
		}
		if ($this->effective_admin) {
			$this->_addMenuItem ('Move', array('controller' => 'teams', 'action' => 'move', 'team' => $team['Team']['id']), array('Teams', $key));
			if ($team['Division']['League']['numeric_sotg'] || $team['Division']['League']['sotg_questions'] != 'none') {
				$this->_addMenuItem ('Spirit', array('controller' => 'teams', 'action' => 'spirit', 'team' => $team['Team']['id']), array('Teams', $key));
			}
		}

		if (array_key_exists('Division', $team)) {
			$this->_addDivisionMenuItems($team['Division'], $team['Division']['League']);
		}
	}

	/**
	 * Add all the links for a franchise to the menu.
	 */
	function _addFranchiseMenuItems($franchise) {
		$this->_addMenuItem ($franchise['Franchise']['name'], array('controller' => 'franchises', 'action' => 'view', 'franchise' => $franchise['Franchise']['id']), array('Teams', 'Franchises'), "{$franchise['Franchise']['name']}::{$franchise['Franchise']['id']}");
		$is_owner = in_array($franchise['Franchise']['id'], $this->Session->read('Zuluru.FranchiseIDs'));
		if ($this->is_admin || $is_owner) {
			$this->_addMenuItem ('Edit', array('controller' => 'franchises', 'action' => 'edit', 'franchise' => $franchise['Franchise']['id']), array('Teams', 'Franchises', "{$franchise['Franchise']['name']}::{$franchise['Franchise']['id']}"));
			$this->_addMenuItem ('Add team', array('controller' => 'franchises', 'action' => 'add_team', 'franchise' => $franchise['Franchise']['id']), array('Teams', 'Franchises', "{$franchise['Franchise']['name']}::{$franchise['Franchise']['id']}"));
			$this->_addMenuItem ('Transfer ownership', array('controller' => 'franchises', 'action' => 'transfer', 'franchise' => $franchise['Franchise']['id']), array('Teams', 'Franchises', "{$franchise['Franchise']['name']}::{$franchise['Franchise']['id']}"));
			$this->_addMenuItem ('Delete', array('controller' => 'franchises', 'action' => 'delete', 'franchise' => $franchise['Franchise']['id']), array('Teams', 'Franchises', "{$franchise['Franchise']['name']}::{$franchise['Franchise']['id']}"));
		}
	}

	/**
	 * Add all the links for a league to the menu.
	 */
	function _addLeagueMenuItems($league) {
		if (!empty($league['Division'])) {
			foreach ($league['Division'] as $division) {
				$this->_addDivisionMenuItems($division, $league['League']);
			}
		}
	}

	/**
	 * Add all the links for a division to the menu.
	 */
	function _addDivisionMenuItems($division, $league) {
		$is_coordinator = in_array($division['id'], $this->Session->read('Zuluru.DivisionIDs'));

		$this->_addMenuItem ($league['name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $league['id']), 'Leagues');
		$path = array('Leagues', $league['name']);
		if (!empty($division['name'])) {
			$this->_addMenuItem ($division['name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']), $path);
			$path[] = $division['name'];
		}
		$this->_addMenuItem ('Schedule', array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['id']), $path);
		$this->_addMenuItem ('Standings', array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id']), $path);
		if ($this->is_logged_in) {
			$this->_addMenuItem ('Scores', array('controller' => 'divisions', 'action' => 'scores', 'division' => $division['id']), $path);
		}
		if ($this->is_admin || $is_coordinator) {
			$this->_addMenuItem ('Add Games', array('controller' => 'schedules', 'action' => 'add', 'division' => $division['id']), array_merge($path, array('Schedule')));
			$this->_addMenuItem ('Approve scores', array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['id']), $path);
			$this->_addMenuItem ('Edit', array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['id']), $path);
			$this->_addMenuItem ('Field distribution', array('controller' => 'divisions', 'action' => 'fields', 'division' => $division['id']), $path);
			$this->_addMenuItem ('Available fields', array('controller' => 'divisions', 'action' => 'slots', 'division' => $division['id']), $path);
			$this->_addMenuItem ('Status report', array('controller' => 'divisions', 'action' => 'status', 'division' => $division['id']), $path);
			$this->_addMenuItem ('Validate ratings', array('controller' => 'divisions', 'action' => 'validate_ratings', 'division' => $division['id']), $path);
			if (Configure::read('scoring.allstars') && $division['allstars'] != 'never') {
				$this->_addMenuItem ('All stars', array('controller' => 'divisions', 'action' => 'allstars', 'division' => $division['id']), $path);
			}
			$this->_addMenuItem ('Captain emails', array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['id']), $path);
			if ($league['numeric_sotg'] || $league['sotg_questions'] != 'none') {
				$this->_addMenuItem ('Spirit Report', array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id']), $path);
				$this->_addMenuItem ('Download', array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id'], 'ext' => 'csv'), array_merge($path, array('Spirit Report')));
			}
		}
		if ($this->is_admin) {
			$this->_addMenuItem ('Add coordinator', array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['id']), $path);
		}

		// Some items are only applicable depending on league configuration
		if (!empty ($division['schedule_type'])) {
			$league_obj = $this->_getComponent ('LeagueType', $division['schedule_type'], $this);
			$league_obj->addMenuItems ($division, $path, $is_coordinator);
		}
	}

	/**
	 * Add all the links for a field to the menu.
	 */
	function _addFieldMenuItems($field) {
		$this->_addMenuItem ($field['Field']['long_name'], array('controller' => 'fields', 'action' => 'view', 'field' => $field['Field']['id']), 'Fields');
		$this->_addMenuItem ('View bookings', array('controller' => 'fields', 'action' => 'bookings', 'field' => $field['Field']['id']), array('Fields', $field['Field']['long_name']));
		if ($this->is_admin) {
			$this->_addMenuItem ('Add Game Slot', array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id']), array('Fields', $field['Field']['long_name']));
			$this->_addMenuItem ('Edit Field', array('controller' => 'fields', 'action' => 'edit', 'field' => $field['Field']['id']), array('Fields', $field['Field']['long_name']));
			$this->_addMenuItem ('Edit Layout', array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id']), array('Fields', $field['Field']['long_name']));
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
	 * @param bool  $unique An indication of whether to try to used cached objects, so
	 *					  multiple objects are not created unnecessarily.
	 * @param mixed $config Optional configuration data to be used to initially configure
	 *					  the object.
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
			trigger_error("cannot find the class $full_class", E_USER_ERROR);
		}

		if ($unique) {
			$obj = new $full_class($controller);
			$obj->name = $full_class;
			if ($config) {
				$obj->configure ($config);
			}
			return $obj;
		}

		if (!array_key_exists ($specific, $component_cache[$type])) {
			$component_cache[$type][$specific] =& new $full_class($controller);
			$component_cache[$type][$specific]->name = $full_class;
		} else {
			// We might have initialized this without a controller (from a model), in
			// which case we'll update the controller now to the current one.
			if (empty($component_cache[$type][$specific]->_controller)) {
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

		// If there are no recipients, don't even bother trying to send
		if (empty($opts['to']) && empty($opts['cc']) && empty($opts['bcc'])) {
			return (array_key_exists('ignore_empty_address', $opts) && $opts['ignore_empty_address']);
		}

		// Get ready and send it
		$email->initialize ($this, $opts);
		$success = $email->send();

		if (! empty ($email->smtpError))
		{
			$this->log("smtp-errors: {$email->smtpError}");
		}

		return $success;
	}

	function _extractEmails($input, $array = false) {
		if (is_array ($input)) {
			$emails = array_filter (Set::extract ('/email_formatted', $input));
			if (empty ($emails)) {
				$emails = array_filter (Set::extract ('/Person/email_formatted', $input));
			}
			if (empty ($emails)) {
				$emails = array_filter (Set::extract ('/email', $input));
			}
			if (empty ($emails)) {
				$emails = array_filter (Set::extract ('/Person/email', $input));
			}
			if (empty ($emails)) {
				$model = Configure::read('security.auth_model');
				$emails = array_filter (Set::extract ("/$model/email_formatted", $input));
			}
			if (count ($emails) >= 1 && !$array) {
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
				$model->contain();
				$data = $model->read(null, $find);
				$data = $data[$model->alias];
			} else {
				if ($find === null) {
					$find = array(
						'contain' => array(),
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
			$this->Session->setFlash(__('You must sign the waiver before proceeding.', true), 'default', array('class' => 'info'));
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

	// TODO: Move this to a component? Leagues and Teams need it, but nothing else
	function _getAffiliateId ($division, $team) {
		if (!isset ($this->Team)) {
			$this->Team = ClassRegistry::init ('Team');
		}

		// Find the affiliated league team
		$franchises = Set::extract ('/Franchise/id', $team);
		$this->Team->Franchise->contain(array(
			'Team' => array('conditions' => array('Team.division_id' => $division['season_divisions'])),
		));
		$affiliates = $this->Team->Franchise->find('all', array('conditions' => array(
			'Franchise.id' => $franchises,
		)));
		$affiliate_id = null;
		foreach ($affiliates as $affiliate) {
			switch (count($affiliate['Team'])) {
				case 0:
					break;

				case 1:
					if ($affiliate_id !== null) {
						return null;
					}
					$affiliate_id = $affiliate['Team'][0]['id'];
					break;

				default:
					return null;
			}
		}
		return $affiliate_id;
	}
}
?>
