<?php
class PricesController extends AppController {
	var $name = 'Prices';
	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'add',
			)))
			{
				// If an event id is specified, check if we're a manager of that event's affiliate
				$event = $this->_arg('event');
				if ($event) {
					if (in_array($this->Event->affiliate($event), $this->UserCache->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'delete',
			)))
			{
				// If a price id is specified, check if we're a manager of that price's affiliate
				$price = $this->_arg('price');
				if ($price) {
					if (in_array($this->Price->affiliate($price), $this->UserCache->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}
		return false;
	}

	function add() {
		$id = $this->_arg('event');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Price->Event->contain (array());
		$event = $this->Price->Event->read(null, $id);
		if (!$event) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('event', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$this->Configuration->loadAffiliate($event['Event']['affiliate_id']);

		if (!empty($this->data)) {
			$this->Price->create();
			if ($this->Price->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('price point', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'events', 'action' => 'view', 'event' => $id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('price point', true)), 'default', array('class' => 'warning'));
				$this->data['Event'] = $event['Event'];
			}
		} else {
			$this->data = $event;
		}
		$this->set(compact('event'));
		$this->set('add', true);

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('price');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('price point', true)), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Price->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('price point', true)), 'default', array('class' => 'success'));
				$this->redirect(array('controller' => 'events', 'action' => 'view', 'event' => $this->data['Price']['event_id']));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('price point', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Price->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Price->contain('Event');
			$this->data = $this->Price->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('price point', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->Configuration->loadAffiliate($this->data['Event']['affiliate_id']);
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete() {
		$id = $this->_arg('price');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('price point', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}
		$dependencies = $this->Price->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this price, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect('/');
		}

		// Don't delete the last price on an event
		$event_id = $this->Price->field('event_id', array('Price.id' => $id));
		$prices = $this->Price->find('count', array(
				'conditions' => array('Price.event_id' => $event_id),
		));
		if ($prices < 2) {
			$this->Session->setFlash(__('You cannot delete the only price point on an event.', true), 'default', array('class' => 'info'));
			$this->redirect(array('controller' => 'events', 'action' => 'view', 'event' => $event_id));
		}

		if ($this->Price->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Price point', true)), 'default', array('class' => 'success'));
			$this->redirect(array('controller' => 'events', 'action' => 'view', 'event' => $event_id));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Price point', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('controller' => 'events', 'action' => 'view', 'event' => $event_id));
	}

}
?>