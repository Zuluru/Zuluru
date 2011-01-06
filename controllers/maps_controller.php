<?php
class MapsController extends AppController {

	var $name = 'Maps';
	var $uses = array('Field');

	function index() { // TODO
	}

	function view() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid field', true));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		$this->Field->contain (array (
			'ParentField',
		));

		$field = $this->Field->read(null, $id);
		if (!$field['Field']['length']) {
			$this->Session->setFlash(__('That field has not yet been laid out.', true));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}
		$field['SiteFields'] = $this->Field->readAtSite ($id, $field['Field']['parent_id'], array('Field.length >' => 0));

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
			$this->Session->setFlash(__('Invalid field', true));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		if (!empty ($this->data)) {
			if ($this->Field->save($this->data)) {
				$this->Session->setFlash(__('The field layout has been saved', true));
				$this->redirect(array('controller' => 'maps', 'action' => 'view', 'field' => $id));
			} else {
				$this->Session->setFlash(__('The field layout could not be saved. Please correct the errors and try again.', true));
			}
		}

		$this->Field->contain (array (
			'ParentField',
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(__('Invalid field', true));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}
		$field['SiteFields'] = $this->Field->readAtSite ($id, $field['Field']['parent_id'], array('Field.length >' => 0));

		$this->set(compact('field'));

		$this->layout = 'map';
	}
}
?>
