<?php
class FranchisesController extends AppController {

	var $name = 'Franchises';

	function publicActions() {
		return array('index', 'letter', 'view');
	}

	function isAuthorized() {
		if (!Configure::read('feature.franchises')) {
			return false;
		}

		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'add',
		)))
		{
			return true;
		}

		// People can perform these operations on franchises they run
		if (in_array ($this->params['action'], array(
				'edit',
				'delete',
				'add_owner',
				'remove_team',
		)))
		{
			// If a franchise id is specified, check if we're the owner of that franchise
			$franchise = $this->_arg('franchise');
			if ($franchise && in_array ($franchise, $this->Session->read('Zuluru.FranchiseIDs'))) {
				return true;
			}
		}

		// People can perform these operations on teams they run
		if (in_array ($this->params['action'], array(
				'add_team',
		)))
		{
			// If a franchise id is specified, check if we're the owner of that franchise
			$franchise = $this->_arg('franchise');
			if ($franchise && in_array ($franchise, $this->Session->read('Zuluru.FranchiseIDs'))) {
				// If no team id is specified, or if we're the owner of the specified team, we can proceed
				$team = $this->_arg('team');
				if (!$team || in_array ($team, $this->Session->read('Zuluru.OwnedTeamIDs'))) {
					return true;
				}
			}
		}

		return false;
	}

	function index() {
		$this->paginate = array(
			'contain' => array('Person'),
		);
		$this->set('franchises', $this->paginate());
		$this->set('letters', $this->Franchise->find('all', array(
				'fields' => array('DISTINCT SUBSTR(Franchise.name, 1, 1) AS letter'),
				'order' => 'letter',
				// Grouping necessary because Cake adds Franchise.id to the query, so we get
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

		$this->set(compact('letter'));
		$this->set('franchises', $this->Franchise->find('all', array(
				'contain' => array('Person'),
				'conditions' => array(
					'Franchise.name LIKE' => "$letter%",
				),
				'order' => array('Franchise.name'),
		)));
		$this->set('letters', $this->Franchise->find('all', array(
				'fields' => array('DISTINCT SUBSTR(Franchise.name, 1, 1) AS letter'),
				'order' => 'letter',
				// Grouping necessary because Cake adds Franchise.id to the query, so we get
				// "DISTINCT letter, id", which is more results than just "DISTINCT letter"
				'group' => 'letter',
		)));
	}

	function view() {
		$id = $this->_arg('franchise');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Franchise->contain (array(
			'Team' => array('Division' => 'League'),
			'Person',
		));

		$franchise = $this->Franchise->read(null, $id);
		if ($franchise === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->set('franchise', $franchise);
	}

	function add() {
		if (!empty($this->data)) {
			$this->Franchise->create();
			$this->data['Person'] = array($this->Auth->User('id'));
			if ($this->Franchise->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('franchise', true)), 'default', array('class' => 'success'));
				$this->_deleteFranchiseSessionData();
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('franchise', true)), 'default', array('class' => 'warning'));
			}
		}

		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('franchise');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Franchise->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('franchise', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('franchise', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Franchise->read(null, $id);
		}
	}

	function delete() {
		$id = $this->_arg('franchise');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		$dependencies = $this->Franchise->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this franchise, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Franchise->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Franchise', true)), 'default', array('class' => 'success'));
			$this->_deleteFranchiseSessionData();
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Franchise', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function add_team() {
		$id = $this->_arg('franchise');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Franchise->contain('Team');
		$franchise = $this->Franchise->read(null, $id);
		if ($franchise === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact('franchise'));
		$existing_team_ids = Set::extract ('/Team/id', $franchise);

		$this->Person->contain (array (
			'Team' => array(
				'Division' => 'League',
				'conditions' => array(
					'TeamsPerson.position' => Configure::read('privileged_roster_positions'),
					'NOT' => array(
						'Team.id' => $existing_team_ids,
					),
				),
				'order' => 'Team.id desc',
			),
		));
		$teams = $this->Person->read(null, $this->Auth->User('id'));

		if ($this->data) {
			if (in_array($this->data['team_id'], $existing_team_ids)) {
				$this->Session->setFlash(__('That team is already part of this franchise', true), 'default', array('class' => 'info'));
			}
			else if (!in_array($this->data['team_id'], Set::extract('/Team/id', $teams))) {
				$this->Session->setFlash(__('You are not a captain, assistant captain or coach of the selected team', true), 'default', array('class' => 'info'));
			}
			else {
				if ($this->Franchise->FranchisesTeam->save(array(
						'franchise_id' => $id,
						'team_id' => $this->data['team_id'],
				)))
				{
					$this->Session->setFlash(__('The selected team has been added to this franchise', true), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'view', 'franchise' => $id));
				} else {
					$this->Session->setFlash(__('Failed to add the selected team to this franchise', true), 'default', array('class' => 'warning'));
				}
			}
		}

		$this->set(compact('teams'));
	}

	function remove_team() {
		$id = $this->_arg('franchise');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->Franchise->contain('Team');
		$franchise = $this->Franchise->read(null, $id);
		if ($franchise === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$team_id = $this->_arg('team');
		if (!$team_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'franchise' => $id));
		}

		$existing_team_ids = Set::extract ('/Team/id', $franchise);
		if (!in_array($team_id, $existing_team_ids)) {
			$this->Session->setFlash(__('That team is not part of this franchise', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'franchise' => $id));
		}

		$this->Franchise->Team->contain('Franchise');
		$team = $this->Franchise->Team->read(null, $team_id);
		if ($team === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('team', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'franchise' => $id));
		}

		if (count($team['Franchise']) == 1) {
			$this->Session->setFlash(__('All teams must be members of at least one franchise. Before you can remove this team from this franchise, you must first add it to another one.', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'franchise' => $id));
		}

		if ($this->Franchise->FranchisesTeam->deleteAll(array(
				'franchise_id' => $id,
				'team_id' => $team_id,
		)))
		{
			// If this was the only team in the franchise, delete the franchise too
			if (count($franchise['Team']) == 1) {
				if ($this->Franchise->delete ($id)) {
					$this->Session->setFlash(__('The selected team has been removed from this franchise.', true) . ' ' .
							__('As there were no other teams in the franchise, it has been deleted as well.', true), 'default', array('class' => 'success'));
					$this->_deleteFranchiseSessionData();
					$this->redirect('/');
				} else {
					$this->Session->setFlash(__('The selected team has been removed from this franchise.', true) . ' ' .
							__('There are no other teams in the franchise, but deletion of the franchise failed.', true), 'default', array('class' => 'warning'));
				}
			} else {
				$this->Session->setFlash(__('The selected team has been removed from this franchise.', true), 'default', array('class' => 'success'));
			}
		} else {
			$this->Session->setFlash(__('Failed to remove the selected team from this franchise.', true), 'default', array('class' => 'warning'));
		}

		$this->redirect(array('action' => 'view', 'franchise' => $id));
	}

	function add_owner() {
		$id = $this->_arg('franchise');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$franchise = $this->Franchise->read(null, $id);
		if ($franchise === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('franchise', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('franchise'));

		$person_id = $this->_arg('person');
		if ($person_id != null) {
			$this->Franchise->Person->contain(array('Franchise' => array('conditions' => array('Franchise.id' => $id))));
			$person = $this->Franchise->Person->read(null, $person_id);
			if (!empty ($person['Franchise'])) {
				$this->Session->setFlash(__("{$person['Person']['full_name']} is already an owner of this franchise", true), 'default', array('class' => 'info'));
			} else {
				$franchise['Person'] = Set::extract ('/Person/id', $franchise);
				$franchise['Person'][] = $person['Person']['id'];
				if ($this->Franchise->saveAll ($franchise)) {
					$this->Session->setFlash(__("Added {$person['Person']['full_name']} as owner", true), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'view', 'franchise' => $id));
				} else {
					$this->Session->setFlash(__("Failed to add {$person['Person']['full_name']} as owner", true), 'default', array('class' => 'warning'));
				}
			}
		}

		$params = $url = $this->_extractSearchParams();
		unset ($params['franchise']);
		unset ($params['person']);
		if (!empty($params)) {
			$test = trim (@$params['first_name'], ' *') . trim (@$params['last_name'], ' *');
			if (strlen ($test) < 2) {
				$this->set('short', true);
			} else {
				// This pagination needs the model at the top level
				$this->Person = $this->Franchise->Person;
				$this->_mergePaginationParams();
				$this->paginate['Person'] = array(
					'conditions' => $this->_generateSearchConditions($params, 'Person'),
					'contain' => false,
					'limit' => Configure::read('feature.items_per_page'),
				);
				$this->set('people', $this->paginate('Person'));
			}
		}
		$this->set(compact('url'));
	}
}
?>
