<?php
/**
 * Component for helping with cached user data.
 */

class UserCacheComponent extends Object
{
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] =& new UserCacheComponent();
			$instance[0]->initializeData();
		}
		return $instance[0];
	}

	function initializeData() {
		$self =& UserCacheComponent::getInstance();
		$self->my_id = null;
		$self->other_id = null;
		$self->data = array();
	}

	function initialize(&$controller) {
		$self =& UserCacheComponent::getInstance();
		$self->_controller =& $controller;
		$self->initializeData();
	}

	function initializeId() {
		if ($this->my_id) {
			return;
		}

		// If this is the home page, and "act as" is temporary, we reset it.
		if ($this->_controller->here == '/' && $this->_controller->Session->check('Zuluru.act_as_temporary')) {
			$this->_controller->Session->delete('Zuluru.act_as_id');
			$this->_controller->Session->delete('Zuluru.act_as_temporary');
		}

		// We must have the my_id variable set, or else later $this->read calls go recursive
		$acting_as = $this->_controller->Session->read('Zuluru.act_as_id');
		if ($acting_as) {
			$this->my_id = $acting_as;
		} else {
			$this->my_id = $this->_controller->Auth->user('zuluru_person_id');
		}

		if ($this->my_id) {
			// Check for a temporary "act as" request.
			$act_as = $this->_controller->_arg('act_as');
			if ($act_as) {
				if ($act_as == $this->_controller->Auth->user('zuluru_person_id')) {
					$this->_controller->Session->delete('Zuluru.act_as_id');
					$this->_controller->Session->delete('Zuluru.act_as_temporary');
					$this->my_id = $this->_controller->Auth->user('zuluru_person_id');
				} else {
					$this->data[$this->my_id] = array();
					$relatives = $this->allActAs();
					$groups = $this->read('GroupIDs');
					if ($act_as == $acting_as || array_key_exists($act_as, $relatives) || in_array(GROUP_ADMIN, $groups)) {
						$this->_controller->Session->write('Zuluru.act_as_id', $act_as);
						$this->_controller->Session->write('Zuluru.act_as_temporary', true);
						unset($this->data[$this->my_id]);
						$this->my_id = $act_as;
					} else {
						$this->_controller->Session->setFlash(__('You do not have permission to act as that person.', true), 'default', array('class' => 'warning'));
						$this->_controller->redirect('/');
					}
				}
			}

			$this->data[$this->my_id] = array();
		}
	}

	function currentId() {
		$self =& UserCacheComponent::getInstance();
		if ($self->_controller->name == 'CakeError') {
			return null;
		}
		$self->initializeId();
		return $self->my_id;
	}

	function realId() {
		$self =& UserCacheComponent::getInstance();
		if ($self->_controller->name == 'CakeError') {
			return null;
		}
		return $self->_controller->Auth->user('zuluru_person_id');
	}

	function read($key, $id = null, $internal = false) {
		$self =& UserCacheComponent::getInstance();
		$self->initializeId();
		if (!$id) {
			$id = $self->my_id;
			if (!$id) {
				return ($internal ? false : array());
			}
		}

		// We always have our own id as a key in the data array, so if
		// the new key doesn't exist, we'll throw away anything we might
		// have had before, so that we only keep one other user's data
		// in the memory cache. This prevents massive memory usage.
		if (!array_key_exists($id, $self->data)) {
			if ($self->other_id) {
				unset($self->data[$self->other_id]);
			}
			$self->other_id = $id;
			$self->data[$id] = array();
		}

		if (strpos($key, '.') !== false) {
			list($key, $subkey) = explode('.', $key);
		} else {
			$subkey = null;
		}

		if (array_key_exists($key, $self->data[$id])) {
			if ($internal) {
				return true;
			} else if ($subkey) {
				return $self->data[$id][$key][$subkey];
			} else {
				return $self->data[$id][$key];
			}
		}

		$self->data[$id] = Cache::read("person/$id", 'file');
		if (!$self->data[$id]) {
			$self->data[$id] = array();
		}

		// Find any data that we don't already have cached
		if (!array_key_exists($key, $self->data[$id])) {
			switch ($key) {
				case 'Affiliates':
					if (!isset($self->_controller->Affiliate)) {
						$self->_controller->Affiliate = ClassRegistry::init('Affiliate');
					}
					$self->data[$id][$key] = $self->_controller->Affiliate->readByPlayerId($id);

					// If affiliates are disabled, make sure that they are in affiliate 1
					if (empty($self->data[$id][$key]) && !Configure::read('feature.affiliates')) {
						$self->_controller->Affiliate->AffiliatesPerson->save(array('person_id' => $id, 'affiliate_id' => 1));
						$self->data[$id][$key] = $self->_controller->Affiliate->readByPlayerId($id);
					}
					break;

				case 'AffiliateIDs':
					if ($self->read('Affiliates', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Affiliate/id', $self->data[$id]['Affiliates']);
					}
					break;

				case 'AllOwnedTeamIDs':
					if (!isset($self->_controller->Team)) {
						$self->_controller->Team = ClassRegistry::init('Team');
					}
					$self->data[$id][$key] = $self->_controller->Team->TeamsPerson->find('list', array(
							'conditions' => array(
								'TeamsPerson.person_id' => $id,
								'TeamsPerson.role' => Configure::read('privileged_roster_roles'),
							),
							'fields' => array('TeamsPerson.team_id'),
					));
					break;

				case 'AllRelativeTeamIDs':
					$relatives = $this->read('RelativeIDs', $id);
					if (!empty($relatives)) {
						if (!isset($self->_controller->Team)) {
							$self->_controller->Team = ClassRegistry::init('Team');
						}
						$self->data[$id][$key] = $self->_controller->Team->TeamsPerson->find('list', array(
								'conditions' => array('TeamsPerson.person_id' => $relatives),
								'fields' => array('TeamsPerson.team_id'),
						));
					}
					break;

				case 'AllTeamIDs':
					if (!isset($self->_controller->Team)) {
						$self->_controller->Team = ClassRegistry::init('Team');
					}
					$self->data[$id][$key] = $self->_controller->Team->TeamsPerson->find('list', array(
							'conditions' => array('TeamsPerson.person_id' => $id),
							'fields' => array('TeamsPerson.team_id'),
					));
					break;

				case 'Credits':
					if (!isset($self->_controller->Credit)) {
						$self->_controller->Credit = ClassRegistry::init('Credit');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Credit, array(
							'contain' => array(),
							'conditions' => array(
								'person_id' => $id,
								'amount != amount_used',
							),
					));
					break;

				case 'Divisions':
					if (!isset($self->_controller->Division)) {
						$self->_controller->Division = ClassRegistry::init('Division');
					}
					$self->data[$id][$key] = $self->_controller->Division->readByPlayerId($id, true, true);
					break;

				case 'DivisionIDs':
					if ($self->read('Divisions', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Division/id', $self->data[$id]['Divisions']);
					}
					break;

				case 'Documents':
					if (!isset($self->_controller->Upload)) {
						$self->_controller->Upload = ClassRegistry::init('Upload');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Upload, array(
							'contain' => array('UploadType'),
							'conditions' => array(
								'person_id' => $id,
								'type_id !=' => null,
							),
					));
					break;

				case 'Franchises':
					if (!isset($self->_controller->Franchise)) {
						$self->_controller->Franchise = ClassRegistry::init('Franchise');
					}
					$self->data[$id][$key] = $self->_controller->Franchise->readByPlayerId($id, true, true);
					break;

				case 'FranchiseIDs':
					if ($self->read('Franchises', $id, true)) {
						$self->data[$id][$key] = Set::extract('/id', $self->data[$id]['Franchises']);
					}
					break;

				case 'Groups':
					if (!isset($self->_controller->Person)) {
						$self->_controller->Person = ClassRegistry::init('Person');
					}
					if ($self->read('Person', $id, true)) {
						if (!empty($self->data[$id]['Person']['Group'])) {
							$self->data[$id][$key] = $self->data[$id]['Person']['Group'];
						} else {
							$self->data[$id][$key] = array();
						}
					}
					break;

				case 'GroupIDs':
					if ($self->read('Groups', $id, true)) {
						$self->data[$id][$key] = Set::extract('/id', $self->data[$id]['Groups']);
					}
					break;

				case 'ManagedAffiliates':
					if ($self->read('Affiliates', $id, true)) {
						$self->data[$id][$key] = Set::extract('/AffiliatesPerson[position=manager]/..', $self->data[$id]['Affiliates']);
					}
					break;

				case 'ManagedAffiliateIDs':
					if ($self->read('ManagedAffiliates', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Affiliate/id', $self->data[$id]['ManagedAffiliates']);
					}
					break;

				case 'OwnedTeams':
					if ($self->read('Teams', $id, true)) {
						$roles = Configure::read('privileged_roster_roles');
						$self->data[$id][$key] = array();
						foreach ($self->data[$id]['Teams'] as $team) {
							if (in_array($team['TeamsPerson']['role'], $roles) &&
								$team['TeamsPerson']['status'] == ROSTER_APPROVED)
							{
								$self->data[$id][$key][] = $team;
							}
						}
					}
					break;

				case 'OwnedTeamIDs':
					if ($self->read('OwnedTeams', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Team/id', $self->data[$id]['OwnedTeams']);
					}
					break;

				case 'Person':
					if (!isset($self->_controller->Person)) {
						$self->_controller->Person = ClassRegistry::init('Person');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Person, $id, array($self->_controller->Auth->authenticate->name, 'Group'));
					break;

				case 'Preregistrations':
					if (!isset($self->_controller->Preregistration)) {
						$self->_controller->Preregistration = ClassRegistry::init('Preregistration');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Preregistration, array(
							'contain' => array(
								'Event',
							),
							'conditions' => array(
								'person_id' => $id,
							),
					));
					break;

				case 'Registrations':
					if (!isset($self->_controller->Registration)) {
						$self->_controller->Registration = ClassRegistry::init('Registration');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Registration, array(
							'order' => 'created DESC',
							'contain' => array(
								'Event' => array('EventType', 'Price'),
								'Price',
							),
							'conditions' => array(
								'person_id' => $id,
							),
					));
					break;

				case 'RegistrationsCanPay':
					if ($self->read('Registrations', $id, true)) {
						$self->data[$id][$key] = array();
						foreach (Configure::read('registration_delinquent') as $payment) {
							$self->data[$id][$key] = array_merge($self->data[$id][$key],
									Set::extract("/Registration[payment=$payment]/..", $self->data[$id]['Registrations']));
						}
					}
					break;

				case 'RegistrationsPaid':
					if ($self->read('Registrations', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Registration[payment=Paid]/..', $self->data[$id]['Registrations']);
					}
					break;

				case 'RegistrationsUnpaid':
					if ($self->read('Registrations', $id, true)) {
						$self->data[$id][$key] = array();
						foreach (Configure::read('registration_unpaid') as $payment) {
							$self->data[$id][$key] = array_merge($self->data[$id][$key],
									Set::extract("/Registration[payment=$payment]/..", $self->data[$id]['Registrations']));
						}
					}
					break;

				case 'RelatedTo':
					if (!isset($self->_controller->Person)) {
						$self->_controller->Person = ClassRegistry::init('Person');
					}

					$config = new DATABASE_CONFIG;
					$prefix = $self->_controller->Auth->authenticate->tablePrefix;
					if ($self->_controller->Auth->authenticate->useDbConfig != 'default') {
						$config_name = $self->_controller->Auth->authenticate->useDbConfig;
						$config = $config->$config_name;
						$prefix = "{$config['database']}.$prefix";
					}

					$self->data[$id][$key] = $self->_findData($self->_controller->Person->Relative, array(
							'contain' => false,
							'fields' => array('Relative.*', 'PeoplePerson.*', "{$self->_controller->Auth->authenticate->name}.*"),
							'joins' => array(
								array(
									'table' => "{$self->_controller->Person->tablePrefix}people_people",
									'alias' => 'PeoplePerson',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'Relative.id = PeoplePerson.person_id',
								),
								array(
									'table' => "$prefix{$self->_controller->Auth->authenticate->useTable}",
									'alias' => $self->_controller->Auth->authenticate->name,
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => "{$self->_controller->Auth->authenticate->name}.{$self->_controller->Auth->authenticate->primaryKey} = Relative.user_id",
								),
							),
							'conditions' => array(
								'PeoplePerson.relative_id' => $id,
							),
					));
					break;

				case 'RelatedToIDs':
					if ($self->read('RelatedTo', $id, true)) {
						$self->data[$id][$key] = Set::extract('/PeoplePerson[approved=1]/../Relative/id', $self->data[$id]['RelatedTo']);
					}
					break;

				case 'Relatives':
					if (!isset($self->_controller->Person)) {
						$self->_controller->Person = ClassRegistry::init('Person');
					}

					$config = new DATABASE_CONFIG;
					$prefix = $self->_controller->Auth->authenticate->tablePrefix;
					if ($self->_controller->Auth->authenticate->useDbConfig != 'default') {
						$config_name = $self->_controller->Auth->authenticate->useDbConfig;
						$config = $config->$config_name;
						$prefix = "{$config['database']}.$prefix";
					}

					$self->data[$id][$key] = $self->_findData($self->_controller->Person->Relative, array(
							'contain' => false,
							'fields' => array('Relative.*', 'PeoplePerson.*', "{$self->_controller->Auth->authenticate->name}.*"),
							'joins' => array(
								array(
									'table' => "{$self->_controller->Person->tablePrefix}people_people",
									'alias' => 'PeoplePerson',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'Relative.id = PeoplePerson.relative_id',
								),
								array(
									'table' => "$prefix{$self->_controller->Auth->authenticate->useTable}",
									'alias' => $self->_controller->Auth->authenticate->name,
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => "{$self->_controller->Auth->authenticate->name}.{$self->_controller->Auth->authenticate->primaryKey} = Relative.user_id",
								),
							),
							'conditions' => array(
								'PeoplePerson.person_id' => $id,
							),
					));
					break;

				case 'RelativeIDs':
					if ($self->read('Relatives', $id, true)) {
						$self->data[$id][$key] = Set::extract('/PeoplePerson[approved=1]/../Relative/id', $self->data[$id]['Relatives']);
					}
					break;

				case 'RelativeTeamIDs':
					if ($self->read('Relatives', $id, true)) {
						$self->data[$id][$key] = array();
						foreach ($self->data[$id]['Relatives'] as $relative) {
							$self->data[$id][$key] = array_merge($self->data[$id][$key], $self->read('TeamIDs', $relative['Relative']['id']));
						}
						$self->data[$id][$key] = array_unique($self->data[$id][$key]);
					}
					break;

				case 'Skills':
					if (!isset($self->_controller->Skill)) {
						$self->_controller->Skill = ClassRegistry::init('Skill');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Skill, array(
							'order' => 'Skill.sport',
							'contain' => false,
							'conditions' => array(
								'person_id' => $id,
							),
					));
					break;

				case 'Tasks':
					$self->data[$id][$key] = $self->requestAction(array('controller' => 'tasks', 'action' => 'assigned'), array('named' => array('person' => $id)));
					break;

				case 'Teams':
					if (!isset($self->_controller->Team)) {
						$self->_controller->Team = ClassRegistry::init('Team');
					}
					$self->data[$id][$key] = $self->_controller->Team->readByPlayerId($id);
					break;

				case 'TeamIDs':
					if ($self->read('Teams', $id, true)) {
						$self->data[$id][$key] = Set::extract('/Team/id', $self->data[$id]['Teams']);
					}
					break;

				case 'User':
					$self->read('Person', $id, true);
					break;

				case 'Waivers':
					if (!isset($self->_controller->Waiver)) {
						$self->_controller->Waiver = ClassRegistry::init('Waiver');
					}
					$self->data[$id][$key] = $self->_findData($self->_controller->Waiver, array(
							'contain' => false,
							'fields' => array('Waiver.*', 'WaiversPerson.*'),
							'joins' => array(
								array(
									'table' => "{$self->_controller->Waiver->tablePrefix}waivers_people",
									'alias' => 'WaiversPerson',
									'type' => 'LEFT',
									'foreignKey' => false,
									'conditions' => 'Waiver.id = WaiversPerson.waiver_id',
								),
							),
							'conditions' => array(
								'WaiversPerson.person_id' => $id,
							),
					));
					break;

				case 'WaiversCurrent':
					if ($self->read('Waivers', $id, true)) {
						$date = date('Y-m-d');
						$self->data[$id][$key] = Set::extract("/WaiversPerson[valid_from<=$date][valid_until>=$date]/..", $self->data[$id]['Waivers']);
					}
					break;

				default:
					trigger_error("Read $key", E_USER_ERROR);
			}

			// Make sure that anything empty is an array, as that's what everything will want.
			if (empty($self->data[$id][$key])) {
				$self->data[$id][$key] = array();
			}
			Cache::write("person/$id", $self->data[$id], 'file');
		}

		if (!$self->data[$id][$key]) {
			return ($internal ? false : array());
		} else if ($internal) {
			return true;
		} else if ($subkey) {
			return $self->data[$id][$key][$subkey];
		} else {
			return $self->data[$id][$key];
		}
	}

	function clear($key, $id = null) {
		$self =& UserCacheComponent::getInstance();
		$self->initializeId();
		if (!$id) {
			$id = $self->my_id;
			if (!$id) {
				return;
			}
		}

		if (empty($self->data[$id])) {
			$self->data[$id] = Cache::read("person/$id", 'file');
			if (empty($self->data[$id])) {
				$self->data[$id] = array();
			}
		}

		if (strpos($key, '.') !== false) {
			list($key, $subkey) = explode('.', $key);
		} else {
			$subkey = null;
		}

		if (!array_key_exists($key, $self->data[$id]) || (!empty($subkey) && !array_key_exists($subkey, $self->data[$id][$key]))) {
			return;
		}

		if ($subkey) {
			unset($self->data[$id][$key][$subkey]);
		} else {
			unset($self->data[$id][$key]);
		}

		Cache::write("person/$id", $self->data[$id], 'file');
	}

	function allActAs($for_menu = false, $field = 'full_name') {
		$act_as = array();
		if (!$this->currentId()) {
			return $act_as;
		}

		$include = array($this->currentId() => true);

		// If we're acting as someone, maybe add the real user and their relatives
		if ($this->currentId() != $this->realId()) {
			if (in_array($this->realId(), $this->read('RelatedToIDs'))) {
				// If the user is a relative, assume it's a parent acting as a child or similar
				$include[$this->realId()] = true;
			} else if (AppController::_isChild($this->read('Person.birthdate'))) {
				// Otherwise, assume it's an admin, and if it's a youth account, find the first parent
				$related = $this->read('RelatedToIDs');
				if (!empty($related)) {
					$include[min($related)] = true;
				}
			}
		}

		if (!$for_menu) {
			// If this is not for a menu, we want the real user last, if not already in the list.
			// This will put admins last when acting as someone else.
			$include[$this->realId()] = true;
		}

		// Add the included user and their relatives relatives
		foreach (array_keys($include) as $id) {
			$act_as[$id] = $this->read("Person.$field", $id);
			$relatives = $this->read('Relatives', $id);
			foreach ($relatives as $relative) {
				if ($relative['PeoplePerson']['approved']) {
					$act_as[$relative['Relative']['id']] = $relative['Relative'][$field];
				}
			}
		}

		// And finally remove the current user, if present; they get special treatment everywhere
		unset($act_as[$this->currentId()]);

		return $act_as;
	}

	function _findData(&$model, $find, $contain = array()) {
		if (is_numeric($find)) {
			$model->contain($contain);
			$data = $model->read(null, $find);
			$return = $data[$model->alias];
			foreach ($contain as $c) {
				if (!empty($data[$c])) {
					$return[$c] = $data[$c];
				}
			}
		} else {
			$return = $model->find('all', $find);
		}

		// We don't want this data hanging around in $model->data to mess up later saves
		$model->data = null;

		return $return;
	}

	/**
	 * Delete all of the cached information related to teams.
	 */
	function _deleteTeamData($id = null) {
		$this->clear('Teams', $id);
		$this->clear('TeamIDs', $id);
		$this->clear('OwnedTeams', $id);
		$this->clear('OwnedTeamIDs', $id);
	}

	/**
	 * Delete all of the cached information related to franchises.
	 */
	function _deleteFranchiseData($id = null) {
		$this->clear('Franchises', $id);
		$this->clear('FranchiseIDs', $id);
	}
}