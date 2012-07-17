<?php
/**
 * Rule helper for returning any attribute from a record.
 */

class RuleAttributeComponent extends RuleComponent
{
	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	function evaluate($params) {
		// TODO: Look for likely array keys (person, user model config name)
		return $params['Person'][$this->config];
	}

	function build_query(&$joins, &$fields) {
		return "Person.{$this->config}";
	}

	function desc() {
		return sprintf (__('have a %s', true), __($this->config, true));
	}
}

?>
