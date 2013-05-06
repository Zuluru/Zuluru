<?php
/**
 * Rule helper for checking whether the user has registered for something.
 */

class RuleRegisteredComponent extends RuleComponent
{
	var $reason = 'have previously registered for the prerequisite';

	function parse($config) {
		$config = trim ($config, '"\'');
		$this->config = array_map ('trim', explode (',', $config));
		$model = ClassRegistry::init('Event');
		$this->events = $model->find('all', array(
				'contain' => array(),
				'conditions' => array('id' => $this->config[0]),
				'fields' => array('id', 'name'),
		));
		return true;
	}

	// Check if the user has registered for one of the specified events
	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		$events = array();
		if ($text_reason) {
			foreach ($this->events as $event) {
				$events[] = $event['Event']['name'];
			}
		} else {
			App::import('Helper', 'Html');
			$html = new HtmlHelper();
			foreach ($this->events as $event) {
				$url = array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']);
				if ($absolute_url) {
					$url = $html->url($url, true);
				} else {
					$url['return'] = true;
				}
				$events[] = $html->link($event['Event']['name'], $url);
			}
		}
		$this->reason = __('have previously registered for the', true) . ' ' . implode(' ' . __('or', true) . ' ', $events);

		if (is_array($params) && array_key_exists ('Registration', $params)) {
			$registered = Set::extract ('/Registration/Event/id', $params);
			$prereqs = array_intersect ($registered, $this->config);
			if (!empty ($prereqs)) {
				return true;
			}
		}
		return false;
	}

	function query($affiliate) {
		return $this->_execute_query(
			$affiliate,
			array(
				'Registration.event_id' => $this->config,
				'Registration.payment' => array('Paid', 'Pending'),
			),
			array('Registration' => array(
				'table' => 'registrations',
				'alias' => 'Registration',
				'type' => 'LEFT',
				'foreignKey' => false,
				'conditions' => 'Registration.person_id = Person.id',
			))
		);
	}

	function desc() {
		return __('Registered', true);
	}
}

?>
