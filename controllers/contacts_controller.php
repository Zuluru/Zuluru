<?php
class ContactsController extends AppController {

	var $name = 'Contacts';
	var $uses = array('Contact', 'Message', 'Person');
	var $components = array('Email');

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'message',
		)))
		{
			return true;
		}

		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				return true;
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
					'delete',
			)))
			{
				// If an upload type id is specified, check if we're a manager of that upload type's affiliate
				$type = $this->_arg('type');
				if ($type) {
					if (in_array($this->Contact->affiliate($type), $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		if (!Configure::read('feature.contacts')) {
			$this->Session->setFlash(__('Contact management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();
		$this->set(compact('affiliates', 'affiliate'));

		$this->paginate = array('Contact' => array(
				'conditions' => array('Contact.affiliate_id' => $affiliates),
				'contain' => array('Affiliate'),
				'order' => array('Affiliate.name', 'Contact.name'),
				'limit' => Configure::read('feature.items_per_page'),
		));

		$this->set('contacts', $this->paginate());
	}

	function add() {
		if (!Configure::read('feature.contacts')) {
			$this->Session->setFlash(__('Contact management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty($this->data)) {
			$this->Contact->create();
			if ($this->Contact->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('contact', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('contact', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->data['Contact']['affiliate_id']);
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		if (!Configure::read('feature.contacts')) {
			$this->Session->setFlash(__('Contact management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('contact');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('contact', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Contact->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('contact', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('contact', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Contact->affiliate($id));
			}
		}
		if (empty($this->data)) {
			$this->Contact->contain ();
			$this->data = $this->Contact->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('contact', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($this->data['Contact']['affiliate_id']);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function delete() {
		if (!Configure::read('feature.contacts')) {
			$this->Session->setFlash(__('Contact management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$id = $this->_arg('contact');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('contact', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Contact->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Contact', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Contact', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function message() {
		if (!Configure::read('feature.contacts')) {
			$this->Session->setFlash(__('Contact management is disabled on this site.', true), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		if (!empty($this->data)) {
			$this->Message->set($this->data);
			if ($this->Message->validates()) {
				$this->Contact->contain(false);
				$contact = $this->Contact->read(null, $this->data['Message']['contact_id']);

				if ($contact && $this->_sendMail (array (
						'to' => "{$contact['Contact']['name']} <{$contact['Contact']['email']}>",
						'replyTo' => $this->UserCache->read('Person'),
						'cc' => ($this->data['Message']['cc'] ? $this->UserCache->read('Person') : array()),
						'subject' => $this->data['Message']['subject'],
						'content' => $this->data['Message']['message'],
						'sendAs' => 'text',
				)))
				{
					$this->Session->setFlash(__('Your message has been sent.', true), 'default', array('class' => 'success'));
					$this->redirect('/');
				}

				$this->Session->setFlash(__('Error sending email.', true), 'default', array('class' => 'error'));
			}
		}

		$id = $this->_arg('contact');
		if (!$id) {
			$affiliates = $this->_applicableAffiliateIDs();
			$contacts = $this->Contact->find('all', array(
					'conditions' => array('Contact.affiliate_id' => $affiliates),
					'contain' => array('Affiliate'),
					'order' => array('Affiliate.name', 'Contact.name'),
			));
			if (empty($contacts)) {
				$this->Session->setFlash(__('No contacts have been set up yet on this site.', true), 'default', array('class' => 'info'));
				$this->redirect('/');
			} else if (count($contacts) == 1) {
				$this->set('contact', $contacts[0]);
			} else {
				if (count($affiliates) > 1) {
					$names = array();
					foreach ($contacts as $contact) {
						$names[$contact['Affiliate']['name']][$contact['Contact']['id']] = $contact['Contact']['name'];
					}
					$contacts = $names;
				} else {
					$contacts = Set::combine($contacts, '{n}.Contact.id', '{n}.Contact.name');
				}
				$this->set(compact('contacts'));
			}
		} else {
			$this->Contact->contain('Affiliate');
			$contact = $this->Contact->read(null, $id);
			if (!$contact) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('contact', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->set(compact('contact'));
		}
	}
}
?>