<?php
class RegionsController extends AppController {

	var $name = 'Regions';

	function index() {
		$this->set('regions', $this->Region->find('all', array(
				'contain' => array(),
				'order' => array('Region.name'),
		)));
	}

	function view() {
		$id = $this->_arg('region');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('region', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Region->contain(array('Facility'));
		$this->set('region', $this->Region->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Region->create();
			if ($this->Region->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('region', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('region', true)), 'default', array('class' => 'warning'));
			}
		}
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
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Region->read(null, $id);
		}
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
