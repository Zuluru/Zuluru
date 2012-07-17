<?php
class NewslettersController extends AppController {

	var $name = 'Newsletters';
	var $uses = array('Newsletter', 'Person');
	var $components = array('Email', 'Lock');

	var $paginate = array(
		'Newsletter' => array(
				'order' => array('target' => 'DESC'),
		),
	);

	function index() {
		$this->Newsletters->recursive = 0;
		$this->set('newsletters', $this->paginate('Newsletter', array('target >= DATE_ADD(CURDATE(), INTERVAL -30 DAY)')));
		$this->set('current', true);
	}

	function past() {
		$this->Newsletters->recursive = 0;
		$this->set('newsletters', $this->paginate('Newsletter'));
		$this->set('current', false);
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('newsletter');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), __('newsletter', true)), 'default', array('class' => 'info'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Newsletter->contain();
		$this->set('newsletter', $this->Newsletter->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Newsletter->create();
			if ($this->Newsletter->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), __('newsletter', true)), 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please correct the errors below and try again.', true), __('newsletter', true)), 'default', array('class' => 'warning'));
			}
		}
		$this->set('mailingLists', $this->Newsletter->MailingList->find('list'));
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
			$this->Newsletter->contain();
			$this->data = $this->Newsletter->read(null, $id);
		}
		$this->set('mailingLists', $this->Newsletter->MailingList->find('list'));

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
		$this->Newsletter->contain(array('Delivery'));
		$newsletter = $this->Newsletter->read(null, $id);
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
					'Subscription' => array(
						'conditions' => array('subscribed' => 0),
					),
				),
			));
			$newsletter = $this->Newsletter->read(null, $id);

			// Handle the rule controlling mailing list membership
			$rule_obj = AppController::_getComponent ('Rule', '', $this, true);
			if (!$rule_obj->init ($newsletter['MailingList']['rule'])) {
				$this->Session->setFlash(__('Failed to parse the rule', true), 'default', array('class' => 'error'));
				$this->redirect(array('action' => 'view', 'newsletter' => $id));
			}

			$people = $rule_obj->query();
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

			if (!$this->Lock->lock ('newsletter', 'newsletter delivery')) {
				$this->redirect(array('action' => 'view', 'newsletter' => $id));
			}

			$delay = $newsletter['Newsletter']['delay'] * MINUTE;
			$this->set(compact('delay'));
		} else {
			$this->Newsletter->contain(array(
				'MailingList',
			));
			$newsletter = $this->Newsletter->read(null, $id);

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
