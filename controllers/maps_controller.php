<?php
class MapsController extends AppController {

	var $name = 'Maps';
	var $uses = array('Field');

	function publicActions() {
		return array('index', 'view');
	}

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
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

		return false;
	}

	function index() {
		if ($this->is_admin) {
			$closed = $this->_arg('closed');
		} else {
			$closed = false;
		}
		$field_conditions = array('Field.latitude !=' => NULL);
		if (!$closed) {
			$field_conditions['Field.is_open'] = true;
		}

		$affiliates = $this->_applicableAffiliateIDs();
		$region_conditions = array('Region.affiliate_id' => $affiliates);

		$regions = $this->Field->Facility->Region->find('all', array(
			'contain' => array(
				'Facility' => array(
					'conditions' => array(
						'Facility.is_open' => true,
					),
					'order' => 'Facility.name',
					'Field' => array('conditions' => $field_conditions),
				),
				'Affiliate',
			),
			'conditions' => $region_conditions,
			'order' => 'Region.id',
		));

		$this->set(compact('regions', 'closed', 'affiliates'));

		$this->layout = 'map';
	}

	function view() {
		$id = $this->_arg('field');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		$this->Field->contain (array (
			'Facility' => array(
				'Field' => array('conditions' => array(
					'Field.id !=' => $id,
					'Field.is_open' => true,
				)),
				'Region',
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}
		if (!$field['Field']['latitude']) {
			$this->Session->setFlash(sprintf(__('That %s has not yet been laid out.', true), Configure::read('ui.field')), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($field['Facility']['Region']['affiliate_id']);

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
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}

		if (!empty ($this->data)) {
			if ($this->Field->saveAll($this->data['Field']) && $this->Field->Facility->save($this->data['Facility'])) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), sprintf(__('%s layout', true), Configure::read('ui.field'))), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'maps', 'action' => 'view', 'field' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), sprintf(__('%s layout', true), Configure::read('ui.field'))), 'default', array('class' => 'warning'));
			}
		}

		$this->Field->contain (array (
			'Facility' => array(
				'Field' => array('conditions' => array(
					'Field.id !=' => $id,
					'Field.is_open' => true,
					'Field.latitude !=' => null,
				)),
				'Region',
			),
		));

		$field = $this->Field->read(null, $id);
		if (!$field) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __(Configure::read('ui.field'), true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'fields', 'action' => 'index'));
		}
		$this->Configuration->loadAffiliate($field['Facility']['Region']['affiliate_id']);

		// We use these as last-ditch emergency values, if the field has neither
		// a valid lat/long or an address that Google can find.
		$leaguelat = Configure::read('organization.latitude');
		$leaguelng = Configure::read('organization.longitude');
		if (empty($leaguelat) || empty($leaguelng)) {
			$this->Session->setFlash(__('Before using the layout editor, you must set the default latitude and longitude for your organization.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'settings', 'action' => 'organization'));
		}

		$this->set(compact('field', 'leaguelat', 'leaguelng'));

		$this->layout = 'map';
	}
}
?>
