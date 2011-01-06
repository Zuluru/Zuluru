<?php
/**
 * Rule for handling a constant.  Can be invoked by name, or by
 * any string starting with ' or ".
 */

class RuleConstantComponent extends RuleComponent
{
	function parse($config) {
		$this->config = trim ($config, '"\'');
		return true;
	}

	function evaluate($params) {
		return $this->config;
	}

	// Just a constant, so we simply return our configured value
	function desc() {
		return $this->config;
	}
}

?>
