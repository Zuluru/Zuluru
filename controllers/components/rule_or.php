<?php
/**
 * Rule for combining the output of boolean rules via "or".
 */

class RuleOrComponent extends RuleComponent
{
	function parse($config) {
		$this->rule = array();
		while (strlen ($config)) {
			list ($rule, $config) = $this->parseOneRule ($config);
			if (! $rule) {
				return false;
			}
			if (!empty ($config)) {
				if ($config[0] != ',') {
					return false;
				}
				$config = substr ($config, 1);
			}
			$this->rule[] = $rule;
		}
		return (!empty ($this->rule));
	}

	function evaluate($params) {
		if (empty ($this->rule))
			return null;
		$reasons = array();
		foreach ($this->rule as $rule) {
			if ($rule->evaluate ($params)) {
				$this->reason = $rule->reason;
				return true;
			} else {
				$reasons[] = $rule->reason;
			}
		}
		$this->reason = implode (__(' AND ', true), $reasons);
		return false;
	}
}

?>
