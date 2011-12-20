<?php
class FieldsController extends AppController {

	var $name = 'Fields';
	var $helpers = array('TinyMce.TinyMce');

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'bookings',
		)))
		{
			return true;
		}
	}

	function index() {
		$this->_readFields(true);
		$this->set('closed', false);
	}

	function closed() {
		$this->_readFields(false);
		$this->set('closed', true);
		$this->render ('index');
	}

	function _readFields($open) {
		$this->Field->contain (array (
			'Region' => array('fields' => array('id', 'name')),
		));
		// TODO: this open/closed crap STILL doesn't work everywhere!
		// Fix it with a revamp of the entire 'fields' database schema.
		$this->set('fields', $this->Field->find('all', array(
			'conditions' => array(
				'is_open' => $open,
				'parent_id' => null,
			),
			'fields' => array('id', 'name', 'is_open'),
			'order' => 'Field.region_id, Field.name',
		)));
	}

	function view() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Field->contain (array (
			'ParentField' => array(
				'Region',
			),
			'Region',
		));

		$field = $this->Field->read(null, $id);
		if ($field === false) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$field['SiteFields'] = $this->Field->readAtSite ($id, $field['Field']['parent_id']);

		$this->set(compact ('field'));

		$this->_addFieldMenuItems ($this->Field->data);
	}

	function add() {
		if (!empty($this->data)) {
			$this->Field->create();

			// If there's a parent field given, the name and code can be skipped
			if (!empty ($this->data['Field']['parent_id'])) {
				unset ($this->Field->validate['name']);
				unset ($this->Field->validate['code']);
			}

			if ($this->Field->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('field', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'edit', 'field' => $this->Field->id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('field', true)), 'default', array('class' => 'warning'));
			}
		}
		$parents = $this->Field->ParentField->find('list', array(
				'conditions' => array(
					'parent_id' => null,
					'is_open' => true,
				),
				'order' => 'name',
		));
		$regions = $this->Field->Region->find('list');
		$this->set(compact('parents', 'regions'));
		$this->_loadAddressOptions();
	}

	function edit() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Field->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('field', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('field', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Field->read(null, $id);
		}
		$parents = $this->Field->ParentField->find('list', array(
				'conditions' => array(
					'parent_id' => null,
					'is_open' => true,
				),
				'order' => 'name',
		));
		$regions = $this->Field->Region->find('list');
		$this->set(compact('parents', 'regions'));
		$this->_loadAddressOptions();

		$this->_addFieldMenuItems ($this->Field->data);
	}

	function open() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Field->field('name', array('id' => $field));

		$success = $this->Field->updateAll (array('is_open' => true), array('OR' => array(
				'Field.id' => $field,
				'Field.parent_id' => $field,
		)));
		$this->set(compact('success', 'name'));
	}

	function close() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Field->field('name', array('id' => $field));

		$success = $this->Field->updateAll (array('is_open' => 0), array('OR' => array(
				'Field.id' => $field,
				'Field.parent_id' => $field,
		)));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}

		// TODO Handle deletions
		$this->Session->setFlash(__('Deletions are not currently supported', true), 'default', array('class' => 'info'));
		$this->redirect('/');

		if ($this->Field->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Field', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Field', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function bookings() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('field', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action'=>'index'));
		}
		// TODO: Is there a better condition to use? Some leagues wrap around a year boundary.
		// Maybe get the Availability table involved?
		$this->Field->contain (array (
			'ParentField',
			'GameSlot' => array(
				'Game' => array(
					'League',
				),
				'order' => 'GameSlot.game_date, GameSlot.game_start',
				'conditions' => 'YEAR(GameSlot.game_date) >= YEAR(NOW())',
			),
		));

		$field = $this->Field->read(null, $id);
		$this->set(compact ('field'));
	}
}
?>
