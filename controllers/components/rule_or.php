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
		return (count($this->rule) > 1);
	}

	function evaluate($params, $team, $strict, $text_reason, $complete) {
		if (empty ($this->rule))
			return null;
		$reasons = array();
		$status = false;
		foreach ($this->rule as $rule) {
			if ($rule->evaluate ($params, $team, $strict, $text_reason, $complete)) {
				$reasons[] = $rule->reason;
				$this->reason_type = $rule->reason_type;
				if (!$this->redirect) {
					$this->redirect = $rule->redirect;
				}
				$status = true;
			} else if ($complete) {
				$reasons[] = $rule->reason;
				// This isn't ideal, but will do until we find a test case demands something better
				$this->reason_type = $rule->reason_type;
			}
		}
		$this->reason = implode (__(' OR ', true), $reasons);
		return $status;
	}

	function query() {
		if (empty ($this->rule))
			return false;

		$ret = array();
		foreach ($this->rule as $rule) {
			$people = $rule->query();
			if ($people === null) {
				return $people;
			}
			$ret = array_unique(array_merge($ret, $people));
		}

		return $ret;
	}
}

?>
