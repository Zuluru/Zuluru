<?php
/**
 * Rule helper for returning a user's membership type.
 */

class RuleMemberTypeComponent extends RuleComponent
{
	var $desc = 'have a membership type';

	function parse($config) {
		$this->config = trim ($config, '"\'');
		if ($this->config[0] == '<') {
			$this->config = array('0000-00-00', substr($this->config, 1));
		} else if ($this->config[0] == '>') {
			$this->config = array(substr($this->config, 1), '9999-12-31');
		} else if (strpos($this->config, ',') !== false) {
			$this->config = explode(',', $this->config);
		}
		return true;
	}

	// Check if the user was a member on the configured date
	function evaluate($affiliate, $params) {
		if (is_array($this->config)) {
			$date_from = strtotime ($this->config[0]);
			$date_to = strtotime ($this->config[1]);
		} else {
			$date_from = $date_to = strtotime ($this->config);
		}
		$today = date('Y-m-d');
		// TODO: A better way to rank membership types that handles more than just intro and full
		$intro = false;
		if (is_array($params) && array_key_exists ('Registration', $params)) {
			foreach ($params['Registration'] as $reg) {
				if (array_key_exists('membership_begins', $reg['Event']) &&
					$reg['Event']['affiliate_id'] == $affiliate &&
					strtotime ($reg['Event']['membership_begins']) <= $date_to &&
					$date_from <= strtotime ($reg['Event']['membership_ends']))
				{
					if ($reg['Event']['membership_ends'] < $today) {
						$this->desc = 'have a past membership type';
					} else if ($reg['Event']['membership_begins'] > $today) {
						$this->desc = 'have an upcoming membership type';
					}

					if ($reg['Event']['membership_type'] == 'full') {
						return 'full';
					} else if ($reg['Event']['membership_type'] == 'intro' || $reg['Event']['membership_type'] == 'junior_intro') {
						$intro = true;
					}
				}
			}
		}

		return ($intro ? 'intro' : 'none');
	}

	function build_query($affiliate, &$joins, &$fields, &$conditions) {
		if (!isset($this->events)) {
			if (is_array($this->config)) {
				$date_from = date('Y-m-d', strtotime ($this->config[0]));
				$date_to = date('Y-m-d', strtotime ($this->config[1]));
			} else {
				$date_from = $date_to = date('Y-m-d', strtotime ($this->config));
			}
			$model = ClassRegistry::init('Event');
			$types = $model->EventType->find('list', array(
					'contain' => array(),
					'conditions' => array('type' => 'membership'),
			));
			$events = $model->find('all', array(
					'contain' => array(),
					'conditions' => array(
						'Event.event_type_id' => array_keys($types),
						'Event.affiliate_id' => $affiliate,
					),
			));
			foreach ($events as $key => $event) {
				if ($event['Event']['membership_begins'] > $date_to || $event['Event']['membership_ends'] < $date_from) {
					unset($events[$key]);
				}
			}
			$this->events = Set::extract('/Event/id', $events);
		}

		$joins['Registration'] = array(
			'table' => 'registrations',
			'alias' => 'Registration',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Registration.person_id = Person.id',
		);
		$joins['Event'] = array(
			'table' => 'events',
			'alias' => 'Event',
			'type' => 'LEFT',
			'foreignKey' => false,
			'conditions' => 'Event.id = Registration.event_id',
		);

		$conditions['Event.id'] = $this->events;
		$conditions['Registration.payment'] = 'Paid';

		if (Configure::read('feature.affiliate')) {
			$conditions['Event.affiliate_id'] = $affiliate;
		}

		// TODO: This is almost certainly MySQL-specific
		$type_str = '"membership_type";s:';
		$type_len_pos = "POSITION('$type_str' IN custom) + " . strlen($type_str);
		$type_len_len = "POSITION('\"' IN SUBSTR(custom, $type_len_pos))";
		$type_len = "SUBSTR(custom, $type_len_pos, $type_len_len)";
		$type = "SUBSTR(custom, $type_len_pos + $type_len_len, $type_len)";
		return $type;
	}

	function desc() {
		return __($this->desc, true);
	}
}

?>
