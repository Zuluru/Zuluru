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
		$this->reason_type = $this->rule->reason_type;
		return (! $success);
	}

	function query() {
		if ($this->rule == null)
			return false;

		// There is no guaranteed way to negate all queries, so we must
		// get the full list of users and remove those that match.
		$yes = $this->rule->query();
		if ($yes === null) {
			return null;
		}
		if ($yes === false) {
			$yes = array();
		}
		// CakePHP should cache the query results, so there's no overhead
		// in doing this multiple times in a single ruleset.
		// We have to pass a non-empty conditions array, or it will be skipped.
		$all = $this->_execute_query(array(1 => 1));
		return array_diff($all, $yes);
	}
}

?>
