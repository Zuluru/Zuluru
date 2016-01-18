<?php
/**
 * Rule helper for returning any attribute from a record.
 */

class RuleAttributeComponent extends RuleComponent
{
	var $invariant = true;
	var $invariant_attributes = array('first_name', 'last_name', 'birthdate', 'gender', 'height');

	function parse($config) {
		$this->config_path = explode('.', trim($config, '"\''));
		$this->config = implode('.', $this->config_path);
		if (count($this->config_path) == 1) {
			$this->config = "Person.{$this->config}";
		}
		array_unshift($this->config_path, 'Person');

		$this->invariant = in_array($this->config, $this->invariant_attributes);
		return true;
	}

	function evaluate($affiliate, $params) {
		foreach ($this->config_path as $key) {
			if (!array_key_exists($key, $params)) {
				if (Set::numeric(array_keys($params))) {
					$params = Set::extract("/$key", $params);
					if (!empty($params)) {
						return $params;
					}
				}
				return '';
			}
			$params = $params[$key];
		}
		return $params;
	}

	function build_query($affiliate, &$joins, &$fields) {
		return $this->config;
	}

	function desc() {
		return sprintf (__('have a %s', true), __($this->config, true));
	}
}

?>
