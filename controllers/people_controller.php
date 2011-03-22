<?php
class PeopleController extends AppController {

	var $name = 'People';
	var $uses = array('Person', 'Team', 'League', 'Group', 'Province', 'Country');
	var $helpers = array('CropImage');
	var $components = array('ImageCrop');
	var $paginate = array(
		'Person' => array(),
		'Registration' => array(
			'contain' => array('Event' => array('EventType')),
			'order' => array('Registration.created' => 'DESC'),
		),
	);

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'sign_waiver',
				'view_waiver',
				'search',
				'teams',
				'photo',
		)))
		{
			return true;
		}

		// People can perform these operations on their own account
		if (in_array ($this->params['action'], array(
				'edit',
				'preferences',
				'photo_upload',
				'photo_resize',
				'registrations',
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

	function statistics() {
		// Get the list of accounts by status
		$status_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.status',
					'COUNT(Person.id) AS count',
				),
				'group' => 'Person.status',
				'order' => 'Person.status',
				'recursive' => -1,
		));

		// Get the list of accounts by group
		$group_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.group_id',
					'COUNT(Person.id) AS count',
				),
				'group' => 'Person.group_id',
				'order' => 'Person.group_id',
				'recursive' => -1,
		));
		$groups = $this->Person->Group->find('list');

		// Get the list of accounts by gender
		$gender_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.gender',
					'COUNT(Person.id) AS count',
				),
				'group' => 'Person.gender',
				'order' => 'Person.gender DESC',
				'recursive' => -1,
		));

		// Get the list of accounts by age
		$age_count = $this->Person->find('all', array(
				'fields' => array(
					'FLOOR((YEAR(NOW()) - YEAR(birthdate)) / 5) * 5 AS age_bucket',
					'COUNT(Person.id) AS count',
				),
				'conditions' => array(
					array('birthdate !=' => null),
					array('birthdate !=' => '0000-00-00'),
				),
				'group' => 'age_bucket',
				'order' => 'age_bucket',
				'recursive' => -1,
		));

		// Get the list of accounts by year started
		$started_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.year_started',
					'COUNT(Person.id) AS count',
				),
				'group' => 'year_started',
				'order' => 'year_started',
				'recursive' => -1,
		));

		// Get the list of accounts by skill level
		$skill_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.skill_level',
					'COUNT(Person.id) AS count',
				),
				'group' => 'skill_level',
				'order' => 'skill_level DESC',
				'recursive' => -1,
		));

		// Get the list of accounts by city
		$city_count = $this->Person->find('all', array(
				'fields' => array(
					'Person.addr_city',
					'COUNT(Person.id) AS count',
				),
				'group' => 'addr_city HAVING count > 2',
				'order' => 'count DESC',
				'recursive' => -1,
		));

		$this->set(compact('status_count', 'groups', 'group_count', 'gender_count',
				'age_count', 'started_count', 'skill_count', 'city_count'));
	}

	function view() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(__('Invalid person', true));
				$this->redirect('/');
			}
		}

		$person = $this->Person->readCurrent($id);
		$this->set(compact('person'));
		$this->set('is_me', ($id === $my_id));

		// Check if the current user is a captain of a team the viewed player is on
		$my_team_ids = $this->Session->read('Zuluru.OwnedTeamIDs');
		$team_ids = Set::extract ('/Team/id', $person['Team']);
		$on_my_teams = array_intersect ($my_team_ids, $team_ids);
		$this->set('is_captain', !empty ($on_my_teams));

		// Check if the current user is a coordinator of a league the viewed player is a captain in
		$my_league_ids = $this->Session->read('Zuluru.LeagueIDs');
		$league_ids = Set::extract ('/Team/league_id', $person['Team']);
		$in_my_leagues = array_intersect ($my_league_ids, $league_ids);
		$this->set('is_coordinator', !empty ($in_my_leagues));

		// Check if the current user is a captain in a league the viewed player is a captain in
		$captain_in_league_ids = Set::extract ('/Team/league_id', $this->Session->read('Zuluru.OwnedTeams'));
		$opponent_captain_in_league_ids = array();
		foreach ($person['Team'] as $team) {
			if (in_array ($team['TeamsPerson']['status'], Configure::read('privileged_roster_positions'))) {
				$opponent_captain_in_league_ids[] = $team['Team']['league_id'];
			}
		}
		$captains_in_same_league = array_intersect ($captain_in_league_ids, $opponent_captain_in_league_ids);
		$this->set('is_league_captain', !empty ($captains_in_same_league));

		// Check if the current user is on a team the viewed player is a captain of
		$this->set('is_my_captain', false);
		$positions = Configure::read('privileged_roster_positions');
		foreach ($person['Team'] as $team) {
			if (in_array ($team['TeamsPerson']['status'], $positions) &&
				in_array ($team['Team']['id'], $this->Session->read('Zuluru.TeamIDs'))
			) {
				$this->set('is_my_captain', true);
				break;
			}
		}
	}

	function edit() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$id && empty($this->data)) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(__('Invalid person', true));
				$this->redirect('/');
			}
		}
		$this->set(compact('id'));

		$this->_loadAddressOptions();
		$this->_loadGroupOptions();

		if (!empty($this->data)) {
			$this->data['Person']['complete'] = true;
			if ($this->Person->save($this->data)) {
				$this->Session->setFlash(__('The person has been saved', true));

				// There may be callbacks to handle
				$components = Configure::read('callbacks.user');
				foreach ($components as $name => $config) {
					$component = $this->_getComponent('User', $name, $this, false, $config);
					$component->onEdit($this->data['Person']);
				}

				if ($this->data['Person']['id'] == $my_id) {
					// Delete the session data, so it's reloaded next time it's needed
					$this->Session->delete('Zuluru.Person');
				}

				$this->redirect('/');
			} else {
				$this->Session->setFlash(__('The person could not be saved. Please correct the errors below and try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->Person->recursive = -1;
			$this->data = $this->Person->read(null, $id);
		}
	}

	function preferences() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(__('Invalid person', true));
				$this->redirect('/');
			}
		}
		$this->set(compact('id'));
		$this->set('person', $this->Person->readCurrent($id));

		$setting = ClassRegistry::init('Setting');
		if (!empty($this->data)) {
			if ($setting->saveAll ($this->data['Setting'], array('validate' => false))) {
				$this->Session->setFlash(__('Your preferences have been saved', true));
				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				$this->Configuration->load($my_id);
				$this->_initMenu();
			} else {
				$this->Session->setFlash(__('Failed to save your preferences', true));
			}
		}

		$this->data = $setting->find('all', array(
				'conditions' => array('person_id' => $id),
		));
	}

	function photo() {
		$file_dir = Configure::read('folders.uploads');
		$photo = $this->Person->Upload->find('first', array(
				'contain' => array(),
				'conditions' => array(
					'other_id' => $this->_arg('person'),
					'type' => 'person',
				),
		));
		if (!empty ($photo)) {
			$this->layout = 'file';
			$file = file_get_contents($file_dir . DS . $photo['Upload']['filename']);
			$type = 'image/jpeg';
			$this->set(compact('file', 'type'));
		}
	}

	function photo_upload() {
		$person = $this->_findSessionData('Person', $this->Person);
		$size = 150;
		$this->set(compact('person', 'size'));

		if (!empty ($this->data) && array_key_exists ('image', $this->data)) {
			if (empty ($this->data['image'])) {
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_INI_SIZE) {
				$max = ini_get('upload_max_filesize');
				$unit = substr($max,-1);
				if ($unit == 'M' || $unit == 'K') {
					$max .= 'b';
				}
				$this->Session->setFlash(sprintf (__('The selected photo is too large. Photos must be less than %s.', true), $max));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_NO_FILE) {
				$this->Session->setFlash(__('You must select a photo to upload', true));
				return;
			}
			if ($this->data['image']['error'] == UPLOAD_ERR_NO_TMP_DIR ||
				$this->data['image']['error'] == UPLOAD_ERR_CANT_WRITE)
			{
				$this->Session->setFlash(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.', true));
				return;
			}
			if ($this->data['image']['error'] != 0 ||
				strpos ($this->data['image']['type'], 'image/') === false)
			{
				$this->log($this->data, 'upload');
				$this->Session->setFlash(__('There was an unexpected error uploading the file. Please try again.', true));
				return;
			}

			// Image was uploaded, ask user to crop it
			$temp_dir = Configure::read('folders.league_base') . DS . 'temp';
			$rand = mt_rand();
			$uploaded = $this->ImageCrop->uploadImage($this->data['image'], $temp_dir, "temp_{$person['id']}_$rand");
			$this->set(compact('uploaded'));
			if (!$uploaded) {
				$this->Session->setFlash(__('Unexpected error uploading the file', true));
			} else {
				$this->render('photo_resize');
			}
		}
	}

	function photo_resize() {
		if (!empty ($this->data)) {
			$person = $this->_findSessionData('Person', $this->Person);
			$size = 150;
			$this->set(compact('person', 'size'));
			$temp_dir = Configure::read('folders.league_base') . DS . 'temp';
			$file_dir = Configure::read('folders.uploads');

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
						'contain' => array(),
						'conditions' => array(
							'other_id' => $person['id'],
							'type' => 'person',
						),
				));
				if (empty ($photo)) {
					$this->Person->Upload->save(array(
							'other_id' => $person['id'],
							'type' => 'person',
							'filename' => basename ($image),
					));
				} else {
					$this->Person->Upload->id = $photo['Upload']['id'];
					$this->Person->Upload->saveField ('approved', false);
				}
				$this->Session->setFlash(__('Photo saved, but will not be visible by others until approved', true));
			}
			$this->redirect(array('action' => 'view'));
		}
	}

	function approve_photos() {
		$photos = $this->Person->Upload->find('all', array(
				'contain' => array('Person'),
				'conditions' => array('approved' => 0),
		));
		if (empty ($photos)) {
			$this->Session->setFlash(__('There are no photos to approve.', true));
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

		$person = $this->Person->Upload->read (null, $id);
		$variables = array(
			'%fullname' => $person['Person']['full_name'],
		);

		if (!$this->_sendMail (array (
				'to' => $person,
				'config_subject' => 'photo_approved_subject',
				'config_body' => "photo_approved_body",
				'variables' => $variables,
				'sendAs' => 'text',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']));
		}
	}

	function delete_photo() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);

		$photo = $this->Person->Upload->read(null, $id);
		if (empty ($photo)) {
			$success = false;
		} else {
			$success = $this->Person->Upload->delete ($id);
			if ($success) {
				$file_dir = Configure::read('folders.uploads');
				unlink($file_dir . DS . $photo['Upload']['filename']);
			}
		}
		$this->set(compact('success'));

		$variables = array(
			'%fullname' => $photo['Person']['full_name'],
		);

		if (!$this->_sendMail (array (
				'to' => $photo,
				'config_subject' => 'photo_deleted_subject',
				'config_body' => "photo_deleted_body",
				'variables' => $variables,
				'sendAs' => 'text',
		)))
		{
			$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $photo['Person']['email']));
		}
	}

	function sign_waiver() {
		$type = $this->_arg('type');
		if ($type == null || !array_key_exists ($type, Configure::read('options.waiver_types'))) {
			$this->Session->setFlash(__('Unknown waiver type', true));
			$this->redirect('/');
		}

		$id = $this->Auth->user('id');
		if (!$id) {
			$this->Session->setFlash(__('Invalid person', true));
			$this->redirect('/');
		}
		$this->Person->contain ('Waiver');
		$person = $this->Person->read(null, $id);

		// Make sure it's either this year or next year they're waivering for
		$current = $this->membershipYear();
		$year = $this->_arg('year');
		if ($year == null) {
			$year = $current;
		}
		$expiry = $this->membershipEnd($year) . ' 23:59:59';

		$waiver = $this->_findWaiver ($person['Waiver'], $expiry);
		if ($waiver != null) {
			$this->Session->setFlash(__('You have already accepted this waiver', true));
			$this->redirect('/');
		}
		$this->set(compact('person', 'waiver'));

		if ($year != $current && $year != $current+1) {
			$this->Session->setFlash(__('Invalid membership year', true));
			$this->redirect('/');
		}

		if (!empty ($this->data)) {
			if ($this->data['Person']['signed'] == 'yes') {
				if ($this->Person->Waiver->save (array(
						'person_id' => $id,
						'type' => $type,
						'expires' => $expiry,
				)))
				{
					// By deleting the waivers session variable, the next page will reload them
					$this->Session->delete('Zuluru.Waivers');
					$this->Session->setFlash(__('Waiver signed.', true));
					$event = $this->_arg('event');
					if ($event) {
						$this->redirect(array('controller' => 'registrations', 'action' => 'register', 'event' => $event));
					} else {
						$this->redirect('/');
					}
				} else {
					$this->Session->setFlash(__('Failed to save the waiver.', true));
				}
			} else {
				$this->Session->setFlash(__('Sorry, you may only proceed with registration by agreeing to the waiver.', true));
			}
		}
		$this->set(compact('type', 'year'));
	}

	function view_waiver() {
		$type = $this->_arg('type');
		if ($type == null || !array_key_exists ($type, Configure::read('options.waiver_types'))) {
			$this->Session->setFlash(__('Unknown waiver type', true));
			$this->redirect('/');
		}

		$id = $this->Auth->user('id');
		if (!$id) {
			$this->Session->setFlash(__('Invalid person', true));
			$this->redirect('/');
		}
		$this->Person->contain ('Waiver');
		$person = $this->Person->read(null, $id);

		// Make sure it's either this year or next year they're waivering for
		$current = $this->membershipYear();
		$year = $this->_arg('year');
		if ($year == null) {
			$year = $current;
		}

		$waiver = $this->_findWaiver ($person['Waiver'], $this->membershipEnd ($year));
		$this->set(compact('person', 'waiver'));

		if ($year != $current && $year != $current+1) {
			$this->Session->setFlash(__('Invalid membership year', true));
			$this->redirect('/');
		}

		$this->set(compact('type', 'year'));
	}

	function delete() {
		if (!Configure::read('feature.manage_accounts')) {
			$this->Session->setFlash (__('This system uses ' . Configure::read('feature.manage_name') . ' to manage user accounts. Account deletion through Zuluru is disabled.', true));
			$this->redirect('/');
		}

		$id = $this->_arg('person');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for person', true));
			$this->redirect('/');
		}

		// TODO: Don't delete people that have paid registration history, are on team rosters, league coordinators, or the only admin

		// TODO Handle deletions
		$this->Session->setFlash(sprintf(__('Deleting %s is disabled', true), 'players'));
		$this->redirect('/');

		if ($this->Person->delete($id)) {
			$this->Session->setFlash(__('Person deleted', true));
			// TODO: Unwind any registrations, including calling event_obj for additional processing like deleting team records
			$this->redirect('/');
		}
		$this->Session->setFlash(__('Person was not deleted', true));
		$this->redirect('/');
	}

	function search() {
		$params = $url = $this->_extractSearchParams();
		if (!empty($params)) {
			$test = trim (@$params['first_name'], ' *') . trim (@$params['last_name'], ' *');
			if (strlen ($test) < 2) {
				$this->set('short', true);
			} else {
				$this->_mergePaginationParams();
				$this->paginate['Person'] = array(
					'conditions' => $this->_generateSearchConditions($params),
					'contain' => array('Upload'),
				);
				$this->set('people', $this->paginate('Person'));
			}
		}
		$this->set(compact('url'));
	}

	function list_new() {
		$new = $this->Person->find ('all', array(
			'conditions' => array(
				'status' => 'new',
				'complete' => 1,
			),
			'order' => array('last_name' => 'DESC', 'first_name' => 'DESC'),
		));
		$this->set(compact('new'));
	}

	function approve() {
		if (!empty ($this->data)) {
			if (empty ($this->data['Person']['disposition'])) {
				$id = $this->data['Person']['id'];
				$this->Session->setFlash(__('You must select a disposition for this account', true));
			} else {
				$this->_approve();
				$this->redirect(array('action' => 'list_new'));
			}
		} else {
			$id = $this->_arg('person');
		}

		if (!$id) {
			$this->Session->setFlash(__('Invalid person', true));
			$this->redirect(array('action' => 'list_new'));
		}

		$this->Person->recursive = -1;
		$person = $this->Person->read(null, $id);
		if (!$person) {
			$this->Session->setFlash(__('Invalid person', true));
			$this->redirect(array('action' => 'list_new'));
		}
		if ($person['Person']['status'] != 'new') {
			$this->Session->setFlash(__('That account has already been approved', true));
			$this->redirect(array('action' => 'list_new'));
		}

		$duplicates = $this->Person->findDuplicates($person);
		$auth = $this->Auth->authenticate->read(null, $id);

		$this->set(compact('person', 'duplicates', 'auth'));
	}

	function _approve() {
		if (strpos ($this->data['Person']['disposition'], ':') !== false) {
			list($disposition,$dup_id) = split(':', $this->data['Person']['disposition']);
		} else {
			$disposition = $this->data['Person']['disposition'];
			$dup_id = null;
		}

		$this->Person->recursive = -1;
		$person = $this->Person->read(null, $this->data['Person']['id']);
		if (!empty ($dup_id)) {
			$existing = $this->Person->read(null, $dup_id);
		}

		// TODO: Some of these require updates/deletions in the settings table
		switch($disposition) {
			case 'approved_player':
				$data = array(
					'id' => $person['Person']['id'],
					// TODO: 'Player' is hard-coded here, but also in the database
					'group_id' => $this->Person->Group->field('id', array('name' => 'Player')),
					'status' => 'active',
				);
				$saved = $this->Person->save ($data, false, array_keys ($data));
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member activation', true));
					$this->redirect(array('action' => 'approve', 'person' => $person['Person']['id']));
				}

				$variables = array(
					'%fullname' => $saved['Person']['full_name'],
					'%memberid' => $id,
					'%username' => $saved['Person']['user_name'],
				);

				if (!$this->_sendMail (array (
						'to' => $person,
						'config_subject' => 'approved_subject',
						'config_body' => "{$disposition}_body",
						'variables' => $variables,
						'sendAs' => 'text',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']));
				}
				break;

			case 'approved_visitor':
				$data = array(
					'id' => $person['Person']['id'],
					// TODO: 'Non-player account' is hard-coded here, but also in the database
					'group_id' => $this->Person->Group->field('id', array('name' => 'Non-player account')),
					'status' => 'inactive',
				);
				$saved = $this->Person->save ($data, false, array_keys ($data));
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member activation', true));
					$this->redirect(array('action' => 'approve', 'person' => $person['Person']['id']));
				}

				$variables = array(
					'%fullname' => $saved['Person']['full_name'],
					'%username' => $saved['Person']['user_name'],
				);

				if (!$this->_sendMail (array (
						'to' => $person,
						'config_subject' => 'approved_subject',
						'config_body' => "{$disposition}_body",
						'variables' => $variables,
						'sendAs' => 'text',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']));
				}
				break;

			case 'delete':
				if (method_exists ($this->Auth->authenticate, 'delete_duplicate_user')) {
					$this->Auth->authenticate->delete_duplicate_user($person['Person']['id']);
				}
				if (! $this->Person->delete($person['Person']['id']) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']));
				}
				break;

			case 'delete_duplicate':
				if (method_exists ($this->Auth->authenticate, 'delete_duplicate_user')) {
					$this->Auth->authenticate->delete_duplicate_user($person['Person']['id']);
				}

				if (! $this->Person->delete($person['Person']['id']) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']));
					break;
				}

				$variables = array(
					'%fullname' => $person['Person']['full_name'],
					'%username' => $person['Person']['user_name'],
					'%existingusername' => $existing['Person']['user_name'],
					'%existingemail' => $existing['Person']['email'],
					'%passwordurl' => Router::url (Configure::read('urls.password_reset'), true),
				);

				if (!$this->_sendMail (array (
						'to' => array($person['Person'], $existing['Person']),
						'config_subject' => "{$disposition}_subject",
						'config_body' => "{$disposition}_body",
						'variables' => $variables,
						'sendAs' => 'text',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']));
				}
				break;

			// This is basically the same as the delete duplicate, except
			// that some old information (e.g. user ID) is preserved
			case 'merge_duplicate':
				if (method_exists ($this->Auth->authenticate, 'merge_duplicate_user')) {
					$this->Auth->authenticate->merge_duplicate_user($person['Person']['id'], $existing['Person']['id']);
				}

				if (! $this->Person->delete($person['Person']['id'], false) ) {
					$this->Session->setFlash(sprintf (__('Failed to delete %s', true), $person['Person']['full_name']));
					break;
				}

				// Unset a few fields that we want to retain from the old record
				foreach (array('group_id', 'status') as $field) {
					unset ($person['Person'][$field]);
				}
				$person['Person']['id'] = $dup_id;

				$saved = $this->Person->save ($person);
				if (!$saved) {
					$this->Session->setFlash(__('Couldn\'t save new member information', true));
				}

				$variables = array(
					'%fullname' => $person['Person']['full_name'],
					'%username' => $person['Person']['user_name'],
					'%existingusername' => $existing['Person']['user_name'],
					'%existingemail' => $existing['Person']['email'],
					'%passwordurl' => Router::url (Configure::read('urls.password_reset'), true),
				);

				if (!$this->_sendMail (array (
						'to' => array($person['Person'], $existing['Person']),
						'config_subject' => "{$disposition}_subject",
						'config_body' => "{$disposition}_body",
						'variables' => $variables,
						'sendAs' => 'text',
				)))
				{
					$this->Session->setFlash(sprintf (__('Error sending email to %s', true), $person['Person']['email']));
				}
				break;
		}
	}

	// This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	function ical($id) {
		$this->layout = 'ical';
		if (!$id) {
			return;
		}

		// Check that the person has enabled this option
		$this->Person->contain(array(
				'Setting',
		));
		$person = $this->Person->readCurrent($id);
		$enabled = Set::extract ('/Setting[name=enable_ical]/value', $person);
		if (empty ($enabled) || !$enabled[0]) {
			return;
		}

		$team_ids = Set::extract ('/Team/id', $person['Team']);
		if (!empty ($team_ids)) {
			$games = $this->League->Game->find ('all', array(
				'conditions' => array(
					'OR' => array(
						'HomeTeam.id' => $team_ids,
						'AwayTeam.id' => $team_ids,
					),
					'Game.published' => true,
				),
				'fields' => array(
					'Game.id', 'Game.home_team', 'Game.home_score', 'Game.away_team', 'Game.away_score', 'Game.status', 'Game.league_id',
					'GameSlot.game_date', 'GameSlot.game_start', 'GameSlot.game_end',
					'HomeTeam.id', 'HomeTeam.name',
					'AwayTeam.id', 'AwayTeam.name',
				),
				'contain' => array(
					'GameSlot' => array('Field' => array('ParentField')),
					'ScoreEntry' => array('conditions' => array('ScoreEntry.team_id' => $team_ids)),
					'HomeTeam',
					'AwayTeam',
				),
				'order' => 'GameSlot.game_date ASC, GameSlot.game_start ASC',
			));

			// Game iCal element will handle team_id as an array
			$this->set('team_id', $team_ids);
			$this->set('games', $games);
		}

		$this->set ('calendar_type', 'Player Schedule');
		$this->set ('calendar_name', "{$person['Person']['full_name']}\'s schedule");

		Configure::write ('debug', 0);
	}

	function registrations() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(__('Invalid person', true));
				$this->redirect('/');
			}
		}

		$this->Person->recursive = -1;
		$this->set('person', $this->Person->read(null, $id));
		$this->set('registrations', $this->paginate ('Registration', array('person_id' => $id)));
	}

	function teams() {
		$id = $this->_arg('person');
		$my_id = $this->Auth->user('id');

		if (!$id) {
			$id = $my_id;
			if (!$id) {
				$this->Session->setFlash(__('Invalid person', true));
				$this->redirect('/');
			}
		}

		$this->Person->recursive = -1;
		$this->set('person', $this->Person->read(null, $id));
		$this->set('teams', $this->Person->Team->readByPlayerId($id, false, false, array('League.open DESC', 'LeaguesDay.day_id')));
	}

	function cron() {
		$this->layout = 'bare';

		if (Configure::read('feature.registration')) {
			$types = $this->Person->Registration->Event->EventType->find ('list', array(
					'fields' => 'id',
					'conditions' => array('type' => 'membership'),
			));
			$events = $this->Person->Registration->Event->find ('all', array(
					'conditions' => array('event_type_id' => $types)
			));

			$year = $this->membershipYear();
			$now = time();

			$current = array();
			foreach ($events as $event) {
				if (strtotime ($event['Event']['membership_begins']) < $now &&
					$now < strtotime ($event['Event']['membership_ends']))
				{
					$current[] = $event['Event']['id'];
				}
			}

			$people = $this->Person->find ('all', array(
					'conditions' => array(
						array('Person.id IN (SELECT DISTINCT person_id FROM registrations WHERE event_id IN (' . implode (',', $current) . ') AND payment = "Paid")'),
						array("Person.id NOT IN (SELECT secondary_id FROM activity_logs WHERE type = 'email_membership_letter' AND primary_id = $year)"),
					),
			));

			$emailed = 0;
			$activity = array();
			foreach ($people as $person) {
				// Send the email
				$variables = array(
					'%fullname' => $person['Person']['full_name'],
					'%firstname' => $person['Person']['first_name'],
					'%lastname' => $person['Person']['last_name'],
					'%year' => $year
				);
				if ($this->_sendMail (array (
						'to' => $person,
						'config_subject' => 'member_letter_subject',
						'config_body' => 'member_letter_body',
						'variables' => $variables,
						'sendAs' => 'text',
				)))
				{
					$activity[] = array(
						'type' => 'email_membership_letter',
						'primary_id' => $year,
						'secondary_id' => $person['Person']['id'],
					);
					++ $emailed;
				}
			}

			$this->set(compact ('emailed'));
			// Update the activity log
			$log = ClassRegistry::init ('ActivityLog');
			$log->saveAll ($activity);
		}
	}
}
?>
