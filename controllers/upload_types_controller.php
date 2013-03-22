<?php
class UploadTypesController extends AppController {

	var $name = 'UploadTypes';

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
					'view',
					'edit',
					'delete',
			)))
			{
				// If an upload type id is specified, check if we're a manager of that upload type's affiliate
				$type = $this->_arg('type');
				if ($type) {
					if (in_array($this->UploadType->affiliate($type), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set('uploadTypes', $this->UploadType->find('all', array(
				'conditions' => array('UploadType.affiliate_id' => $this->_applicableAffiliateIDs(true)),
				'contain' => array(),
		)));
	}

	function view() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('type');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('upload type', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->UploadType->contain(array(
				'Upload' => array('Person'),
				'Affiliate',
		));
		$uploadType = $this->UploadType->read(null, $id);
		if (!$uploadType) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('upload type', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($uploadType['UploadType']['affiliate_id']);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('uploadType', 'affiliates'));
	}

	function add() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty($this->data)) {
			$this->UploadType->create();
			if ($this->UploadType->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('upload type', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('upload type', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['UploadType']['affiliate_id']);
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('type');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('upload type', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->UploadType->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('upload type', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('upload type', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->UploadType->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->UploadType->contain();
			$this->data = $this->UploadType->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('upload type', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['UploadType']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function delete() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('type');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('upload type', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->UploadType->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this upload type, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->UploadType->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Upload type', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Upload type', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}
}
?>