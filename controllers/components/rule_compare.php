<?php
/**
 * Rule for comparing two values.
 */

class RuleCompareComponent extends RuleComponent
{
	var $reverse = array('=' => '=', '<' => '>', '<=' => '>=', '>' => '<', '>=' => '<=', '!=' => '!=');

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
		if (!in_array ($op, array_keys($this->reverse))) {
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

	function evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url) {
		if (count ($this->rule) != 2 || empty($this->config)) {
			return null;
		}
		$left = $this->rule[0]->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url);
		$right = $this->rule[1]->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url);

		// If neither thing we're comparing can change, then neither can we
		$this->invariant = ($this->rule[0]->invariant && $this->rule[1]->invariant);

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

	function query($affiliate) {
		if (count ($this->rule) != 2 || empty($this->config)) {
			return null;
		}

		$fields = $joins = $conditions = array();
		$left = $this->rule[0]->build_query($affiliate, $joins, $fields, $conditions);
		$right = $this->rule[1]->build_query($affiliate, $joins, $fields, $conditions);
		if ($left === false || $right === false) {
			return false;
		}

		// Queries with "having" will also have "group by", which doesn't produce
		// results for anyone with zero matches. Check for danger situations and
		// don't allow them to proceed.
		if ($this->rule[0]->query_having) {
			if ($this->config[0] == '<' ||
				($this->config == '=' && $right == '0') ||
				($this->config == '!=' && $right != '0'))
			{
				return null;
			}

			$group = "{$this->rule[0]->query_having} {$this->config} $right";
			$conditions[] = $left;
		} else if ($this->rule[1]->query_having) {
			if ($this->config[0] == '>' ||
				($this->config == '=' && $left == '0') ||
				($this->config == '!=' && $left != '0'))
			{
				return null;
			}

			$group = "{$this->rule[1]->query_having} {$this->reverse[$this->config]} $left";
			$conditions[] = $right;
		} else {
			$group = '';
			$conditions[] = array("$left {$this->config}" => $right);
		}

		return $this->_execute_query($affiliate, $conditions, $joins, $fields, $group);
	}
}

?>
