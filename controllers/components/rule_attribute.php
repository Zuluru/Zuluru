<?php
/**
 * Rule helper for returning any attribute from a record.
 */

class RuleAttributeComponent extends RuleComponent
{
	// We assume that attributes don't change.
	// TODO: Should be safe for common ones (birthdate, gender), but make it handle everything.
	var $invariant = true;

	function parse($config) {
		$this->config = trim ($config, '"\'');
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
