<?php
/**
 * Rule for negating the output of any boolean rule.
 */

class RuleNotComponent extends RuleComponent
{
	function evaluate($params) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate ($params);
		$this->reason = $this->rule->reason;
		return (! $success);
	}
}

?>
