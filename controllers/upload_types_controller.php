<?php
class UploadTypesController extends AppController {

	var $name = 'UploadTypes';

	function index() {
		if (!Configure::read('feature.documents')) {
			$this->Session->setFlash(__('Document management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$this->set('uploadTypes', $this->UploadType->find('all'));
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
		));
		$this->set('uploadType', $this->UploadType->read(null, $id));
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
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team', true)), 'default', array('class' => 'warning'));
			}
		}

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
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('team', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->UploadType->read(null, $id);
		}
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