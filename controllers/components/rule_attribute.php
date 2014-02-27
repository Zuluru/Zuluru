<?php
/**
 * Rule helper for returning any attribute from a record.
 */

class RuleAttributeComponent extends RuleComponent
{
	var $invariant = true;
	var $invariant_attributes = array('first_name', 'last_name', 'birthdate', 'gender', 'height', 'group_id');

	function parse($config) {
		$this->config = trim ($config, '"\'');
		$this->invariant = in_array($this->config, $this->invariant_attributes);
		return true;
	}

	function evaluate($affiliate, $params) {
		// TODO: Look for likely array keys (person, user model config name)
		return $params['Person'][$this->config];
	}

	function build_query($affiliate, &$joins, &$fields) {
		return "Person.{$this->config}";
	}

	function desc() {
		return sprintf (__('have a %s', true), __($this->config, true));
	}
}

?>
