<?php
class NoticesController extends AppController {

	var $name = 'Notices';

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'next',
				'viewed',
		)))
		{
			return true;
		}
	}

	function next() {
		// Guests get no notices
		if (!$this->is_logged_in || mt_rand(0, 100) > Configure::read('notice_frequency')) {
			return array();
		}

		// Delete any old reminder requests
		$this->Notice->NoticesPerson->deleteAll(array(
				'remind' => true,
				'created < DATE_SUB(NOW(), INTERVAL 1 MONTH)',
		));

		// Delete any annual recurring notices that are too old
		$annual = $this->Notice->find('list', array(
				'conditions' => array('Notice.repeat' => 'annual'),
		));
		$this->Notice->NoticesPerson->deleteAll(array(
				'created < DATE_SUB(NOW(), INTERVAL 1 YEAR)',
				'notice_id' => array_keys($annual),
		));

		// Find the list of all notices the user has seen
		$notices = $this->Notice->NoticesPerson->find('all', array(
				'conditions' => array(
					'person_id' => $this->Auth->user('id'),
				),
		));
		$notice_ids = Set::extract('/NoticesPerson/notice_id', $notices);

		// Check if this user has seen a notice recently; we don't want to overwhelm them
		if (!empty ($notices)) {
			$dates = Set::extract('/NoticesPerson/created', $notices);
			rsort($dates);
			// Was the most recent response in the past 7 days?
			if (array_shift($dates) > date('Y-m-d H:i:s', time() - 7 * 24 * 60 * 60)) {
				return array();
			}
		}

		// Figure out which notices to include based on this user's current details
		$display_to = array('player');
		$teams = $this->Session->read('Zuluru.OwnedTeamIDs');
		if (!empty($teams)) {
			$display_to[] = 'captain';
		}
		$leagues = $this->Session->read('Zuluru.DivisionIDs');
		if (!empty($leagues)) {
			$display_to[] = 'coordinator';
		}
		if ($this->is_admin) {
			$display_to[] = 'admin';
		}

		// Find a notice that the user hasn't seen, if any
		$notice = $this->Notice->find('first', array(
				'conditions' => array(
					'active' => true,
					'effective_date <= NOW()',
					'display_to' => $display_to,
					'NOT' => array('id' => $notice_ids),
				),
				'contain' => array(),
				'order' => 'id',
		));

		// Use current user record to do replacements in notice text
		if (!empty($notice)) {
			while (preg_match('#(.*)<%person (.*?) %>(.*)#', $notice['Notice']['notice'], $matches)) {
				if (!isset($person)) {
					$this->Person = ClassRegistry::init('Person');
					$this->Person->contain();
					$person = $this->Person->read (null, $this->Auth->user('id'));
				}
				$notice['Notice']['notice'] = $matches[1] . $person['Person'][$matches[2]] . $matches[3];
			}
		}

		return $notice;
	}

	function viewed($id, $remind = 0) {
		$this->Notice->NoticesPerson->save(array(
				'notice_id' => $id,
				'person_id' => $this->Auth->user('id'),
				'remind' => $remind,
		));
	}
}
?>