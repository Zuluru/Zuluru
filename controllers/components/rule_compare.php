<?php
/**
 * Rule for comparing two values.
 */

class RuleCompareComponent extends RuleComponent
{
	function parse($config) {
		$this->rule = array();

		// Get the left side rule
		list ($rule, $config) = $this->parseOneRule ($config);
		if (! $rule || empty ($config)) {
			$this->log("Failed to parse left side rule from $config", 'rules');
			return false;
		}
		$this->rule[] = $rule;

		// Check for a valid operator
		$p = strpos ($config, ' ');
		if ($p === false) {
			$this->log("Did not find a space in $config", 'rules');
			return false;
		}
		$op = substr ($config, 0, $p);
		if (!in_array ($op, array('=', '<', '<=', '>', '>=', '!='))) {
			$this->log("Did not find a valid operator in $config", 'rules');
			return false;
		}
		$this->config = $op;
		$config = trim (substr ($config, $p));

		// Get the right side rule
		list ($rule, $config) = $this->parseOneRule ($config);
		if (! $rule || !empty ($config)) {
			$this->log("Failed to parse right side rule from $config", 'rules');
			return false;
		}
		$this->rule[] = $rule;

		return true;
	}

	function evaluate($params) {
		if (count ($this->rule) != 2 || empty($this->config)) {
			return null;
		}
		$left = $this->rule[0]->evaluate($params);
		$right = $this->rule[1]->evaluate($params);
		$prefix = '';
		switch ($this->config) {
			case '<':
				$success = ($left < $right);
				$result = __('less than', true);
				break;

			case '<=':
				$success = ($left <= $right);
				$result = __('less than or equal to', true);
				break;

			case '>':
				$success = ($left > $right);
				$result = __('greater than', true);
				break;

			case '>=':
				$success = ($left >= $right);
				$result = __('greater than or equal to', true);
				break;

			case '=':
				$success = ($left == $right);
				$result = __('of', true);
				break;

			case '!=':
				$success = ($left != $right);
				$result = __('of', true);
				$prefix = __('NOT ', true);
				break;
		}

		$this->reason = $prefix . $this->rule[0]->desc() . ' ' . $result . ' ' . $this->rule[1]->desc();

		return $success;
	}
}

?>
