<?php
class FieldsController extends AppController {

	var $name = 'Fields';

	function publicActions() {
		return array('index', 'view', 'tooltip');
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'bookings',
		)))
		{
			return true;
		}

		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'add',
			)))
			{
				// If a facility id is specified, check if we're a manager of that facility's affiliate
				$facility = $this->_arg('facility');
				if ($facility) {
					if (in_array($this->Field->Facility->affiliate($facility), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			if (in_array ($this->params['action'], array(
					'edit',
					'open',
					'close',
					'delete',
					'bookings',
			)))
			{
				// If a field id is specified, check if we're a manager of that field's affiliate
				$field = $this->_arg('field');
				if ($field) {
					if (in_array($this->Field->affiliate($field), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}
	}

	// This is here to support the many links to this page that are out there
	function index() {
		$this->redirect(array('controller' => 'facilities'));
	}

	function view() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		$this->Field->contain(array(
			'Facility' => array(
				'Region',
				'Field' => array(
					'conditions' => array(
						'Field.id !=' => $id,
						'Field.is_open' => true,
					),
					'order' => 'Field.num',
				),
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($field['Facility']['Region']['affiliate_id']);

		$this->set(compact ('field'));

		$this->_addFieldMenuItems ($this->Field->data);
	}

	function tooltip() {
		$id = $this->_arg('field');
		if (!$id) {
			return;
		}
		$this->Field->contain(array(
			'Facility' => array(
				'Region',
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			return;
		}
		$this->Configuration->loadAffiliate($field['Facility']['Region']['affiliate_id']);

		$this->set(compact ('field'));

		Configure::write ('debug', 0);
		$this->layout = 'ajax';
	}

	function add() {
		$id = $this->_arg('facility');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		if (!empty($this->data)) {
			$this->Field->create();

			if ($this->Field->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'edit', 'field' => $this->Field->id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'warning'));
				$this->data = $this->Field->_afterFind($this->data);
				$this->Configuration->loadAffiliate($this->Field->Facility->affiliate($id));
			}
		} else {
			$this->Field->Facility->contain(array('Region'));
			$this->data = $this->Field->Facility->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
			}
			$this->data['Field'] = array('facility_id' => $id);
			$this->Configuration->loadAffiliate($this->data['Region']['affiliate_id']);
		}
		$this->set('add', true);

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Field->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'warning'));
				$this->data = $this->Field->_afterFind($this->data);
				$this->Configuration->loadAffiliate($this->Field->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Field->contain(array('Facility' => 'Region'));
			$this->data = $this->Field->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
				$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Facility']['Region']['affiliate_id']);
		}

		$this->_addFieldMenuItems ($this->data);
	}

	function open() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('field');
		$name = $this->Field->field('name', array('id' => $id));

		$success = $this->Field->updateAll (array('is_open' => true), array('Field.id' => $id));
		$this->set(compact('id', 'success', 'name'));
	}

	function close() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('field');
		$name = $this->Field->field('name', array('id' => $id));

		$success = $this->Field->updateAll (array('is_open' => 0), array('Field.id' => $id));
		$this->set(compact('id', 'success', 'name'));
	}

	function delete() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		$dependencies = $this->Field->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(sprintf(__('The following records reference this %s, so it cannot be deleted.', true), Configure::read('ui.field')) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Field->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __(Configure::read('ui.field_cap'), true)), 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __(Configure::read('ui.field_cap'), true)), 'default', array('class' => 'warning'));
		$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
	}

	function bookings() {
		$id = $this->_arg('field');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		// TODO: Is there a better condition to use? Some divisions wrap around a year boundary.
		// Maybe get the Availability table involved?
		$this->Field->contain (array (
			'Facility' => 'Region',
			'GameSlot' => array(
				'Game' => array(
					'conditions' => array(
						'OR' => array(
							'Game.home_dependency_type !=' => 'copy',
							'Game.home_dependency_type' => null,
						),
					),
					'Division' => 'League',
				),
				'order' => 'GameSlot.game_date, GameSlot.game_start',
				'conditions' => 'YEAR(GameSlot.game_date) >= YEAR(NOW())',
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'facilities', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($field['Facility']['Region']['affiliate_id']);
		$this->set(compact ('field'));
	}
}
?>
