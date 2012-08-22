<?php
/**
 * Rule for combining the output of boolean rules via "and".
 */

class RuleAndComponent extends RuleComponent
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
		return (count($this->rule) > 1);
	}

	function evaluate($params, $team, $strict, $text_reason) {
		if (empty ($this->rule))
			return null;
		$reasons = array();
		$status = true;
		foreach ($this->rule as $rule) {
			if (!$rule->evaluate ($params, $team, $strict, $text_reason)) {
				$reasons[] = $rule->reason;
				$this->reason_type = $rule->reason_type;
				$status = false;
			} else {
				$reasons[] = $rule->reason;
				// This isn't ideal, but will do until we find a test case demands something better
				$this->reason_type = $rule->reason_type;
			}
		}
		$this->reason = implode (__(' AND ', true), $reasons);
		return $status;
	}

	function query() {
		if (empty ($this->rule))
			return false;
		foreach ($this->rule as $rule) {
			$people = $rule->query();
			if ($people === null) {
				return $people;
			}
			if (isset($ret)) {
				$ret = array_intersect($ret, $people);
			} else {
				$ret = $people;
			}

			// If at any time there's none that match, skip the rest of the queries
			if (empty($ret)) {
				break;
			}
		}

		return $ret;
	}
}

?>
