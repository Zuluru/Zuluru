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

	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		if (empty ($this->rule))
			return null;
		$reasons = array();
		$status = true;
		foreach ($this->rule as $rule) {
			if (!$rule->evaluate ($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url)) {
				// If an invariant rule fails, then we generally don't want to report it,
				// since there's nothing the user can do
				if (!$rule->invariant) {
					$reasons[] = $rule->reason;
				}

				$this->reason_type = $rule->reason_type;
				if (!$this->redirect) {
					$this->redirect = $rule->redirect;
				}
				$status = false;

				// If an invariant rule fails, then the AND can never succeed
				if ($rule->invariant) {
					$this->invariant = true;
				}
			} else if ($complete) {
				if (!$rule->invariant) {
					$reasons[] = $rule->reason;
				}

				// This isn't ideal, but will do until we find a test case demands something better
				$this->reason_type = $rule->reason_type;
			}
		}
		$reasons = array_unique($reasons);
		$this->reason = implode (__(' AND ', true), $reasons);
		if (count($reasons) > 1) {
			$this->reason = "({$this->reason})";
		}
		return $status;
	}

	function query($affiliate) {
		if (empty ($this->rule))
			return false;
		foreach ($this->rule as $rule) {
			$people = $rule->query($affiliate);
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
