<?php
class HolidaysController extends AppController {

	var $name = 'Holidays';

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'delete',
			)))
			{
				// If a holiday id is specified, check if we're a manager of that holiday's affiliate
				$holiday = $this->_arg('holiday');
				if ($holiday) {
					if (in_array($this->Holiday->affiliate($holiday), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$this->Holiday->recursive = 0;
		$this->paginate['conditions'] = array('Holiday.affiliate_id' => $this->_applicableAffiliateIDs(true));
		$this->set('holidays', $this->paginate());
	}

	function add() {
		if (!empty($this->data)) {
			$this->Holiday->create();
			if ($this->Holiday->save($this->data)) {
				$this->Session->setFlash(__('The holiday has been saved', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The holiday could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Holiday']['affiliate_id']);
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render('edit');
	}

	function edit() {
		$id = $this->_arg('holiday');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid holiday', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Holiday->save($this->data)) {
				$this->Session->setFlash(__('The holiday has been saved', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The holiday could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Holiday->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Holiday->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(__('Invalid holiday', true), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Holiday']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function delete() {
		$id = $this->_arg('holiday');
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for holiday', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Holiday->delete($id)) {
			$this->Session->setFlash(__('Holiday deleted', true), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Holiday was not deleted', true), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}
}
