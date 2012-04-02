<?php
class HolidaysController extends AppController {

	var $name = 'Holidays';

	function index() {
		$this->Holiday->recursive = 0;
		$this->set('holidays', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid holiday', true), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('holiday', $this->Holiday->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Holiday->create();
			if ($this->Holiday->save($this->data)) {
				$this->Session->setFlash(__('The holiday has been saved', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The holiday could not be saved. Please, try again.', true), 'default', array('class' => 'warning'));
			}
		}
	}

	function edit($id = null) {
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
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Holiday->read(null, $id);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for holiday', true), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Holiday->delete($id)) {
			$this->Session->setFlash(__('Holiday deleted', true), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Holiday was not deleted', true), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}
}
