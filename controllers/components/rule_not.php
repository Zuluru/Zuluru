<?php
/**
 * Rule for negating the output of any boolean rule.
 */

class RuleNotComponent extends RuleComponent
{
	function evaluate($params, $team) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate ($params, $team);
		$this->reason = $this->rule->reason;
		$this->reason_type = $rule->reason_type;
		return (! $success);
	}
}

?>
