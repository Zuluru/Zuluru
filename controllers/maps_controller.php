<?php
class MapsController extends AppController {

	var $name = 'Maps';
	var $uses = array('Field');

	function index() { // TODO
	}

	function view() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		$this->Field->contain (array (
			'Facility' => array(
				'Field' => array('conditions' => array(
					'Field.id !=' => $id,
					'Field.is_open' => true,
				)),
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field['Field']['length']) {
			$this->Session->setFlash(__('That field has not yet been laid out.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		$home_addr = '';
		if ($this->Auth->user()) {
			$home_addr = $this->Session->read('Zuluru.Person.addr_street') . ', ' .
						$this->Session->read('Zuluru.Person.addr_city') . ', ' .
						$this->Session->read('Zuluru.Person.addr_prov');
		}
		$this->set(compact('field', 'home_addr'));

		$this->layout = 'map';
	}

	function edit() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		if (!empty ($this->data)) {
			if ($this->Field->saveAll($this->data['Field']) && $this->Field->Facility->save($this->data['Facility'])) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('field layout', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'maps', 'action' => 'view', 'field' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('field layout', true)), 'default', array('class' => 'warning'));
			}
		}

		$this->Field->contain (array (
			'Facility' => array(
				'Field' => array('conditions' => array(
					'Field.id !=' => $id,
					'Field.is_open' => true,
					'Field.latitude !=' => null,
				)),
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		$this->set(compact('field'));

		$this->layout = 'map';
	}
}
?>
