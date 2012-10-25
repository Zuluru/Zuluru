<?php
class RegionsController extends AppController {

	var $name = 'Regions';

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if (!$affiliate) {
					// If there's no affiliate id, this is a top-level operation that all managers can perform
					return true;
				} else if (in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
					return true;
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'view',
					'edit',
					'delete',
			)))
			{
				// If a region id is specified, check if we're a manager of that region's affiliate
				$region = $this->_arg('region');
				if ($region) {
					if (in_array($this->Region->affiliate($region), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->set('regions', $this->Region->find('all', array(
				'conditions' => array('affiliate_id' => $affiliates),
				'contain' => 'Affiliate',
				'order' => array('Affiliate.name', 'Region.name'),
		)));
	}

	function view() {
		$id = $this->_arg('region');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Region->contain(array('Affiliate', 'Facility'));
		$region = $this->Region->read(null, $id);
		if (!$region) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($region['Region']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('region', 'affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Region->create();
			if ($this->Region->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('region', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('region', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Region']['affiliate_id']);
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('region');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Region->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('region', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('region', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Region->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Region->contain();
			$this->data = $this->Region->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Region']['affiliate_id']);
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));
	}

	function delete() {
		$id = $this->_arg('region');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Region->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this region, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Region->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Region', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Region', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

}
?>