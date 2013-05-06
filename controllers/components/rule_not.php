<?php
/**
 * Rule for negating the output of any boolean rule.
 */

class RuleNotComponent extends RuleComponent
{
	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate ($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url);
		$this->reason = 'NOT ' . $this->rule->reason;
		$this->reason_type = $this->rule->reason_type;

		// If the thing we're negating can't change, then neither can we
		$this->invariant = $this->rule->invariant;

		return (! $success);
	}

	function query($affiliate) {
		if ($this->rule == null)
			return false;

		// There is no guaranteed way to negate all queries, so we must
		// get the full list of users and remove those that match.
		$yes = $this->rule->query($affiliate);
		if ($yes === null) {
			return null;
		}
		if ($yes === false) {
			$yes = array();
		}
		// CakePHP should cache the query results, so there's no overhead
		// in doing this multiple times in a single ruleset.
		// We have to pass a non-empty conditions array, or it will be skipped.
		$all = $this->_execute_query($affiliate, array(1 => 1));
		return array_diff($all, $yes);
	}
}

?>
