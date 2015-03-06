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
	var $components = array('Session', 'Auth', 'Cookie', 'RequestHandler', 'UserCache');
	var $uses = array('Person', 'Configuration');
	var $helpers = array(
			'Session', 'UserCache',
			'Html', 'ZuluruHtml', 'Form', 'ZuluruForm', 'Js' => array('ZuluruJquery'),
			'Time', 'ZuluruTime', 'Number', 'Text');
	var $view = 'Theme';

	var $is_admin = false;
	var $is_manager = false;
	var $is_official = false;
	var $is_volunteer = false;
	var $is_coach = false;
	var $is_player = false;
	var $is_child = false;
	var $is_logged_in = false;
	var $is_visitor = true;

	var $menu_items = array();

	function beforeFilter() {
		// Set up our caches for rarely-changed data
		Cache::config('file', array('engine' => 'File', 'path' => CACHE . DS . 'queries'));
		Cache::config('long_term', array('engine' => 'File', 'path' => CACHE . DS . 'queries', 'duration' => YEAR));

		parent::beforeFilter();

		$this->_setLanguage();

		// Use the configured model for handling hashing of passwords, and configure
		// the Auth field names using it
		$this->Auth->userModel = Configure::read('security.auth_model');
		$this->Auth->authenticate = ClassRegistry::init($this->Auth->userModel);
		$this->Auth->sessionKey = 'Auth.User';
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
		if (isset($this->Configuration) && !empty($this->Configuration->table)) {
			$this->Configuration->load($this->UserCache->currentId());
			if (Configure::read('feature.affiliates')) {
				$affiliates = $this->_applicableAffiliateIDs();
				if (count($affiliates) == 1) {
					$this->Configuration->loadAffiliate(reset($affiliates));
				}
			}
		}
		if (Configure::read('feature.items_per_page')) {
			$this->paginate['limit'] = Configure::read('feature.items_per_page');
		}

		// Set the theme, if any. Must be done before processing, in order for the theme to affect emails.
		$this->theme = Configure::read('theme');

		// Requests made through requestAction don't need any of the rest of this
		if (!empty($this->params['requested'])) {
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

		if (!$this->RequestHandler->isAjax()) {
			if ($this->_arg('return') && !$this->Session->check('Navigation.redirect')) {
				// If there's a return requested, and nothing already saved to return to, remember the referrer
				$url = $this->referer(null, true);
				$matched = preg_match('#(.*)/act_as:[0-9]*(.*)#', $url, $matches);
				if ($matched) {
					$url = "{$matches[1]}{$matches[3]}";
				}
				$this->Session->write('Navigation.redirect', $url);
			} else if (!$this->_arg('return') && $this->Session->check('Navigation.redirect') && empty($this->data)) {
				// If there's no return requested, and something saved, and this is not a POST, then the operation was aborted and we
				// don't want to remember this any more
				$this->Session->delete('Navigation.redirect');
			}
		}

		// Check if we need to redirect logged-in users for some required step first
		// We will allow them to see help or logout. Or get the leagues list, as that's where some things redirect to.
		$free = $this->freeActions();
		if ($this->is_logged_in && !in_array($this->action, $free)) {
			$email = $this->UserCache->read('Person.email');
			if (($this->name != 'People' || $this->action != 'edit') && empty ($email) && $this->UserCache->read('Person.user_id')) {
				$this->Session->setFlash(__('Last time we tried to contact you, your email bounced. We require a valid email address as part of your profile. You must update it before proceeding.', true), 'default', array('class' => 'warning'));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			if (($this->name != 'People' || $this->action != 'edit') && $this->UserCache->read('Person.complete') == 0) {
				$this->Session->setFlash(__('Your player profile is incomplete. You must update it before proceeding.', true), 'default', array('class' => 'warning'));
				$this->redirect (array('controller' => 'people', 'action' => 'edit'));
			}

			// Force response to roster requests, if enabled
			if (Configure::read('feature.force_roster_request')) {
				$teams = Set::extract ('/TeamsPerson[status=' . ROSTER_INVITED . ']/..', $this->UserCache->read('Teams'));
				$response_required = array();
				foreach ($teams as $team) {
					// Only force responses to leagues that have started play, but the roster deadline hasn't passed
					if ($team['Division']['open'] < date('Y-m-d') && !Division::rosterDeadlinePassed($team['Division'])) {
						$response_required[] = $team['Team']['id'];
					}
				}
				if (!empty ($response_required) && $this->name != 'Settings' &&
					// We will let people look at information about teams that they've been invited to
					($this->name != 'Teams' || !in_array ($this->_arg('team'), $response_required)))
				{
					$this->Session->setFlash(__('You have been invited to join a team, and must either accept or decline this invitation before proceeding. Before deciding, you have the ability to look at this team\'s roster, schedule, etc.', true), 'default', array('class' => 'info'));
					$this->redirect (array('controller' => 'teams', 'action' => 'view', 'team' => reset($response_required)));
				}
			}
		}

		$this->_initMenu();
	}

	function _setLanguage() {
		$this->_findLanguage();
		$i18n =& I18n::getInstance();
		$this->Session->write('Config.language', $i18n->l10n->lang);
		Configure::write('Config.language', $i18n->l10n->lang);
		Configure::write('Config.language_name', $i18n->l10n->language);
		Configure::Load('features');
		Configure::Load('options');
	}

	function _findLanguage() {
		$i18n =& I18n::getInstance();

		$translations = Cache::read('available_translations');
		$translation_strings = Cache::read('available_translation_strings');
		if (!$translations || !$translation_strings) {
			$translations = array('en' => 'English');
			$translation_strings = array("en: 'English'");
			$dir = opendir(APP . 'locale');
			if ($dir) {
				while (false !== ($entry = readdir($dir))) {
					if (array_key_exists($entry, $i18n->l10n->__l10nMap) && file_exists(APP . 'locale' . DS . $entry . DS . 'LC_MESSAGES' . DS . 'default.po')) {
						$code = $i18n->l10n->__l10nMap[$entry];
						$translations[$code] = $i18n->l10n->__l10nCatalog[$code]['language'];
						$translation_strings[] = "$code: '{$i18n->l10n->__l10nCatalog[$code]['language']}'";
					}
				}
			}
			Cache::write('available_translations', $translations);
			Cache::write('available_translation_strings', $translation_strings);
		}
		Configure::write('available_translations', $translations);
		Configure::write('available_translation_strings', implode(', ', $translation_strings));

		$language = Configure::read('personal.language');
		if (!empty($language)) {
			$i18n->l10n->__setLanguage($language);
			return;
		}

		if ($this->Session->check('Config.language')) {
			$i18n->l10n->__setLanguage($this->Session->read('Config.language'));
			return;
		}

		$langs = array();

		// From http://www.thefutureoftheweb.com/blog/use-accept-language-header
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			// break up string into pieces (languages and q factors)
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

			if (count($lang_parse[1])) {
				// create a list like "en" => 0.8
				$langs = array_combine($lang_parse[1], $lang_parse[4]);
				
				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val) {
					if ($val === '') $langs[$lang] = 1;
				}

				// sort list based on value	
				arsort($langs, SORT_NUMERIC);
			}
		}

		// See if we have a file that matches something the user wants
		foreach (array_keys($langs) as $lang) {
			$i18n->l10n->__setLanguage($lang);
			foreach ($i18n->l10n->languagePath as $path) {
				if ($path == 'eng' || file_exists(APP . 'locale' . DS . low(Inflector::slug($path)) . DS . 'LC_MESSAGES' . DS . 'default.po')) {
					return;
				}
			}
		}

		// Use the site's default language, if there is one
		if (Configure::read('site.default_language')) {
			$i18n->l10n->__setLanguage(Configure::read('site.default_language'));
			return;
		}

		// Last ditch default to English
		$i18n->l10n->__setLanguage('eng');
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

	function redirect($url = null, $next = null) {
		// If there's a referer saved, we always go back there
		if ($this->Session->check('Navigation.redirect')) {
			$saved = Router::normalize($this->Session->read('Navigation.redirect'));
			$this->Session->delete('Navigation.redirect');
		}

		if ($next && !is_numeric($next)) {
			$this->Session->write('Navigation.redirect', $next);
		}

		if (isset($saved)) {
			parent::redirect($saved);
		}

		// If there was no referer saved, we might not want to redirect
		if ($url !== null) {
			if ($next && is_numeric($next)) {
				parent::redirect(Router::normalize($url), $next);
			} else {
				parent::redirect(Router::normalize($url));
			}
		}
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
	function _loadGroupOptions($force_players = false) {
		if ($force_players) {
			$conditions = array('OR' => array(
					// We always want to include players, even if they aren't a valid "create account" group.
					'id' => GROUP_PLAYER,
					'active' => true,
			));
		} else {
			$conditions = array('active' => true);
		}
		if (!$this->is_admin) {
			if ($this->is_manager) {
				$conditions['level <='] = 5;
			} else {
				$conditions['level <'] = 5;
			}
		}
		$groups = $this->Group->find('all', array(
			'conditions' => $conditions,
			'order' => array('Group.level', 'Group.id'),
		));
		$group_list = array();
		foreach ($groups as $group) {
			if (!empty($group['Group']['description'])) {
				$group_list[$group['Group']['id']] = "{$group['Group']['name']}: {$group['Group']['description']}";
			} else {
				$group_list[$group['Group']['id']] = $group['Group']['name'];
			}
		}
		$this->set('groups', $group_list);
		return $group_list;
	}

	/**
	 * Read and set variables for the database-based affiliate options.
	 */
	function _loadAffiliateOptions() {
		$affiliates = $this->Person->Affiliate->find('all', array(
				'conditions' => array(
					'active' => true,
					'NOT' => array('id' => $this->UserCache->read('ManagedAffiliateIDs')),
				),
				'contain' => array(),
		));
		$affiliates = Set::combine($affiliates, '{n}.Affiliate.id', '{n}.Affiliate.name');

		$this->set('affiliates', $affiliates);
	}

	/**
	 * Basic check for authorization, based solely on the person's login group.
	 * Set some "is_" variables for the views to use (is_admin, is_player, etc.).
	 *
	 * @access public
	 */
	function _setPermissions() {
		$this->is_admin = $this->is_manager = $this->is_official = $this->is_volunteer = $this->is_coach = $this->is_player = $this->is_child = $this->is_logged_in = false;
		$this->is_visitor = true;
		$auth =& $this->Auth->authenticate;
		$user = $this->Auth->user();

		// If the user has no session but does have "remember me" login information
		// saved in a cookie, we want to redirect to the login page, which will
		// just log them in automatically and send them right back here. If we
		// don't do that, then their permissions aren't set up correctly, and menus
		// and views will be wrong.
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
		if (empty($this->params['requested']) && (!$user || $login->expired())) {
			// If there is a redirect requested, logout will erase it, so remember it!
			if ($this->Session->check('Auth.redirect')) {
				$redirect = $this->Session->read('Auth.redirect');
			}

			$this->Session->delete('Zuluru');
			$this->Auth->logout();
			$this->UserCache->initializeData();
			$login->login();
			$user = $this->Auth->user();

			if (isset($redirect)) {
				$this->Session->write('Auth.redirect', $redirect);
			}
		}

		// Do we already have the corresponding person record?
		// If not read it; if it doesn't even exist, create it.
		if ($user && !$this->UserCache->currentId()) {
			$person = $auth->Person->find('first', array(
					'contain' => false,
					'conditions' => array(
						'user_id' => $user[$auth->alias][$auth->primaryKey],
					),
			));
			if ($person) {
				$this->Session->write("{$this->Auth->sessionKey}.zuluru_person_id", $person['Person']['id']);
			} else {
				if ($auth->Person->create_person_record($user[$auth->alias], $auth->nameField)) {
					$this->Session->write("{$this->Auth->sessionKey}.zuluru_person_id", $auth->Person->id);
				} else {
					// TODO: What to do when writing this record fails?
				}
			}
		}

		$groups = $this->UserCache->read('Groups');
		$real_groups = $this->UserCache->read('Groups', $this->UserCache->realId());
		$real_group_levels = Set::extract('/level', $real_groups);
		if ($this->UserCache->read('Person.status', $this->UserCache->realId()) == 'active') {
			// Approved accounts are granted permissions up to level 1,
			// since they can just add that group to themselves anyway.
			$real_group_levels[] = 1;
		}
		if (empty($real_group_levels)) {
			$max_level = 0;
		} else {
			$max_level = max($real_group_levels);
		}
		foreach ($groups as $group) {
			if ($this->UserCache->currentId() != $this->UserCache->realId()) {
				// Don't give people enhanced access just because the person they are acting as has it
				if ($group['level'] > $max_level) {
					continue;
				}
			}

			// TODO: Eliminate all these is_ variables and use database-driven permissions
			switch ($group['name']) {
				case 'Administrator':
					$this->is_admin = $this->is_manager = true;
					break;

				case 'Manager':
					$this->is_manager = true;
					break;

				case 'Official':
					$this->is_official = true;
					break;

				case 'Volunteer':
					$this->is_volunteer = true;
					break;

				case 'Coach':
					$this->is_coach = true;
					break;

				case 'Player':
					$this->is_player = true;
					break;
			}
		}
		if ($this->UserCache->currentId()) {
			$this->is_logged_in = true;
			$this->is_visitor = false;
			$this->is_child = $this->_isChild($this->UserCache->read('Person.birthdate'));
		}

		// Set these in convenient locations for views to use
		foreach (array('is_admin', 'is_manager', 'is_official', 'is_volunteer', 'is_coach', 'is_player', 'is_child', 'is_logged_in', 'is_visitor') as $role) {
			$this->set($role, $this->$role);
		}
		$this->set('my_id', $this->UserCache->currentId());

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
		if ($this->name == 'Pages') {
			return array('display');
		}
		return null;
	}

	// Some actions must always be allowed regardless of any redirect that we might want.
	function freeActions() {
		return array();
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
		$on_team = in_array($team_id, $this->UserCache->read('TeamIDs'));
		if ($on_team) {
			$this->effective_admin = $this->effective_coordinator = false;
		} else {
			$this->effective_admin = $this->is_admin;

			if ($this->is_manager) {
				if (!isset ($this->Team)) {
					$this->Team = ClassRegistry::init ('Team');
				}
				$division = $this->Team->field('division_id', array('id' => $team_id));
				$league = $this->Team->Division->field('league_id', array('id' => $division));
				$affiliate = $this->Team->Division->League->field('affiliate_id', array('id' => $league));
				$this->effective_coordinator = in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'));
			} else {
				$divisions = $this->UserCache->read('Divisions');
				$teams = Set::extract ('/Team/id', $divisions);
				$this->effective_coordinator = in_array($team_id, $teams);
			}
		}

		$this->set('is_effective_admin', $this->effective_admin);
		$this->set('is_effective_coordinator', $this->effective_coordinator);
	}

	// Various ways to get the list of affiliates to show
	function _applicableAffiliates($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return array(1 => Configure::read('organization.name'));
		}

		$affiliate_model = ClassRegistry::init ('Affiliate');

		// If there's something in the URL, perhaps only use that
		$affiliate = $this->_arg('affiliate');
		if ($affiliate === null) {
			// If the user has selected a specific affiliate to view, perhaps only use that
			$affiliate = $this->Session->read('Zuluru.CurrentAffiliate');
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || $this->is_admin ||
				($this->is_manager && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs')))
			)
			{
				return $affiliate_model->find('list', array(
					'conditions' => array('Affiliate.id' => $affiliate),
				));
			}
		}

		// Managers may get only their list of managed affiliates
		if (!$this->is_admin && $this->is_manager && $admin_only) {
			$affiliates = $this->UserCache->read('ManagedAffiliates');
			$affiliates = Set::combine($affiliates, '{n}.Affiliate.id', '{n}.Affiliate.name');
			ksort($affiliates);
			return $affiliates;
		}

		// Non-admins get their current list of "subscribed" affiliates
		if ($this->is_logged_in && !$this->is_admin) {
			$affiliates = $this->UserCache->read('Affiliates');
			if (!empty($affiliates)) {
				$affiliates = Set::combine($affiliates, '{n}.Affiliate.id', '{n}.Affiliate.name');
				ksort($affiliates);
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		return $affiliate_model->find('list', array(
				'conditions' => array('active' => true),
				'order' => 'name',
		));
	}

	// Various ways to get the list of affiliates to query
	function _applicableAffiliateIDs($admin_only = false) {
		if (!Configure::read('feature.affiliates')) {
			return array(1);
		}

		// If there's something in the URL, perhaps only use that
		$affiliate = $this->_arg('affiliate');
		if ($affiliate === null) {
			// If the user has selected a specific affiliate to view, perhaps only use that
			$affiliate = $this->Session->read('Zuluru.CurrentAffiliate');
		}

		if ($affiliate !== null) {
			// We only allow overrides through the URL or session if:
			// - this is not an admin-only page OR
			// - the current user is an admin OR
			// - the current user is a manager of that affiliate
			if (!$admin_only || $this->is_admin ||
				($this->is_manager && in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs')))
			)
			{
				return array($affiliate);
			}
		}

		// Managers may get only their list of managed affiliates
		if (!$this->is_admin && $this->is_manager && $admin_only) {
			return $this->UserCache->read('ManagedAffiliateIDs');
		}

		// Non-admins get their current list of selected affiliates
		if ($this->is_logged_in && !$this->is_admin) {
			$affiliates = $this->UserCache->read('AffiliateIDs');
			if (!empty($affiliates)) {
				return $affiliates;
			}
		}

		// Anyone not logged in, and admins, get the full list
		$affiliate_model = ClassRegistry::init ('Affiliate');
		return array_keys($affiliate_model->find('list', array(
				'conditions' => array('active' => true),
		)));
	}

	/**
	 * Put basic items on the menu, some based on configuration settings.
	 * Other items like specific teams and divisions are added elsewhere.
	 */
	function _initMenu()
	{
		// Initialize the menu
		$this->menu_items = array();

		if ($this->is_manager) {
			$affiliates = $this->_applicableAffiliates(true);
		}
		$groups = $this->UserCache->read('GroupIDs');

		if ($this->is_logged_in) {
			$this->_addMenuItem(__('Home', true), array('controller' => 'all', 'action' => 'splash'));
			$this->_addMenuItem(__('My Profile', true), array('controller' => 'people', 'action' => 'view'));
			$this->_addMenuItem(__('View', true), array('controller' => 'people', 'action' => 'view'), __('My Profile', true));
			$this->_addMenuItem(__('Edit', true), array('controller' => 'people', 'action' => 'edit'), __('My Profile', true));
			$this->_addMenuItem(__('Preferences', true), array('controller' => 'people', 'action' => 'preferences'), __('My Profile', true));
			if (in_array(GROUP_PARENT, $groups)) {
				$this->_addMenuItem(__('Add new child', true), array('controller' => 'people', 'action' => 'add_relative'), __('My Profile', true));
			}
			$this->_addMenuItem(__('Link to relative', true), array('controller' => 'people', 'action' => 'link_relative'), __('My Profile', true));
			$this->_addMenuItem(__('Waiver history', true), array('controller' => 'people', 'action' => 'waivers'), __('My Profile', true));
			$this->_addMenuItem(__('Change password', true), array('controller' => 'users', 'action' => 'change_password'), __('My Profile', true));
			if (Configure::read('feature.photos')) {
				$this->_addMenuItem(__('Upload photo', true), array('controller' => 'people', 'action' => 'photo_upload'), __('My Profile', true));
			}
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload document', true), array('controller' => 'people', 'action' => 'document_upload'), __('My Profile', true));
			}
		}

		// Depending on the account type, and the available registrations, this may not be available
		// Admins and managers, anyone not logged in, and anyone with any registration history always get it
		$show_registration = false;
		if (Configure::read('feature.registration')) {
			$show_registration = $this->is_admin || $this->is_manager || !$this->is_logged_in;
			$registrations = $this->UserCache->read('Registrations');
			if (!$show_registration && !empty($registrations)) {
				$show_registration = true;
			}

			// Parents and players always get it
			if (!$show_registration) {
				$always = array_intersect($groups, array(GROUP_PLAYER,GROUP_PARENT));
				if (!empty($always)) {
					$show_registration = true;
				}
			}

			// If there are any generic events available, everyone gets it
			if (!$show_registration) {
				$affiliates = $this->_applicableAffiliateIDs();
				if ($this->Person->Registration->Event->find('count', array(
						'contain' => 'EventType',
						'conditions' => array(
							'EventType.type' => 'generic',
							"Event.open < DATE_ADD(CURDATE(), INTERVAL 30 DAY)",
							"Event.close > CURDATE()",
							'Event.affiliate_id' => $affiliates,
						),
				)) > 0)
				{
					$show_registration = true;
				}
			}

			// If there are any team events available, coaches get it
			if (!$show_registration && in_array(GROUP_COACH, $groups)) {
				if ($this->Person->Registration->Event->find('count', array(
						'contain' => 'EventType',
						'conditions' => array(
							'EventType.type' => 'team',
							"Event.open < DATE_ADD(CURDATE(), INTERVAL 30 DAY)",
							"Event.close > CURDATE()",
							'Event.affiliate_id' => $affiliates,
						),
				)) > 0)
				{
					$show_registration = true;
				}
			}

			if ($show_registration) {
				$this->_addMenuItem(__('Registration', true), array('controller' => 'events', 'action' => 'wizard'));
				$this->_addMenuItem(__('Wizard', true), array('controller' => 'events', 'action' => 'wizard'), __('Registration', true));
				$this->_addMenuItem(__('All events', true), array('controller' => 'events', 'action' => 'index'), __('Registration', true));
				if ($this->is_logged_in && !empty($registrations)) {
					$this->_addMenuItem(__('My history', true), array('controller' => 'people', 'action' => 'registrations'), __('Registration', true));
				}

				$unpaid = $this->UserCache->read('RegistrationsCanPay');
				if (!empty ($unpaid)) {
					$this->_addMenuItem(__('Checkout', true), array('controller' => 'registrations', 'action' => 'checkout'), __('Registration', true));
				}
			}

			if ($this->is_admin || $this->is_manager) {
				$this->_addMenuItem(__('Preregistrations', true), array('controller' => 'preregistrations', 'action' => 'index'), __('Registration', true));
				$this->_addMenuItem(__('List', true), array('controller' => 'preregistrations', 'action' => 'index'), array(__('Registration', true), __('Preregistrations', true)));
				$this->_addMenuItem(__('Add', true), array('controller' => 'preregistrations', 'action' => 'add'), array(__('Registration', true), __('Preregistrations', true)));
				$this->_addMenuItem(__('Unpaid', true), array('controller' => 'registrations', 'action' => 'unpaid'), __('Registration', true));
				$this->_addMenuItem(__('Credits', true), array('controller' => 'registrations', 'action' => 'credits'), __('Registration', true));
				$this->_addMenuItem(__('Report', true), array('controller' => 'registrations', 'action' => 'report'), __('Registration', true));
				$this->_addMenuItem(sprintf(__('Create %s', true), __('event', true)), array('controller' => 'events', 'action' => 'add'), __('Registration', true));
				$this->_addMenuItem(__('Questionnaires', true), array('controller' => 'questionnaires', 'action' => 'index'), __('Registration', true));
				$this->_addMenuItem(__('List', true), array('controller' => 'questionnaires', 'action' => 'index'), array(__('Registration', true), __('Questionnaires', true)));
				$this->_addMenuItem(__('Questions', true), array('controller' => 'questions', 'action' => 'index'), array(__('Registration', true), __('Questionnaires', true)));
				$this->_addMenuItem(__('Deactivated', true), array('controller' => 'questionnaires', 'action' => 'deactivated'), array(__('Registration', true), __('Questionnaires', true)));
				$this->_addMenuItem(__('List', true), array('controller' => 'questions', 'action' => 'index'), array(__('Registration', true), __('Questionnaires', true), __('Questions', true)));
				$this->_addMenuItem(__('Deactivated', true), array('controller' => 'questions', 'action' => 'deactivated'), array(__('Registration', true), __('Questionnaires', true), __('Questions', true)));
			}
		}

		// Add the personal menu items next, so that specific teams and divisions
		// are the first sub-menus in the Teams and Leagues menus, rather than
		// the generic operations.
		if ($this->is_logged_in) {
			$this->_initPersonalMenu();
			$relatives = $this->UserCache->allActAs(true, 'first_name');
			foreach ($relatives as $id => $name) {
				$this->_initPersonalMenu($id, $name);
			}
		}

		if ($this->is_logged_in) {
			$this->_addMenuItem(__('Teams', true), array('controller' => 'teams', 'action' => 'index'));
			$this->_addMenuItem(__('List', true), array('controller' => 'teams', 'action' => 'index'), __('Teams', true));
			// If registrations are enabled, it takes care of team creation
			if ($this->is_admin || $this->is_manager || !Configure::read('feature.registration')) {
				$this->_addMenuItem(sprintf(__('Create %s', true), __('team', true)), array('controller' => 'teams', 'action' => 'add'), __('Teams', true));
			}
			if ($this->is_admin || $this->is_manager) {
				$this->_addMenuItem(__('Unassigned teams', true), array('controller' => 'teams', 'action' => 'unassigned'), __('Teams', true));
			}
		}

		if ($this->is_logged_in && Configure::read('feature.franchises')) {
			$this->_addMenuItem(__('Franchises', true), array('controller' => 'franchises', 'action' => 'index'), __('Teams', true));
			$this->_addMenuItem(__('List', true), array('controller' => 'franchises', 'action' => 'index'), array(__('Teams', true), __('Franchises', true)));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('franchise', true)), array('controller' => 'franchises', 'action' => 'add'), array(__('Teams', true), __('Franchises', true)));
		}

		$this->_addMenuItem(__('Leagues', true), array('controller' => 'leagues', 'action' => 'index'));
		$this->_addMenuItem(__('List', true), array('controller' => 'leagues', 'action' => 'index'), __('Leagues', true));
		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('League summary', true), array('controller' => 'leagues', 'action' => 'summary'), __('Leagues', true));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('league', true)), array('controller' => 'leagues', 'action' => 'add'), __('Leagues', true));
		}

		$this->_addMenuItem(__(Configure::read('ui.fields_cap'), true), array('controller' => 'facilities', 'action' => 'index'));
		$this->_addMenuItem(__('List', true), array('controller' => 'facilities', 'action' => 'index'), __(Configure::read('ui.fields_cap'), true));
		$this->_addMenuItem(sprintf(__('Map of all %s', true), __(Configure::read('ui.fields'), true)), array('controller' => 'maps', 'action' => 'index'), __(Configure::read('ui.fields_cap'), true), null, array('target' => 'map'));
		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('Regions', true), array('controller' => 'regions', 'action' => 'index'), __(Configure::read('ui.fields_cap'), true));
			$this->_addMenuItem(__('List', true), array('controller' => 'regions', 'action' => 'index'), array(__(Configure::read('ui.fields_cap'), true), __('Regions', true)));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('region', true)), array('controller' => 'regions', 'action' => 'add'), array(__(Configure::read('ui.fields_cap'), true), __('Regions', true)));

			$this->_addMenuItem(__('Closed facilities', true), array('controller' => 'facilities', 'action' => 'closed'), __(Configure::read('ui.fields_cap'), true));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('facility', true)), array('controller' => 'facilities', 'action' => 'add'), __(Configure::read('ui.fields_cap'), true));
			if (!Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Add bulk gameslots', true), array('controller' => 'game_slots', 'action' => 'add'), __(Configure::read('ui.fields_cap'), true));
			} else if (count($affiliates) == 1) {
				$this->_addMenuItem(__('Add bulk gameslots', true), array('controller' => 'game_slots', 'action' => 'add', 'affiliate' => reset(array_keys($affiliates))), __(Configure::read('ui.fields_cap'), true));
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__($name, true), array('controller' => 'game_slots', 'action' => 'add', 'affiliate' => $affiliate), array(__(Configure::read('ui.fields_cap'), true), __('Add bulk gameslots', true)));
				}
			}
		}

		if ($this->is_logged_in) {
			$this->_addMenuItem(__('Search', true), array('controller' => 'people', 'action' => 'search'), __('People', true));
			if (Configure::read('feature.badges')) {
				$this->_addMenuItem(__('Badges', true), array('controller' => 'badges', 'action' => 'index'), __('People', true));
				$this->_addMenuItem(__('Nominate', true), array('controller' => 'people', 'action' => 'nominate'), array(__('People', true), __('Badges', true)));
				if ($this->is_admin || $this->is_manager) {
					$new = $this->Person->Badge->find ('count', array(
						'joins' => array(
							array(
								'table' => "{$this->Person->tablePrefix}badges_people",
								'alias' => 'BadgesPerson',
								'type' => 'LEFT',
								'foreignKey' => false,
								'conditions' => 'BadgesPerson.badge_id = Badge.id',
							),
						),
						'conditions' => array(
							'BadgesPerson.approved' => false,
							'Badge.affiliate_id' => array_keys($affiliates),
						),
					));
					if ($new > 0) {
						$this->set('new_nominations', $new);
						$this->_addMenuItem(sprintf(__('Approve nominations (%d pending)', true), $new), array('controller' => 'people', 'action' => 'approve_badges'), array(__('People', true), __('Badges', true)));
					}
					$this->_addMenuItem(__('Deactivated', true), array('controller' => 'badges', 'action' => 'deactivated'), array(__('People', true), __('Badges', true)));
				}
			}
		}

		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('By name', true), array('controller' => 'people', 'action' => 'search'), array(__('People', true), __('Search', true)));
			$this->_addMenuItem(__('By rule', true), array('controller' => 'people', 'action' => 'rule_search'), array(__('People', true), __('Search', true)));
			$this->_addMenuItem(__('By league', true), array('controller' => 'people', 'action' => 'league_search'), array(__('People', true), __('Search', true)));
			$this->_addMenuItem(__('Inactive', true), array('controller' => 'people', 'action' => 'inactive_search'), array(__('People', true), __('Search', true)));

			if (!isset ($this->Person)) {
				$this->Person = ClassRegistry::init ('Person');
			}
			$new = $this->Person->find ('count', array(
				'contain' => array(),
				'joins' => array(
					array(
						'table' => "{$this->Person->tablePrefix}affiliates_people",
						'alias' => 'AffiliatePerson',
						'type' => 'LEFT',
						'foreignKey' => false,
						'conditions' => 'AffiliatePerson.person_id = Person.id',
					),
				),
				'conditions' => array(
					'Person.status' => 'new',
					'Person.complete' => 1,
					'AffiliatePerson.affiliate_id' => array_keys($affiliates),
				),
			));
			if ($new > 0) {
				$this->set('new_accounts', $new);
				$this->_addMenuItem(sprintf(__('Approve new accounts (%d pending)', true), $new), array('controller' => 'people', 'action' => 'list_new'), __('People', true));
			}

			if (!isset ($this->Person->Upload) &&
				(Configure::read('feature.photos') || Configure::read('feature.documents')))
			{
				$this->Person->Upload = ClassRegistry::init ('Upload');
			}

			if (Configure::read('feature.photos') && Configure::read('feature.approve_photos')) {
				$new = $this->Person->Upload->find ('count', array(
					'conditions' => array(
						'approved' => 0,
						'type_id' => null,
					),
				));
				if ($new > 0) {
					$this->set('new_photos', $new);
					$this->_addMenuItem(sprintf(__('Approve new photos (%d pending)', true), $new), array('controller' => 'people', 'action' => 'approve_photos'), __('People', true));
				}
			}

			if (Configure::read('feature.documents')) {
				$new = $this->Person->Upload->find ('count', array(
					'conditions' => array(
						'approved' => 0,
						'type_id !=' => null,
					),
				));
				if ($new > 0) {
					$this->set('new_documents', $new);
					$this->_addMenuItem(sprintf(__('Approve new documents (%d pending)', true), $new), array('controller' => 'people', 'action' => 'approve_documents'), __('People', true));
				}
			}

			$this->_addMenuItem(__('List all', true), array('controller' => 'people', 'action' => 'index'), __('People', true));
			$groups = $this->Person->Group->find('list', array(
				'conditions' => array('OR' => array(
					// We always want to include players, even if they aren't a valid "create account" group.
					'id' => GROUP_PLAYER,
					'active' => true,
				)),
				'order' => array('Group.level', 'Group.id'),
			));
			foreach ($groups as $group => $name) {
				$this->_addMenuItem(__(Inflector::pluralize($name), true), array('controller' => 'people', 'action' => 'index', 'group' => $group), array(__('People', true), 'List all'));
			}
			$this->_addMenuItem(__('Bulk import', true), array('controller' => 'users', 'action' => 'import'), __('People', true));

			$this->_addMenuItem(__('Newsletters', true), array('controller' => 'newsletters', 'action' => 'index'));
			$this->_addMenuItem(__('Upcoming', true), array('controller' => 'newsletters', 'action' => 'index'), __('Newsletters', true));
			$this->_addMenuItem(__('Mailing lists', true), array('controller' => 'mailing_lists', 'action' => 'index'), __('Newsletters', true));
			$this->_addMenuItem(__('List', true), array('controller' => 'mailing_lists', 'action' => 'index'), array(__('Newsletters', true), __('Mailing lists', true)));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('mailing list', true)), array('controller' => 'mailing_lists', 'action' => 'add'), array(__('Newsletters', true), __('Mailing lists', true)));
			$this->_addMenuItem(sprintf(__('Create %s', true), __('newsletter', true)), array('controller' => 'newsletters', 'action' => 'add'), __('Newsletters', true));
			$this->_addMenuItem(__('All newsletters', true), array('controller' => 'newsletters', 'action' => 'past'), __('Newsletters', true));
		}

		if ($this->is_admin) {
			$this->_addMenuItem(__('Organization', true), array('controller' => 'settings', 'action' => 'organization'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('Features', true), array('controller' => 'settings', 'action' => 'feature'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('Email', true), array('controller' => 'settings', 'action' => 'email'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('Team', true), array('controller' => 'settings', 'action' => 'team'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('User', true), array('controller' => 'settings', 'action' => 'user'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('Profile', true), array('controller' => 'settings', 'action' => 'profile'), array(__('Configuration', true), __('Settings', true)));
			$this->_addMenuItem(__('Scoring', true), array('controller' => 'settings', 'action' => 'scoring'), array(__('Configuration', true), __('Settings', true)));
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Registration', true), array('controller' => 'settings', 'action' => 'registration'), array(__('Configuration', true), __('Settings', true)));
				if (Configure::read('registration.online_payments')) {
					$this->_addMenuItem(__('Payment', true), array('controller' => 'settings', 'action' => 'payment'), array(__('Configuration', true), __('Settings', true)));
				}
			}

			if (Configure::read('feature.affiliates')) {
				$this->_addMenuItem(__('Affiliates', true), array('controller' => 'affiliates', 'action' => 'index'), __('Configuration', true));
			}
		}

		if (Configure::read('feature.affiliates') && $this->is_manager) {
			if (count($affiliates) == 1 && !$this->is_admin) {
				$affiliate = reset(array_keys($affiliates));
				$this->_addMenuItem(__('Organization', true), array('controller' => 'settings', 'action' => 'organization', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('Features', true), array('controller' => 'settings', 'action' => 'feature', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('Email', true), array('controller' => 'settings', 'action' => 'email', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('Team', true), array('controller' => 'settings', 'action' => 'team', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('User', true), array('controller' => 'settings', 'action' => 'user', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('Profile', true), array('controller' => 'settings', 'action' => 'profile', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				$this->_addMenuItem(__('Scoring', true), array('controller' => 'settings', 'action' => 'scoring', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
				if (Configure::read('feature.registration')) {
					$this->_addMenuItem(__('Registration', true), array('controller' => 'settings', 'action' => 'registration', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
					if (Configure::read('registration.online_payments')) {
						$this->_addMenuItem(__('Payment', true), array('controller' => 'settings', 'action' => 'payment', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true)));
					}
				}
			} else {
				foreach ($affiliates as $affiliate => $name) {
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'organization', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Organization', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'feature', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Features', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'email', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Email', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'team', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Team', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'user', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('User', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'scoring', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Scoring', true)));
					$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'profile', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Profile', true)));
					if (Configure::read('feature.registration')) {
						$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'registration', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Registration', true)));
						if (Configure::read('registration.online_payments')) {
							$this->_addMenuItem(__($name, true), array('controller' => 'settings', 'action' => 'payment', 'affiliate' => $affiliate), array(__('Configuration', true), __('Settings', true), __('Payment', true)));
						}
					}
				}
			}
		}

		if ($this->is_admin || $this->is_manager || $this->is_official || $this->is_volunteer) {
			if (Configure::read('feature.tasks')) {
				$this->_addMenuItem(__('Tasks', true), array('controller' => 'tasks', 'action' => 'index'));
				$this->_addMenuItem(__('List', true), array('controller' => 'tasks', 'action' => 'index'), __('Tasks', true));
			}
		}

		if ($this->is_admin) {
			$this->_addMenuItem(__('Permissions', true), array('controller' => 'groups', 'action' => 'index'), __('Configuration', true));
		}

		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('Holidays', true), array('controller' => 'holidays', 'action' => 'index'), __('Configuration', true));
			if (Configure::read('feature.documents')) {
				$this->_addMenuItem(__('Upload types', true), array('controller' => 'upload_types', 'action' => 'index'), __('Configuration', true));
			}

			$this->_addMenuItem(__('Waivers', true), array('controller' => 'waivers', 'action' => 'index'), __('Configuration', true));

			if (Configure::read('feature.tasks')) {
				$this->_addMenuItem(__('Categories', true), array('controller' => 'categories', 'action' => 'index'), __('Tasks', true));
				$this->_addMenuItem(__('Download All', true), array('controller' => 'tasks', 'action' => 'index', 'download' => true), __('Tasks', true));
			}

			if (Configure::read('feature.contacts')) {
				$this->_addMenuItem(__('Contacts', true), array('controller' => 'contacts', 'action' => 'index'), __('Configuration', true));
			}

			$this->_addMenuItem(__('Clear cache', true), array('controller' => 'all', 'action' => 'clear_cache'), __('Configuration', true));
		}

		$this->_addMenuItem(__('Help', true), array('controller' => 'help'));
		if (Configure::read('feature.contacts') && $this->is_logged_in) {
			$this->_addMenuItem(__('Contact us', true), array('controller' => 'contacts', 'action' => 'message'), __('Help', true));
		}
		$this->_addMenuItem(__('Help index', true), array('controller' => 'help'), __('Help', true));
		$this->_addMenuItem(__('New users', true), array('controller' => 'help', 'action' => 'guide', 'new_user'), __('Help', true));
		$this->_addMenuItem(__('Advanced users', true), array('controller' => 'help', 'action' => 'guide', 'advanced'), __('Help', true));
		$this->_addMenuItem(__('Coaches/Captains', true), array('controller' => 'help', 'action' => 'guide', 'captain'), __('Help', true));
		if ($this->is_admin || $this->is_manager || $this->UserCache->read('DivisionIDs')) {
			$this->_addMenuItem(__('Coordinators', true), array('controller' => 'help', 'action' => 'guide', 'coordinator'), __('Help', true));
		}
		if ($this->is_admin) {
			$this->_addMenuItem(__('Site setup and configuration', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'setup'), array(__('Help', true), __('Administrators', true)));
		}
		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('Player management', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'players'), array(__('Help', true), __('Administrators', true)));
			$this->_addMenuItem(__('League management', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'leagues'), array(__('Help', true), __('Administrators', true)));
			$this->_addMenuItem(sprintf(__('%s management', true), __(Configure::read('ui.field_cap'), true)), array('controller' => 'help', 'action' => 'guide', 'administrator', 'fields'), array(__('Help', true), __('Administrators', true)));
			$this->_addMenuItem(__('Registration', true), array('controller' => 'help', 'action' => 'guide', 'administrator', 'registration'), array(__('Help', true), __('Administrators', true)));
		}

		if ($this->is_admin || $this->is_manager) {
			$this->_addMenuItem(__('Statistics', true), array('controller' => 'people', 'action' => 'statistics'), __('People', true));
			$this->_addMenuItem(__('Participation', true), array('controller' => 'people', 'action' => 'participation'), array(__('People', true), __('Statistics', true)));
			$this->_addMenuItem(__('Retention', true), array('controller' => 'people', 'action' => 'retention'), array(__('People', true), __('Statistics', true)));
			$this->_addMenuItem(__('Statistics', true), array('controller' => 'teams', 'action' => 'statistics'), __('Teams', true));
			if (Configure::read('feature.registration')) {
				$this->_addMenuItem(__('Statistics', true), array('controller' => 'registrations', 'action' => 'statistics'), __('Registration', true));
			}
		}

		if (!$this->is_logged_in) {
			$this->_addMenuItem(__('Reset password', true), array('controller' => 'users', 'action' => 'reset_password'));
		}
		if (Configure::read('feature.manage_accounts')) {
			if (!$this->is_logged_in) {
				$this->_addMenuItem(sprintf(__('Create %s', true), __('account', true)), array('controller' => 'users', 'action' => 'create_account'));
			} else if ($this->is_admin || $this->is_manager) {
				$this->_addMenuItem(sprintf(__('Create %s', true), __('account', true)), array('controller' => 'users', 'action' => 'create_account'), __('People', true));
			}
		}

		if (! $this->Session->read('Zuluru.external_login')) {
			if ($this->is_logged_in) {
				$this->_addMenuItem(__('Logout', true), array('controller' => 'users', 'action' => 'logout'));
			} else {
				$this->_addMenuItem(__('Login', true), array('controller' => 'users', 'action' => 'login'));
			}
		}
	}

	/**
	 * Put personalized items like specific teams and divisions on the menu.
	 */
	function _initPersonalMenu($id = null, $name = null) {
		if ($id) {
			$this->_addMenuItem(__('View', true), array('controller' => 'people', 'action' => 'view', 'act_as' => $id), array('My Profile', $name));
			$this->_addMenuItem(__('Edit', true), array('controller' => 'people', 'action' => 'edit', 'act_as' => $id), array('My Profile', $name));
			$this->_addMenuItem(__('Preferences', true), array('controller' => 'people', 'action' => 'preferences', 'act_as' => $id), array('My Profile', $name));
			$this->_addMenuItem(__('Waiver history', true), array('controller' => 'people', 'action' => 'waivers', 'act_as' => $id), array('My Profile', $name));
			$this->_addMenuItem(__('Upload photo', true), array('controller' => 'people', 'action' => 'photo_upload', 'act_as' => $id), array('My Profile', $name));

			if (Configure::read('feature.registration')) {
				$unpaid = $this->UserCache->read('RegistrationsCanPay', $id);
				if (!empty ($unpaid)) {
					$this->_addMenuItem(__('Checkout', true), array('controller' => 'registrations', 'action' => 'checkout', 'act_as' => $id), array(__('Registration', true), $name));
				}

				$registrations = $this->UserCache->read('Registrations', $id);
				if (!empty($registrations)) {
					$this->_addMenuItem(__('History', true), array('controller' => 'people', 'action' => 'registrations', 'act_as' => $id), array(__('Registration', true), $name));
				}
				$this->_addMenuItem(__('Wizard', true), array('controller' => 'events', 'action' => 'wizard', 'act_as' => $id), array(__('Registration', true), $name));
			}
		}

		$teams = $this->UserCache->read('Teams', $id);
		foreach ($teams as $team) {
			$this->_addTeamMenuItems ($team, $id, $name);
		}
		$all_teams = $this->UserCache->read('AllTeamIDs');
		if (!empty($all_teams)) {
			$this->_addMenuItem(__('My history', true), array('controller' => 'people', 'action' => 'teams'), __('Teams', true));
		}
		if ($id) {
			$all_teams = $this->UserCache->read('AllTeamIDs', $id);
			if (!empty($all_teams)) {
				$this->_addMenuItem(__('History', true), array('controller' => 'people', 'action' => 'teams', 'person' => $id, 'act_as' => $id), array(__('Teams', true), $name));
			}
		}

		if (!$id) {
			if (Configure::read('feature.franchises')) {
				$franchises = $this->UserCache->read('Franchises');
				if (!empty($franchises)) {
					foreach ($franchises as $franchise) {
						$this->_addFranchiseMenuItems ($franchise);
					}
				}
			}

			$divisions = $this->UserCache->read('Divisions');
			foreach ($divisions as $division) {
				$this->_addDivisionMenuItems ($division['Division'], $division['League']);
			}
		}
	}

	/**
	 * Add all the links for a team to the menu.
	 */
	function _addTeamMenuItems($team, $id = null, $name = null) {
		if ($id) {
			$path = array(__('Teams', true), $name);
		} else {
			$path = array(__('Teams', true));
		}

		$is_captain = in_array($team['Team']['id'], $this->UserCache->read('OwnedTeamIDs'));
		if (empty($team['Division']['id'])) {
			$affiliate_id = $team['Team']['affiliate_id'];
		} else {
			$affiliate_id = $team['Division']['League']['affiliate_id'];
		}
		$is_manager = $this->is_manager && in_array($affiliate_id, $this->UserCache->read('ManagedAffiliateIDs'));

		$this->_limitOverride($team['Team']['id']);
		$key = "{$team['Team']['name']}::{$team['Team']['id']}";

		if (!empty($team['Team']['division_id'])) {
			$this->_addMenuItem($team['Team']['name'] . ' (' . $team['Division']['long_league_name'] . ')', array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), $path, $key);
			$this->_addMenuItem(__('Schedule', true), array('controller' => 'teams', 'action' => 'schedule', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			$this->_addMenuItem(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $team['Division']['id'], 'team' => $team['Team']['id']), array_merge($path, array($key)));
			if ($team['Team']['track_attendance'] &&
				in_array($team['Team']['id'], $this->UserCache->read('AllTeamIDs')))
			{
				$this->_addMenuItem(__('Attendance', true), array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			}
			if ($this->is_logged_in && $team['Team']['open_roster'] && !Division::rosterDeadlinePassed($team['Division']) &&
				!in_array($team['Team']['id'], $this->UserCache->read('TeamIDs')))
			{
				$this->_addMenuItem(__('Join team', true), array('controller' => 'teams', 'action' => 'roster_request', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			}
			$this->_addDivisionMenuItems($team['Division'], $team['Division']['League'], $id, $name);
		} else {
			$this->_addMenuItem($team['Team']['name'], array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']), $path, $key);
		}

		if ($this->is_admin || $is_manager || $is_captain) {
			$this->_addMenuItem(__('Edit', true), array('controller' => 'teams', 'action' => 'edit', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			$this->_addMenuItem(__('Player emails', true), array('controller' => 'teams', 'action' => 'emails', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			$this->_addMenuItem(__('Delete', true), array('controller' => 'teams', 'action' => 'delete', 'team' => $team['Team']['id']), array_merge($path, array($key)));
		}
		if ($this->effective_admin || $is_manager ||
			(($is_captain || $this->effective_coordinator) && !Division::rosterDeadlinePassed($team['Division'])))
		{
			$this->_addMenuItem(__('Add player', true), array('controller' => 'teams', 'action' => 'add_player', 'team' => $team['Team']['id']), array_merge($path, array($key)));
		}
		if ($this->effective_admin) {
			$this->_addMenuItem(__('Move', true), array('controller' => 'teams', 'action' => 'move', 'team' => $team['Team']['id']), array_merge($path, array($key)));
		}
		if (($this->is_admin || $is_manager) && League::hasSpirit($team)) {
			$this->_addMenuItem(__('Spirit', true), array('controller' => 'teams', 'action' => 'spirit', 'team' => $team['Team']['id']), array_merge($path, array($key)));
		}
		if ($this->is_logged_in && League::hasStats($team)) {
			$this->_addMenuItem(__('Stats', true), array('controller' => 'teams', 'action' => 'stats', 'team' => $team['Team']['id']), array_merge($path, array($key)));
			$this->_addMenuItem(__('Download', true), array('controller' => 'teams', 'action' => 'stats', 'team' => $team['Team']['id'], 'ext' => 'csv'), array_merge($path, array($key, 'Stats')));
		}
	}

	/**
	 * Add all the links for a franchise to the menu.
	 */
	function _addFranchiseMenuItems($franchise, $id = null, $name = null) {
		if ($id) {
			$path = array(__('Teams', true), __('Franchises', true), $name);
		} else {
			$path = array(__('Teams', true), __('Franchises', true));
		}

		$this->_addMenuItem($franchise['name'], array('controller' => 'franchises', 'action' => 'view', 'franchise' => $franchise['id']), $path, "{$franchise['name']}::{$franchise['id']}");
		$is_owner = in_array($franchise['id'], $this->UserCache->read('FranchiseIDs'));
		$is_manager = $this->is_manager && in_array($franchise['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));

		if ($this->is_admin || $is_manager || $is_owner) {
			$this->_addMenuItem(__('Edit', true), array('controller' => 'franchises', 'action' => 'edit', 'franchise' => $franchise['id']), array_merge($path, array("{$franchise['name']}::{$franchise['id']}")));
			$this->_addMenuItem(__('Add Team', true), array('controller' => 'franchises', 'action' => 'add_team', 'franchise' => $franchise['id']), array_merge($path, array("{$franchise['name']}::{$franchise['id']}")));
			$this->_addMenuItem(__('Add an Owner', true), array('controller' => 'franchises', 'action' => 'add_owner', 'franchise' => $franchise['id']), array_merge($path, array("{$franchise['name']}::{$franchise['id']}")));
			$this->_addMenuItem(__('Delete', true), array('controller' => 'franchises', 'action' => 'delete', 'franchise' => $franchise['id']), array_merge($path, array("{$franchise['name']}::{$franchise['id']}")));
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
	function _addDivisionMenuItems($division, $league, $id = null, $name = null) {
		Configure::load("sport/{$league['sport']}");
		if ($id) {
			$path = array(__('Leagues', true), $name);
		} else {
			$path = array(__('Leagues', true));
		}

		if (!array_key_exists('league_name', $division)) {
			Division::_addNames($division, $league);
		}

		$is_coordinator = in_array($division['id'], $this->UserCache->read('DivisionIDs'));
		$is_manager = $this->is_manager && in_array($league['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs'));

		if (array_key_exists('Division', $league)) {
			$division_count = count($league['Division']);
		} else {
			$division_count = $this->requestAction(array('controller' => 'leagues', 'action' => 'division_count'),
					array('named' => array('league' => $league['id'])));
		}

		if ($division_count == 1) {
			$this->_addMenuItem($division['league_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $league['id']), $path);
			$path[] = $division['league_name'];
		} else {
			$this->_addMenuItem($league['name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $league['id']), $path);
			$path[] = $league['name'];
			if (!empty($division['name'])) {
				$this->_addMenuItem($division['name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $division['id']), $path);
				$path[] = $division['name'];
			}
		}
		$this->_addMenuItem(__('Schedule', true), array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['id']), $path);
		$this->_addMenuItem(__('Standings', true), array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['id']), $path);
		if ($this->is_logged_in) {
			if ($division['schedule_type'] != 'competition') {
				$this->_addMenuItem(__('Scores', true), array('controller' => 'divisions', 'action' => 'scores', 'division' => $division['id']), $path);
			}
			if (League::hasStats($league)) {
				$this->_addMenuItem(__('Stats', true), array('controller' => 'divisions', 'action' => 'stats', 'division' => $division['id']), $path);
			}
		}
		if ($this->is_admin || $is_manager || $is_coordinator) {
			$this->_addMenuItem(__('Add Games', true), array('controller' => 'schedules', 'action' => 'add', 'division' => $division['id']), array_merge($path, array('Schedule')));
			if ($division['schedule_type'] != 'competition') {
				$this->_addMenuItem(__('Approve scores', true), array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['id']), $path);
			}
			if ($division_count == 1) {
				$this->_addMenuItem(__('Edit', true), array('controller' => 'leagues', 'action' => 'edit', 'league' => $league['id']), $path);
			} else {
				$this->_addMenuItem(__('Edit', true), array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['id']), $path);
			}
			$this->_addMenuItem(sprintf(__('%s distribution', true), __(Configure::read('sport.field_cap'), true)), array('controller' => 'divisions', 'action' => 'fields', 'division' => $division['id']), $path);
			$this->_addMenuItem(sprintf(__('%s availability', true), __(Configure::read('sport.field_cap'), true)), array('controller' => 'divisions', 'action' => 'slots', 'division' => $division['id']), $path);
			$this->_addMenuItem(__('Status report', true), array('controller' => 'divisions', 'action' => 'status', 'division' => $division['id']), $path);
			if (Configure::read('scoring.allstars') && $division['allstars'] != 'never') {
				$this->_addMenuItem(__('All stars', true), array('controller' => 'divisions', 'action' => 'allstars', 'division' => $division['id']), $path);
			}
			$this->_addMenuItem(__('Captain emails', true), array('controller' => 'divisions', 'action' => 'emails', 'division' => $division['id']), $path);
			if (League::hasSpirit($league)) {
				$this->_addMenuItem(__('Spirit Report', true), array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id']), $path);
				$this->_addMenuItem(__('Download', true), array('controller' => 'divisions', 'action' => 'spirit', 'division' => $division['id'], 'ext' => 'csv'), array_merge($path, array('Spirit Report')));
			}
			$this->_addMenuItem(__('Adjust seeds', true), array('controller' => 'divisions', 'action' => 'seeds', 'division' => $division['id']), $path);
		}
		if ($this->is_admin) {
			$this->_addMenuItem(__('Add coordinator', true), array('controller' => 'divisions', 'action' => 'add_coordinator', 'division' => $division['id']), $path);
		}

		// Some items are only applicable depending on league configuration
		if (!empty ($division['schedule_type'])) {
			$league_obj = $this->_getComponent ('LeagueType', $division['schedule_type'], $this);
			$league_obj->addMenuItems ($division, $path, $is_coordinator || $is_manager);
		}
	}

	/**
	 * Add all the links for a field to the menu.
	 */
	function _addFieldMenuItems($field) {
		$this->_addMenuItem($field['Field']['long_name'], array('controller' => 'fields', 'action' => 'view', 'field' => $field['Field']['id']), __(Configure::read('ui.fields_cap'), true));
		$this->_addMenuItem(__('View bookings', true), array('controller' => 'fields', 'action' => 'bookings', 'field' => $field['Field']['id']), array(__(Configure::read('ui.fields_cap'), true), $field['Field']['long_name']));
		if ($this->is_admin) {
			$this->_addMenuItem(__('Add Game Slot', true), array('controller' => 'game_slots', 'action' => 'add', 'field' => $field['Field']['id']), array(__(Configure::read('ui.fields_cap'), true), $field['Field']['long_name']));
			$this->_addMenuItem(sprintf(__('Edit %s', true), __(Configure::read('ui.field_cap'), true)), array('controller' => 'fields', 'action' => 'edit', 'field' => $field['Field']['id']), array(__(Configure::read('ui.fields_cap'), true), $field['Field']['long_name']));
			$this->_addMenuItem(__('Edit Layout', true), array('controller' => 'maps', 'action' => 'edit', 'field' => $field['Field']['id']), array(__(Configure::read('ui.fields_cap'), true), $field['Field']['long_name']));
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
	function _addMenuItem($name, $url = null, $path = array(), $sort = null, $opts = null) {
		if ($sort === null)
			$sort = $name;
		if (!is_array ($path))
			$path = array($path);
		$parent =& $this->menu_items;
		foreach ($path as $element) {
			if (!empty($element)) {
				if (!array_key_exists ($element, $parent)) {
					$parent[$element] = array('items' => array(), 'name' => $element);
				}
				$parent =& $parent[$element]['items'];
			}
		}

		if (!array_key_exists ($sort, $parent)) {
			$parent[$sort] = array('items' => array(), 'name' => $name);
		}

		if ($url) {
			$parent[$sort]['url'] = $url;
		}
		if ($opts) {
			$parent[$sort]['opts'] = $opts;
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
		if (empty($opts['to'])) {
			return (array_key_exists('ignore_empty_address', $opts) && $opts['ignore_empty_address']);
		}

		// Add any custom headers
		if (array_key_exists ('header', $opts)) {
			$email->header($opts['header']);
		}

		// Check if there are attachments to be included
		$email->attachments = Configure::read('email.attachments');
		if (empty($email->attachments)) {
			$email->attachments = array();
		}
		if (!empty($opts['attachments'])) {
			$email->attachments = array_merge($email->attachments, $opts['attachments']);
		}
		if (!empty($email->attachments)) {
			$email->filePaths = Configure::read('email.attachment_paths');
		}

		// Get ready and send it
		$email->initialize ($this, $opts);
		if (array_key_exists('content', $opts)) {
			$success = $email->send($opts['content']);
		} else {
			$success = $email->send();
		}

		if (! empty ($email->smtpError))
		{
			$this->log("smtp-errors: {$email->smtpError}");
		}

		return $success;
	}

	function _extractEmails($input, $array = false, $check_relatives = true) {
		if (is_array ($input)) {
			$emails = array();

			if (!empty($input['email_formatted'])) {
				$emails[$input['email']] = $input['email_formatted'];
			} else if (!empty($input['email'])) {
				$emails[$input['email']] = $input['email'];
			}
			if (!empty($input['alternate_email_formatted'])) {
				$emails[$input['alternate_email']] = $input['alternate_email_formatted'];
			} else if (!empty($input['alternate_email'])) {
				$emails[$input['alternate_email']] = $input['alternate_email'];
			}

			// Check for relatives, if this is a person record without a user record
			if ($check_relatives && !empty($input['id']) && !empty($input['first_name']) && empty($input['user_id'])) {
				$relatives = $this->UserCache->read('RelatedTo', $input['id']);
				$emails = array_merge($this->_extractEmails($relatives, true, false), $emails);
			}

			// If we haven't found anything yet, look further down the hierarchy
			if (empty($emails)) {
				foreach ($input as $values) {
					if (is_array($values)) {
						$emails = array_merge($this->_extractEmails($values, true, $check_relatives), $emails);
					}
				}
			}

			if (!empty($emails) && !$array) {
				return reset($emails);
			}
			return array_unique($emails);
		}

		// Anything else, return as-is and hope for the best!
		if ($array) {
			return array($input);
		} else {
			return $input;
		}
	}

	function _handlePersonSearch($params, &$url, $person_model = null, $conditions = array()) {
		if (!empty($params)) {
			$names = array();
			foreach (array('first_name', 'last_name') as $field) {
				if (!empty($params[$field])) {
					$names[] = trim ($params[$field], ' *');
				}
			}
			$test = implode('', $names);
			$min = ($this->is_admin || $this->is_manager) ? 1 : 2;
			if (strlen ($test) < $min) {
				$this->set('error', __('The search terms used are too general. Please be more specific.', true));
			} else {
				// This pagination needs the model at the top level
				if (!isset($this->Person)) {
					$this->Person = $person_model;
				}
				$this->_mergePaginationParams();
				$this->paginate['Person'] = array(
					'conditions' => array_merge(
						$this->_generateSearchConditions($params, 'Person', 'AffiliatePerson'),
						$conditions
					),
					'contain' => false,
					'fields' => array('DISTINCT Person.id', 'Person.first_name', 'Person.last_name'),
					'limit' => Configure::read('feature.items_per_page'),
					'joins' => array(
						array(
							'table' => "{$this->Person->tablePrefix}affiliates_people",
							'alias' => 'AffiliatePerson',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'AffiliatePerson.person_id = Person.id',
						),
						array(
							'table' => "{$this->Person->tablePrefix}groups_people",
							'alias' => 'GroupPerson',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'GroupPerson.person_id = Person.id',
						),
					),
				);
				$this->set('people', $this->paginate('Person'));
			}
		}
		$this->set(compact('url'));
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

	function _generateSearchConditions($params, $model = null, $affiliate_model = null) {
		$conditions = array();
		if ($model == null) {
			$model = Inflector::singularize($this->name);
		}
		$model_obj = $this->{$model};

		// Match people in the affiliate, or admins who are effectively in all
		if ($affiliate_model && array_key_exists('affiliate_id', $params)) {
			$admins = $this->Person->GroupsPerson->find('list', array(
					'conditions' => array('group_id' => GROUP_ADMIN),
					'fields' => array('person_id', 'person_id'),
			));
			$conditions['OR'] = array(
				"$affiliate_model.affiliate_id" => $params['affiliate_id'],
				'Person.id' => $admins,
			);
		}

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

	// TODO: Move this to a component? Leagues and Teams need it, but nothing else
	function _getAffiliateId ($division, $team) {
		if (empty($division['season_divisions'])) {
			return null;
		}

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

	static function _isChild($birthdate) {
		// Assumption is that youth leagues will always require birthdates to properly categorize players
		if (empty($birthdate)) {
			return false;
		}

		if (Configure::read('feature.birth_year_only')) {
			$birth_year = substr($birthdate, 0, 4);
			if ($birth_year == '0000') {
				return false;
			}
			return (date('Y') - $birth_year < 18);
		}
		return (strtotime($birthdate) > strtotime('-18 years'));
	}

	function _expireReservations() {
		if (!isset ($this->Registration)) {
			$this->Registration = ClassRegistry::init ('Registration');
		}

		$expired = $this->Registration->find('all', array(
				'contain' => array(
					'Event' => 'EventType',
					'Person' => $this->Auth->authenticate->name,
				),
				'conditions' => array(
					'payment' => 'Reserved',
					'reservation_expires < NOW()',
				),
		));
		foreach ($expired as $registration) {
			$this->Registration->id = $registration['Registration']['id'];
			// This payment status may change in the unreserve call, but it
			// needs to be set now so cap calculations are correct.
			$this->Registration->saveField('payment', 'Unpaid');

			$event_obj = $this->_getComponent ('EventType', $registration['Event']['EventType']['type'], $this);
			$status = $event_obj->unreserve($registration, $registration);

			if ($registration['Registration']['delete_on_expiry']) {
				// This reservation was created from the waiting list, and should be deleted
				$event_obj->unregister($registration, $registration);
			} else if ($status != 'Unpaid') {
				$this->Registration->id = $registration['Registration']['id'];
				$this->Registration->saveField('payment', $status);
			}

			$event_obj->_saveViewVars();
			$this->set(array('event' => $registration, 'registration' => $registration, 'status' => $status));

			$this->_sendMail (array (
					'to' => $registration,
					'subject' =>  Configure::read('organization.name') . ' Reservation expired',
					'template' => 'reservation_expired',
					'sendAs' => 'both',
			));
			$event_obj->_restoreViewVars();
		}
	}
}
?>
