<?php
class MailingListsController extends AppController {

	var $name = 'MailingLists';

	var $paginate = array(
		'contain' => array('Affiliate'),
	);

	function publicActions() {
		return array('unsubscribe');
	}

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'add',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if (!$affiliate) {
					// If there's no affiliate id, this is a top-level operation that all managers can perform
					return true;
				} else if (in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
					return true;
				}
			}

			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'view',
					'edit',
					'delete',
			)))
			{
				// If a list id is specified, check if we're a manager of that list's affiliate
				$list = $this->_arg('mailing_list');
				if ($list) {
					if (in_array($this->MailingList->affiliate($list), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
						return true;
					}
				}
			}
		}

		return false;
	}

	function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate['conditions'] = array(
			'MailingList.affiliate_id' => $affiliates,
		);
		$this->set('mailingLists', $this->paginate());
		$this->set(compact('affiliates'));
	}

	function view() {
		$id = $this->_arg('mailing_list');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('mailing list', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->MailingList->contain(array('Affiliate', 'Newsletter'));
		$this->set('mailingList', $this->MailingList->read(null, $id));

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->MailingList->create();
			if ($this->MailingList->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('mailing list', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('mailing list', true)), 'default', array('class' => 'warning'));
			}
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set('add', true);
		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('mailing_list');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('mailing list', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->MailingList->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('mailing list', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('mailing list', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->MailingList->read(null, $id);
		}

		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	function delete() {
		$id = $this->_arg('mailing_list');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('mailing list', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->MailingList->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this mailing list, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->MailingList->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Mailing list', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Mailing list', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function unsubscribe() {
		$list_id = $this->_arg('list');
		if (!$list_id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('mailing list', true)), 'default', array('class' => 'info'));
			$this->redirect('/');
		}

		$person_id = $this->_arg('person');
		$my_id = $this->Auth->user('id');
		if (!$person_id) {
			$person_id = $my_id;
			if (!$person_id) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('player', true)), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->_arg('code');
		if ($code || !$my_id) {
			// Authenticate the hash code
			$hash = $this->_hash($person_id, $list_id);
			if ($hash != $code) {
				$this->Session->setFlash(__('The authorization code is invalid.', true), 'default', array('class' => 'warning'));
				$this->redirect('/');
			}
		}

		// Check for subscription records
		$person = $this->MailingList->Subscription->find('first', array(
				'conditions' => array(
					'mailing_list_id' => $list_id,
					'person_id' => $person_id,
				),
		));
		if ($person) {
			if (!$person['Subscription']['subscribed']) {
				$this->Session->setFlash(__('You are not subscribed to this mailing list.', true), 'default', array('class' => 'info'));
				$this->redirect('/');
			}
			$this->MailingList->Subscription->id = $person['Subscription']['id'];
			$success = $this->MailingList->Subscription->save(array('subscribed' => 0));
		} else {
			$success = $this->MailingList->Subscription->save(array(
					'mailing_list_id' => $list_id,
					'person_id' => $person_id,
					'subscribed' => 0,
			));
		}
		if ($success) {
			$this->Session->setFlash(__('You have successfully unsubscribed from this mailing list. Note that you may still be on other mailing lists for this site, and some emails (e.g. roster, attendance and score reminders) cannot be opted out of.', true), 'default', array('class' => 'success'));
			$this->redirect('/');
		}
		$this->Session->setFlash(__('There was an error unsubscribing you from this mailing list. Please try again soon, or contact your system administrator.', true), 'default', array('class' => 'error'));
		$this->redirect('/');
	}

	function _hash ($person, $list) {
		// Build a string of the inputs
		$input = "$person:$list";
		return md5($input . ':' . Configure::read('Security.salt'));
	}
}
?>
