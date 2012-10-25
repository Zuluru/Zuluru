<?php
class NewslettersController extends AppController {

	var $name = 'Newsletters';
	var $uses = array('Newsletter', 'Person');
	var $components = array('Email', 'Lock');

	var $paginate = array(
		'order' => array('target' => 'DESC'),
		'contain' => array('MailingList' => 'Affiliate'),
	);

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations
			if (in_array ($this->params['action'], array(
					'index',
					'past',
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
					'send',
					'delivery',
					'delete',
			)))
			{
				// If a newsletter id is specified, check if we're a manager of that newsletter's affiliate
				$newsletter = $this->_arg('newsletter');
				if ($newsletter) {
					if (in_array($this->Newsletter->affiliate($newsletter), $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
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
			'target >= DATE_ADD(CURDATE(), INTERVAL -30 DAY)',
			'MailingList.affiliate_id' => $affiliates,
		);

		$this->set('newsletters', $this->paginate('Newsletter'));
		$this->set('current', true);
		$this->set(compact('affiliates'));
	}

	function past() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate['conditions'] = array(
			'MailingList.affiliate_id' => $affiliates,
		);

		$this->set('newsletters', $this->paginate('Newsletter'));
		$this->set('current', false);
		$this->set(compact('affiliates'));
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('newsletter');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Newsletter->contain('MailingList');
		$newsletter = $this->Newsletter->read(null, $id);
		if (!$newsletter) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($newsletter['MailingList']['affiliate_id']);
		$this->set(compact('newsletter'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Newsletter->create();
			if ($this->Newsletter->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('newsletter', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('newsletter', true)), 'default', array('class' => 'warning'));
				$this->Configuration->loadAffiliate($this->Newsletter->MailingList->affiliate($this->data['Newsletter']['mailing_list_id']));
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$mailingLists = $this->Newsletter->MailingList->find('all', array(
				'conditions' => array('MailingList.affiliate_id' => $affiliates),
				'contain' => array('Affiliate'),
				'order' => array('Affiliate.name', 'MailingList.name'),
		));
		if (count($affiliates) > 1) {
			$names = array();
			foreach ($mailingLists as $mailingList) {
				$names[$mailingList['Affiliate']['name']][$mailingList['MailingList']['id']] = $mailingList['MailingList']['name'];
			}
			$mailingLists = $names;
		} else {
			$mailingLists = Set::combine($mailingLists, '{n}.MailingList.id', '{n}.MailingList.name');
		}
		$this->set(compact('mailingLists'));

		$this->set('add', true);

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->render ('edit');
	}

	function edit() {
		$id = $this->_arg('newsletter');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Newsletter->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('newsletter', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('newsletter', true)), 'default', array('class' => 'warning'));
			}
		}
		if (empty($this->data)) {
			$this->Newsletter->contain('MailingList');
			$this->data = $this->Newsletter->read(null, $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$affiliate = $this->data['MailingList']['affiliate_id'];
		} else {
			$affiliate = $this->Newsletter->affiliate($id);
		}
		$this->Configuration->loadAffiliate($affiliate);
		$this->set('mailingLists', $this->Newsletter->MailingList->find('list', array(
				'conditions' => array('affiliate_id' => $affiliate),
		)));

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}
	}

	function delete() {
		$id = $this->_arg('newsletter');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$dependencies = $this->Newsletter->dependencies($id);
		if ($dependencies !== false) {
			$this->Session->setFlash(__('The following records reference this newsletter, so it cannot be deleted.', true) . '<br>' . $dependencies, 'default', array('class' => 'warning'));
			$this->redirect(array('action' => 'index'));
		}
		if ($this->Newsletter->delete($id, false)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), __('Newsletter', true)), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), __('Newsletter', true)), 'default', array('class' => 'warning'));
		$this->redirect(array('action' => 'index'));
	}

	function delivery() {
		$id = $this->_arg('newsletter');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Newsletter->contain(array('MailingList', 'Delivery'));
		$newsletter = $this->Newsletter->read(null, $id);
		if (!$newsletter) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Configuration->loadAffiliate($newsletter['MailingList']['affiliate_id']);
		$ids = Set::extract('/Delivery/person_id', $newsletter);
		$person = ClassRegistry::init('Person');
		$people = $person->find('all', array(
				'contain' => array(),
				'conditions' => array('id' => $ids),
		));

		$this->set(compact('newsletter', 'people'));
	}

	function send() {
		$id = $this->_arg('newsletter');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}

		$execute = $this->_arg('execute');
		$test = $this->_arg('test');
		$this->set(compact('execute', 'test'));

		if ($execute) {
			// Read the newsletter, including lists of who has received it
			// and who has unsubscribed from this mailing list
			$this->Newsletter->contain(array(
				'Delivery',
				'MailingList' => array(
					'Affiliate',
					'Subscription' => array(
						'conditions' => array('subscribed' => 0),
					),
				),
			));
			$newsletter = $this->Newsletter->read(null, $id);
			if (!$newsletter) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($newsletter['MailingList']['affiliate_id']);

			// Handle the rule controlling mailing list membership
			$rule_obj = AppController::_getComponent ('Rule', '', $this, true);
			if (!$rule_obj->init ($newsletter['MailingList']['rule'])) {
				$this->Session->setFlash(__('Failed to parse the rule', true), 'default', array('class' => 'error'));
				$this->redirect(array('action' => 'view', 'newsletter' => $id));
			}

			$people = $rule_obj->query($newsletter['MailingList']['affiliate_id']);
			if ($people === null) {
				$this->Session->setFlash(__('The syntax of the mailing list rule is valid, but it is not possible to build a query which will return the expected results. See the "rules engine" help for suggestions.', true), 'default', array('class' => 'error'));
				$this->redirect(array('action' => 'view', 'newsletter' => $id));
			}

			if (!empty($people)) {
				$sent_ids = Set::extract('/Delivery/person_id', $newsletter);
				$unsubscribed_ids = Set::extract('/MailingList/Subscription/person_id', $newsletter);
				$people = array_diff($people, $sent_ids, $unsubscribed_ids);
				$people = $this->Person->find('all', array(
					'contain' => array(),
					'conditions' => array(
						'Person.id' => $people,
					),
					'fields' => array(
						'Person.id', 'Person.email', 'Person.first_name','Person.last_name',
					),
					'limit' => $newsletter['Newsletter']['batch_size'],
					'order' => array('Person.email', 'Person.id'),
				));
			}

			if (empty ($people)) {
				$this->Session->setFlash(__('Finished sending newsletters', true), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'delivery', 'newsletter' => $id));
			}

			if (!$this->Lock->lock ('newsletter', $newsletter['MailingList']['affiliate_id'], 'newsletter delivery')) {
				$this->redirect(array('action' => 'view', 'newsletter' => $id));
			}

			$delay = $newsletter['Newsletter']['delay'] * MINUTE;
			$this->set(compact('delay'));
		} else {
			$this->Newsletter->contain(array(
				'MailingList' => 'Affiliate',
			));
			$newsletter = $this->Newsletter->read(null, $id);
			if (!$newsletter) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
				$this->redirect(array('action' => 'index'));
			}
			$this->Configuration->loadAffiliate($newsletter['MailingList']['affiliate_id']);

			if ($test) {
				$this->Person->contain();
				$person = $this->Person->read(null, $this->Auth->user('id'));
				$people = array($person);
			} else {
				$this->set(compact('newsletter'));
				return;
			}
		}

		$this->set(compact('newsletter', 'people'));
		$params = array (
				'from' => $newsletter['Newsletter']['from'],
				'subject' => $newsletter['Newsletter']['subject'],
				'sendAs' => 'html',
				'template' => 'newsletter',
		);
		if ($newsletter['Newsletter']['personalize']) {
			foreach ($people as $person) {
				$params['to'] = $person;
				$code = $this->_hash($person['Person']['id'], $newsletter['MailingList']['id']);
				$this->set(compact('person', 'code'));
				$this->_sendMail ($params);

				if ($execute) {
					// Update the activity log
					$this->Newsletter->Delivery->create();
					$this->Newsletter->Delivery->save(array(
						'type' => 'newsletter',
						'newsletter_id' => $id,
						'person_id' => $person['Person']['id'],
					));
				}
			}
		} else {
			$params['bcc'] = $people;
			if (!empty($newsletter['Newsletter']['to'])) {
				$params['to'] = $newsletter['Newsletter']['to'];
			} else {
				$params['to'] = $newsletter['Newsletter']['from'];
			}
			if (!empty($newsletter['Newsletter']['replyto'])) {
				$params['replyTo'] = $newsletter['Newsletter']['replyto'];
			}
			$this->_sendMail ($params);

			if ($execute) {
				foreach ($people as $person) {
					// Update the activity log
					$this->Newsletter->Delivery->create();
					$this->Newsletter->Delivery->save(array(
						'type' => 'newsletter',
						'newsletter_id' => $id,
						'person_id' => $person['Person']['id'],
					));
				}
			}
		}

		$this->Lock->unlock ();
	}

	function _hash ($person, $list) {
		// Build a string of the inputs
		$input = "$person:$list";
		return md5($input . ':' . Configure::read('Security.salt'));
	}
}
?>
