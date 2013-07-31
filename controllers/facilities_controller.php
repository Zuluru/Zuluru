<?php
class FacilitiesController extends AppController {

	var $name = 'Facilities';

	function publicActions() {
		return array('index', 'view');
	}

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'add',
					'closed',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'open',
					'close',
					'delete',
			)))
			{
				// If a facility id is specified, check if we're a manager of that facility's affiliate
				$facility = $this->_arg('facility');
				if ($facility) {
					if (in_array($this->Facility->affiliate($facility), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		if (empty($this->params['requested'])) {
			$affiliates = $this->_applicableAffiliateIDs();
		} else {
			$affiliates = $this->_applicableAffiliateIDs(true);
		}

		$regions = $this->Facility->Region->find('all', array(
			'conditions' => array('Region.affiliate_id' => $affiliates),
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
				'Affiliate',
			),
			'order' => 'Region.id',
		));

		if (!empty($this->params['requested'])) {
			return $regions;
		}

		$this->set('facilities_with_fields', $this->Facility->Field->find('list', array(
			'fields' => array('facility_id', 'id'),
		)));
		$this->set('closed', false);
		$this->set(compact('affiliates', 'regions'));
	}

	function closed() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->set('regions', $this->Facility->Region->find('all', array(
			'conditions' => array('Region.affiliate_id' => $affiliates),
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
				'Affiliate',
			),
			'order' => 'Region.id',
		)));
		$this->set('facilities_with_fields', $this->Facility->Field->find('list', array(
			'fields' => array('facility_id', 'id'),
		)));
		$this->set('closed', true);
		$this->set(compact('affiliates'));
		$this->render('index');
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
				'order' => 'Field.is_open DESC, Field.num'
			),
		));

		$facility = $this->Facility->read(null, $id);
		if (!$facility) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($facility['Region']['affiliate_id']);

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
				$this->Configuration->loadAffiliate($this->Facility->Region->affiliate($this->data['Facility']['region_id']));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$regions = $this->Facility->Region->find('all', array(
				'conditions' => array('Region.affiliate_id' => array_keys($affiliates)),
				'contain' => array('Affiliate'),
				'order' => array('Affiliate.name', 'Region.name'),
		));
		if (empty($regions)) {
			$this->Session->setFlash(__('You must first create at least one region for facilities to be located in.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		} else if (count($affiliates) > 1) {
			$region_list = array();
			foreach ($regions as $key => $region) {
				$region_list[$region['Affiliate']['name']][$region['Region']['id']] = $region['Region']['name'];
			}
			$regions = $region_list;
		} else {
			$regions = Set::combine($regions, '{n}.Region.id', '{n}.Region.name');
		}
		$this->set(compact('regions', 'affiliates'));
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
				$this->Configuration->loadAffiliate($this->Facility->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Facility->contain('Region');
			$this->data = $this->Facility->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('facility', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Region']['affiliate_id']);
		}

		$affiliates = $this->_applicableAffiliates(true);
		$regions = $this->Facility->Region->find('list', array(
				'conditions' => array('Region.affiliate_id' => array_keys($affiliates)),
		));
		$this->set(compact('regions', 'affiliates'));
		$this->_loadAddressOptions();
		$this->set('region', $this->_arg('region'));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function open() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('facility');
		$name = $this->Facility->field('name', array('id' => $id));

		$success = $this->Facility->updateAll (array('is_open' => true), array('Facility.id' => $id));
		$this->set(compact('id', 'success', 'name'));
	}

	function close() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		$id = $this->_arg('facility');
		$name = $this->Facility->field('name', array('id' => $id));

		$success = $this->Facility->updateAll (array('is_open' => 0), array('Facility.id' => $id)) &&
				$this->Facility->Field->updateAll (array('is_open' => 0), array('Field.facility_id' => $id));
		$this->set(compact('id', 'success', 'name'));
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
