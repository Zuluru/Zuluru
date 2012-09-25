<?php
class FacilitiesController extends AppController {

	var $name = 'Facilities';

	function publicActions() {
		return array('index', 'view');
	}

	function index() {
		$this->set('regions', $this->Facility->Region->find('all', array(
			'contain' => array(
				'Facility' => array(
					'conditions' => array(
						'Facility.is_open' => true,
					),
					'order' => 'Facility.name',
					'Field' => array(
						'conditions' => array(
							'Field.is_open' => true,
						),
						'order' => 'Field.num',
					),
				),
			),
			'order' => 'Region.id',
		)));
		$this->set('facilities_with_fields', $this->Facility->Field->find('list', array(
			'fields' => array('facility_id', 'id'),
		)));
		$this->set('closed', false);
	}

	function closed() {
		$this->set('regions', $this->Facility->Region->find('all', array(
			'contain' => array(
				'Facility' => array(
					'order' => 'Facility.name',
					'Field' => array(
						'conditions' => array(
							'Field.is_open' => 0,
						),
						'order' => 'Field.num',
					),
				),
			),
			'order' => 'Region.id',
		)));
		$this->set('facilities_with_fields', $this->Facility->Field->find('list', array(
			'fields' => array('facility_id', 'id'),
		)));
		$this->set('closed', true);
		$this->render ('index');
	}

	function view() {
		$id = $this->_arg('facility');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Facility->contain (array (
			'Region',
			'Field' => array(
				'conditions' => $this->is_admin ? array() : array('Field.is_open' => true),
				'order' => 'Field.is_open DESC, Field.num'
			),
		));

		$facility = $this->Facility->read(null, $id);
		if ($facility === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$this->set(compact ('facility'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Facility->create();

			if ($this->Facility->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('facility', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('facility', true)), 'default', array('class' => 'warning'));
			}
		}
		$regions = $this->Facility->Region->find('list');
		$this->set(compact('regions'));
		$this->_loadAddressOptions();
		$this->set('add', true);
		$this->set('region', $this->_arg('region'));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('facility');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Facility->save($this->data)) {
				if (!$this->data['Facility']['is_open']) {
					$this->Facility->Field->updateAll (array('is_open' => 0), array('Field.facility_id' => $id));
				}
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('facility', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('facility', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->Facility->contain();
			$this->data = $this->Facility->read(null, $id);
		}
		$regions = $this->Facility->Region->find('list');
		$this->set(compact('regions'));
		$this->_loadAddressOptions();

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function open() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Facility->field('name', array('id' => $facility));

		$success = $this->Facility->updateAll (array('is_open' => true), array('Facility.id' => $facility));
		$this->set(compact('success', 'name'));
	}

	function close() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Facility->field('name', array('id' => $facility));

		$success = $this->Facility->updateAll (array('is_open' => 0), array('Facility.id' => $facility)) &&
				$this->Facility->Field->updateAll (array('is_open' => 0), array('Field.facility_id' => $facility));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('facility');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		$dependencies = $this->Facility->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this facility, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Facility->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Facility', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Facility', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}
}
?>
