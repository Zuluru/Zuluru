<?php
class WaiversController extends AppController {

	var $name = 'Waivers';
	var $paginate = array(
		'contain' => array('Affiliate'),
		'order' => array('Affiliate.name', 'Waiver.id'),
	);

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'sign',
				'review',
		)))
		{
			return true;
		}

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
					'view',
					'edit',
					'delete',
			)))
			{
				// If a waiver id is specified, check if we're a manager of that waiver's affiliate
				$waiver = $this->_arg('waiver');
				if ($waiver) {
					if (in_array($this->Waiver->affiliate($waiver), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate['conditions'] = array('Waiver.affiliate_id' => $affiliates);
		$this->set('waivers', $this->paginate());
		$this->set(compact('affiliates'));
	}

	function view() {
		$id = $this->_arg('waiver');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Waiver->contain('Affiliate');
		$waiver = $this->Waiver->read(null, $id);
		if (!$waiver) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($waiver['Waiver']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('waiver', 'affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Waiver->create();
			if ($this->Waiver->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('waiver', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('waiver', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Waiver']['affiliate_id']);
			}
		}
		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('waiver');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$can_edit_text = ($this->Waiver->dependencies($id) === false);
		$this->set(compact('can_edit_text'));

		if (!empty($this->data)) {
			if (array_key_exists('text', $this->data['Waiver']) && !$can_edit_text) {
				$this->Session->setFlash(__('This waiver has already been signed, so for legal reasons the text cannot be edited.', true), 'default', array('class' => 'warning'));
				$this->redirect(array('action' => 'index'));
			}

			if ($this->Waiver->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('waiver', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('waiver', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Waiver->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Waiver->contain(array());
			$this->data = $this->Waiver->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Waiver']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete() {
		$id = $this->_arg('waiver');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Waiver->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this waiver, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Waiver->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Waiver', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Waiver', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function sign() {
		$waiver_id = $this->_arg('waiver');
		if (!$waiver_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->Waiver->contain(array());
		$waiver = $this->Waiver->read (null, $waiver_id);
		if (!$waiver || !$waiver['Waiver']['active']) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($waiver['Waiver']['affiliate_id']);

		// Make sure they're waivering for a valid date
		$date = $this->_arg('date');
		if (!$date || !$this->Waiver->canSign($date)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver date', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$person_id = $this->Auth->user('id');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Waiver->Person->contain('Waiver');
		$person = $this->Waiver->Person->read(null, $person_id);

		// Check if they have already signed this waiver
		if ($this->Waiver->signed($date, $person['Waiver'])) {
			$this->Session->setFlash(__('You have already accepted this waiver', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		list ($valid_from, $valid_until) = $this->Waiver->validRange($date);
		if ($valid_from === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver date', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty ($this->data)) {
			if ($this->data['Person']['signed'] == 'yes') {
				if ($this->Waiver->WaiversPerson->save (compact('person_id', 'waiver_id', 'valid_from', 'valid_until')))
				{
					$this->Session->setFlash(__('Waiver signed.', true), 'default', array('class' => 'success'));
					$this->redirect('/');
				} else {
					$this->Session->setFlash(__('Failed to save the waiver.', true), 'default', array('class' => 'warning'));
				}
			} else {
				$this->Session->setFlash(__('Sorry, you may only proceed by agreeing to the waiver.', true), 'default', array('class' => 'warning'));
			}
		}

		$this->set(compact('person', 'waiver', 'date', 'valid_from', 'valid_until'));
	}

	function review() {
		$waiver_id = $this->_arg('waiver');
		if (!$waiver_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Waiver->contain();
		$waiver = $this->Waiver->read(null, $waiver_id);
		if (!$waiver) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($waiver['Waiver']['affiliate_id']);
		$conditions = array('Waiver.id' => $waiver_id);

		$date = $this->_arg('date');
		if ($date) {
			list ($valid_from, $valid_until) = $this->Waiver->validRange($date);
			if ($valid_from === false) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('waiver date', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$conditions['valid_from <='] = $date;
			$conditions['valid_until >='] = $date;
		} else {
			list ($valid_from, $valid_until) = $this->Waiver->validRange(date('Y-m-d'));
		}

		$person_id = $this->Auth->user('id');
		if (!$person_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('person', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$order = array('WaiversPerson.created' => 'DESC');
		$this->Waiver->Person->contain(array('Waiver' => compact('conditions', 'order')));
		$person = $this->Waiver->Person->read(null, $person_id);

		$this->set(compact('person', 'waiver', 'date', 'valid_from', 'valid_until'));
	}
}
?>