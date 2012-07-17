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
		return true;
	}

	// Check if the user has registered for one of the specified events
	function evaluate($params) {
		if (is_array($params) && array_key_exists ('Registration', $params)) {
			$registered = Set::extract ('/Registration/Event/id', $params);
			$prereqs = array_intersect ($registered, $this->config);
			if (!empty ($prereqs)) {
				return true;
			}
		}
		return false;
	}

	function query() {
		return $this->_execute_query(
			array(
				'Registration.event_id' => $this->config,
				'Registration.payment' => 'Paid',
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
