<?php
/**
 * Rule for converting a date into a standard format for comparison purposes.
 */

class RuleFormatDateComponent extends RuleComponent
{
	function evaluate($params) {
		if ($this->rule == null)
			return null;
		$date = $this->rule->evaluate ($params);
		return date ('Y-m-d', strtotime ($date));
	}

	function build_query(&$joins, &$fields) {
		if ($this->rule == null)
			return null;
		$date = $this->rule->build_query ($joins, $fields);
		return date ('Y-m-d', strtotime ($date));
	}

	// Just a formatter, so we simply return our rule's description
	function desc() {
		if ($this->rule == null)
			return null;
		return $this->rule->desc();
	}
}

?>
