<?php
/**
 * Rule for handling a constant.  Can be invoked by name, or by
 * any string starting with ' or ".
 */

class RuleConstantComponent extends RuleComponent
{
	// Constants can never change, by definition
	var $invariant = true;

	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	function evaluate($affiliate, $params) {
		return $this->config;
	}

	function build_query($affiliate, &$joins, &$fields) {
		return $this->config;
	}

	// Just a constant, so we simply return our configured value
	function desc() {
		return $this->config;
	}
}

?>
