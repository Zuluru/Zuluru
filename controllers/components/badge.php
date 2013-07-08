<?php

class BadgeComponent extends Object
{
	var $visibility = array();

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function visibility($allow_admin, $min_visibility = BADGE_VISIBILITY_LOW) {
		$this->visibility = range(BADGE_VISIBILITY_HIGH, $min_visibility);
		if ($allow_admin) {
			$this->visibility[] = BADGE_VISIBILITY_ADMIN;
		}
		return $this->visibility;
	}

	function getVisibility() {
		return $this->visibility;
	}

	function prepForDisplay(&$person, $categories = array('runtime', 'aggregate')) {
		// Prepare for reading badge lists
		if (!isset($this->Badge)) {
			if (isset($this->_controller->Badge)) {
				$this->Badge = $this->_controller->Badge;
			} else {
				$this->Badge = ClassRegistry::init('Badge');
			}
		}

		foreach ($categories as $category) {
			$badges = $this->Badge->find('all', array(
					'conditions' => array(
						'Badge.category' => $category,
						'Badge.active' => true,
						'Badge.visibility' => $this->visibility,
					),
					'contain' => array(),
			));
			foreach ($badges as $badge) {
				if (!empty($badge['Badge']['handler'])) {
					if ($category == 'aggregate') {
						list($id,$reps) = explode('x', $badge['Badge']['handler']);
						$this->aggregate($badge, $person, null, $id, $reps);
					} else {
						// TODO: Graceful handling of missing handlers
						$handler = AppController::_getComponent('Badge', $badge['Badge']['handler']);
						$this->$category($badge, $person, null, $handler);
					}
				}
			}
		}
	}

	/**
	 * Find all badges of a particular category and determines whether or not each one applies to the provided
	 * record, assigning or removing badges as required.
	 *
	 * @param mixed $category The badge category to test.
	 * @param mixed $data The record to test badges against. Specifics depend on the badge category.
	 * @param mixed $extra Some categories require additional data to work (e.g. payment status for registrations).
	 * @return mixed True if there were no failures, false otherwise.
	 *
	 */	
	function update($category, $data, $extra = null) {
		$success = true;

		// Prepare for reading badge lists
		if (!isset($this->Badge)) {
			if (isset($this->_controller->Badge)) {
				$this->Badge = $this->_controller->Badge;
			} else {
				$this->Badge = ClassRegistry::init('Badge');
			}
		}

		$badges = $this->Badge->find('all', array(
				'conditions' => array(
					'Badge.category' => $category,
					'Badge.active' => true,
				),
				'contain' => array(),
		));
		foreach ($badges as $badge) {
			if (!empty($badge['Badge']['handler'])) {
				if ($category != 'aggregate') {
					// TODO: Graceful handling of missing handlers
					$handler = AppController::_getComponent('Badge', $badge['Badge']['handler']);
					$success &= $this->$category($badge, $data, $extra, $handler);
				}
			}
		}

		return $success;
	}

	/**
	 * The various badge categories use their handlers in different ways.
	 * Nominated and assigned categories are handled manually, so no callback is required for them.
	 */	

	function runtime($badge, &$person, $extra, $handler) {
		if (array_key_exists('Person', $person)) {
			$p = $person['Person'];
		} else {
			$p = $person;
		}
		if ($handler->applicable($p)) {
			$person['Badge'][] = $badge['Badge'];
		}
		return true;
	}

	function aggregate($badge, &$person, $extra, $id, $reps) {
		$badges = Set::extract("/Badge[id=$id]", $person);
		if (count($badges) >= $reps) {
			$person['Badge'] = Set::extract("/Badge[id!=$id]/.", $person);
			$person['Badge'][] = $badge['Badge'];
		}
	}

	function game($badge, &$data, $extra, $handler) {
		if (!isset($this->Game)) {
			if (isset($this->_controller->Game)) {
				$this->Game = $this->_controller->Game;
			} else {
				$this->Game = ClassRegistry::init('Game');
			}
		}
		if (!isset($this->game) || $this->game['Game']['id'] != $data['Game']['id']) {
			$this->Game->contain(array('HomeTeam' => 'Person', 'AwayTeam' => 'Person'));
			$this->game = $this->Game->read(null, $data['Game']['id']);
		}

		$success = true;
		foreach (array('HomeTeam', 'AwayTeam') as $team) {
			$success &= $this->Badge->BadgesPerson->deleteAll(array(
				'badge_id' => $badge['Badge']['id'],
				'team_id' => $this->game[$team]['id'],
				'game_id' => $data['Game']['id'],
			));
			if ($handler->applicable($this->game, $this->game[$team]['id'])) {
				foreach ($this->game[$team]['Person'] as $person) {
					$badge_data = array(
						'badge_id' => $badge['Badge']['id'],
						'person_id' => $person['id'],
						'team_id' => $this->game[$team]['id'],
						'game_id' => $data['Game']['id'],
						'approved' => true,
					);
					$this->Badge->BadgesPerson->create();
					$success &= $this->Badge->BadgesPerson->save($badge_data);
				}
			}
		}

		return $success;
	}

	function team($badge, &$data, $extra, $handler) {
		if (!isset($this->Team)) {
			if (isset($this->_controller->Team)) {
				$this->Team = $this->_controller->Team;
			} else {
				$this->Team = ClassRegistry::init('Team');
			}
		}
		if (!isset($data['team_id']) || !array_key_exists('role', $data)) {
			$this->Team->TeamsPerson->contain(array());
			$record = $this->Team->TeamsPerson->read(null, $data['id']);
			$data = array_merge($record['TeamsPerson'], $data);
		}
		if (!isset($this->team) || $this->team['Team']['id'] != $data['team_id']) {
			$this->Team->contain(array('Division'));
			$this->team = $this->Team->read(null, $data['team_id']);
		}

		$success = $this->Badge->BadgesPerson->deleteAll(array(
			'badge_id' => $badge['Badge']['id'],
			'person_id' => $data['person_id'],
			'team_id' => $data['team_id'],
		));
		if ($handler->applicable($this->team) && in_array($data['role'], Configure::read('regular_roster_roles')) && $data['status'] == ROSTER_APPROVED) {
			$badge_data = array(
				'badge_id' => $badge['Badge']['id'],
				'person_id' => $data['person_id'],
				'team_id' => $data['team_id'],
				'approved' => true,
			);
			$this->Badge->BadgesPerson->create();
			$success &= $this->Badge->BadgesPerson->save($badge_data);
		}

		return $success;
	}

	function registration($badge, &$data, $paid, $handler) {
		if (!isset($this->Registration)) {
			if (isset($this->_controller->Registration)) {
				$this->Registration = $this->_controller->Registration;
			} else {
				$this->Registration = ClassRegistry::init('Registration');
			}
		}
		if (!isset($this->registration) || $this->registration['Registration']['id'] != $data['Registration']['id']) {
			$this->Registration->contain(array('Person', 'Event' => 'EventType'));
			$this->registration = $this->Registration->read(null, $data['Registration']['id']);
		}

		$success = $this->Badge->BadgesPerson->deleteAll(array(
			'badge_id' => $badge['Badge']['id'],
			'registration_id' => $data['Registration']['id'],
		));
		if ($handler->applicable($this->registration) && $paid) {
			$badge_data = array(
				'badge_id' => $badge['Badge']['id'],
				'person_id' => $this->registration['Person']['id'],
				'registration_id' => $data['Registration']['id'],
				'approved' => true,
			);
			$this->Badge->BadgesPerson->create();
			$success &= $this->Badge->BadgesPerson->save($badge_data);
		}

		return $success;
	}
}

?>
