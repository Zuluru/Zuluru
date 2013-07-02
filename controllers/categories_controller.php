<?php
class CategoriesController extends AppController {

	var $name = 'Categories';

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
				// If a category id is specified, check if we're a manager of that category's affiliate
				$category = $this->_arg('category');
				if ($category) {
					if (in_array($this->Category->affiliate($category), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
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

		$this->set('categories', $this->Category->find('all', array(
				'conditions' => array('affiliate_id' => $affiliates),
				'contain' => 'Affiliate',
				'order' => array('Affiliate.name', 'Category.name'),
		)));
	}

	function view() {
		$id = $this->_arg('category');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('category', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Category->contain(array('Affiliate', 'Task' => array('Person')));
		$category = $this->Category->read(null, $id);
		if (!$category) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('category', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($category['Category']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('category', 'affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Category->create();
			if ($this->Category->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('category', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('category', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Category']['affiliate_id']);
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('category');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('category', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Category->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('category', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('category', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Category->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Category->contain();
			$this->data = $this->Category->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('category', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Category']['affiliate_id']);
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));
	}

	function delete() {
		$id = $this->_arg('category');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('category', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Category->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this category, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Category->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Category', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Category', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

}
?>