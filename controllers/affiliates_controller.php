<?php
class AffiliatesController extends AppController {

	var $name = 'Affiliates';

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'select',
				'view_all',
		)))
		{
			return true;
		}

		// Anyone that's logged in can perform these operations, but only through internal requests
		if (in_array ($this->params['action'], array(
				'index',
		)))
		{
			return (array_key_exists('requested', $this->params) && $this->params['requested']);
		}

		return false;
	}

	function index() {
		if ($this->is_admin && empty($this->params['requested'])) {
			$conditions = array();
			$contain = array('Person' => array('conditions' => array('AffiliatesPerson.position' => 'manager')));
		} else {
			$conditions = array('active' => true);
			$contain = array();
		}
		$affiliates = $this->Affiliate->find('all', compact('contain', 'conditions'));
		if (!empty($this->params['requested'])) {
			return $affiliates;
		}
		$this->set(compact('affiliates'));
	}

	function view() {
		$id = $this->_arg('affiliate');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Affiliate->contain(array(
				'Person' => array('conditions' => array('AffiliatesPerson.position' => 'manager')),
		));
		$this->set('affiliate', $this->Affiliate->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Affiliate->create();
			if ($this->Affiliate->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('affiliate', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('affiliate', true)), 'default', array('class' => 'warning'));
			}
		}
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('affiliate');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($id);
		if (!empty($this->data)) {
			if ($this->Affiliate->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('affiliate', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('affiliate', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->Affiliate->contain(array());
			$this->data = $this->Affiliate->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
		}
	}

	function delete() {
		$id = $this->_arg('affiliate');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Affiliate->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this affiliate, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Affiliate->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Affiliate', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Affiliate', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function add_manager() {
		$id = $this->_arg('affiliate');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Affiliate->contain(array('Person' => array('conditions' => array('AffiliatesPerson.position' => 'manager'))));
		$affiliate = $this->Affiliate->read(null, $id);
		if (!$affiliate) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set(compact('affiliate'));

		$person_id = $this->_arg('person');
		if ($person_id != null) {
			$this->Affiliate->Person->contain(array('Affiliate' => array('conditions' => array('Affiliate.id' => $id))));
			$person = $this->Affiliate->Person->read(null, $person_id);
			if (!empty ($person['Affiliate']) && $person['Affiliate'][0]['AffiliatesPerson']['position'] == 'manager') {
				$this->Session->setFlash(__("{$person['Person']['full_name']} is already a manager of this affiliate", true), 'default', array('class' => 'info'));
			} else {
				$join = ClassRegistry::init('AffiliatesPerson');
				if (!empty ($person['Affiliate'])) {
					$success = $join->updateAll(array('position' => '"manager"'), array('affiliate_id' => $id, 'person_id' => $person_id));
				} else {
					$success = $join->save(array('affiliate_id' => $id, 'person_id' => $person_id, 'position' => 'manager'));
				}
				if ($success) {
					$this->Session->setFlash(__("Added {$person['Person']['full_name']} as manager", true), 'default', array('class' => 'success'));
					$this->redirect(array('action' => 'view', 'affiliate' => $id));
				} else {
					$this->Session->setFlash(__("Failed to add {$person['Person']['full_name']} as manager", true), 'default', array('class' => 'warning'));
				}
			}
		}

		$params = $url = $this->_extractSearchParams();
		unset ($params['affiliate']);
		unset ($params['person']);
		$this->_handlePersonSearch($params, $url, $this->Affiliate->Person,
				array('Group.name' => array('Manager', 'Administrator')));
	}

	function remove_manager() {
		$id = $this->_arg('affiliate');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('affiliate', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$person_id = $this->_arg('person');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'view', 'affiliate' => $id));
		}

		$join = ClassRegistry::init('AffiliatesPerson');
		if ($join->updateAll(array('position' => '"player"'), array('affiliate_id' => $id, 'person_id' => $person_id))) {
			$this->Session->setFlash(__('Successfully removed manager', true), 'default', array('class' => 'success'));
		} else {
			$this->Session->setFlash(__('Failed to remove manager!', true), 'default', array('class' => 'warning'));
		}
		$this->redirect(array('action' => 'view', 'affiliate' => $id));
	}

	function unmanaged() {
		if (!Configure::read('feature.affiliates')) {
			return array();
		}

		$affiliates = $this->Affiliate->find('all', array(
			'conditions' => array(
				'Affiliate.active' => true,
			),
			'contain' => array('Person' => array('conditions' => array('AffiliatesPerson.position' => 'manager'))),
		));
		foreach ($affiliates as $key => $affiliate) {
			if (!empty($affiliate['Person'])) {
				unset($affiliates[$key]);
			} else {
				unset($affiliates[$key]['Person']);
			}
		}
		return $affiliates;
	}

	function select() {
		if (!empty($this->data)) {
			$this->Session->write('Zuluru.CurrentAffiliate', $this->data['Affiliate']['affiliate']);
			$this->redirect('/');
		}
		$affiliates = $this->Affiliate->find('list', array(
				'contain' => array(),
				'conditions' => array('active' => true),
		));
		$this->set(compact('affiliates'));
	}

	function view_all() {
		$this->Session->delete('Zuluru.CurrentAffiliate');
		$this->redirect('/');
	}
}
?>