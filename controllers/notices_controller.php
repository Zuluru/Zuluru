<?php
class NoticesController extends AppController {

	var $name = 'Notices';

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		if (in_array ($this->params['action'], array(
				'random',
				'viewed',
		)))
		{
			return true;
		}
	}

	function random() {
		// Guests get no notices
		if (!$this->is_logged_in || mt_rand(0, 100) > NOTICE_FREQUENCY) {
			return array();
		}

		// Delete any old reminder requests
		$this->Notice->NoticesPerson->deleteAll(array(
				'remind' => true,
				'created < DATE_SUB(NOW(), INTERVAL 1 MONTH)',
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
		$leagues = $this->Session->read('Zuluru.LeagueIDs');
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
				'order' => 'RAND()',
		));

		// See if we can find one with annual repetition, and the user hasn't seen it in the past year
		if (empty($notice)) {
			$notice = $this->Notice->find('first', array(
					'joins' => array(
						array(
							'table' => "{$this->Notice->tablePrefix}notices_people",
							'alias' => 'NoticesPerson',
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions' => 'Notice.id = NoticesPerson.notice_id',
						),
					),
					'conditions' => array(
						'Notice.active' => true,
						'Notice.effective_date <= NOW()',
						'Notice.display_to' => $display_to,
						'Notice.repeat' => 'annual',
						'NoticesPerson.person_id' => $this->Auth->user('id'),
						'NoticesPerson.created < DATE_SUB(NOW(), INTERVAL 1 YEAR)',
					),
					'order' => 'RAND()',
			));
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