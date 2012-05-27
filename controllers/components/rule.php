<?php
/**
 * Base class for rules engine functionality.  This class handles all of
 * the rule chaining as well as providing some common utility functions
 * that derived classes need.
 */

class RuleComponent extends Object
{
	/**
	 * Saved configuration from initialization
	 */
	var $config = array();

	/**
	 * Rule (or chain of rules)
	 */
	var $rule = null;

	/**
	 * Reason why the rule passed or failed
	 */
	var $reason = 'Unknown reason!';
	var $reason_type = REASON_TYPE_PLAYER_ACTIVE;

	/**
	 * Common string replacements to make reasons more readable
	 */
	var $tr = array(
		'NOT not '										=> '',
		'have a membership type of none'				=> 'not have a valid membership',
		'have a membership type of intro'				=> 'have an introductory membership',
		'have a membership type of full'				=> 'have a full membership',
		'have an introductory membership OR have a full membership' => 'have a valid membership',
		'have a full membership OR have an introductory membership' => 'have a valid membership',
		'have a valid membership OR have a valid membership' => 'have a valid membership',

		'have a birthdate greater than or equal to'		=> 'have been born on or after',
		'have a birthdate greater than'					=> 'have been born after',
		'have a birthdate less than or equal to'		=> 'have been born on or before',
		'have a birthdate less than'					=> 'have been born before',

		'have a team count of 0'						=> 'not be on another roster',

		'have previously registered for the prerequisite OR have previously registered for the prerequisite' => 'have previously registered for one of the prerequisites',
		'have previously registered for one of the prerequisites OR have previously registered for the prerequisite' => 'have previously registered for one of the prerequisites',
	);

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	/**
	 * Initialize the rule engine, loading all required components and
	 * initializing each of them.
	 *
	 * Rules may overload this if necessary, but the default should suffice.
	 *
	 * @param mixed $config A configuration string defining the rule chain
	 * @return mixed True if successful, false if there is some error in the config
	 */
	function init($config) {
		return $this->parse ($config);
	}

	function parse($config) {
		list ($this->rule, $config) = $this->parseOneRule ($config);
		return (empty ($config) && $this->rule != null);
	}

	function parseOneRule($config) {
		// Check for a constant
		if ($config[0] == '\'' || $config[0] == '"') {
			$rule_name = 'constant';
			$p = 0;
			$p2 = $this->findClose ($config, $p, $config[0]);
		} else {
			// Anything else should be a rule name followed by arguments in parentheses
			$p = strpos ($config, '(');
			$rule_name = trim (substr ($config, 0, $p));
			if (empty ($rule_name)) {
				$this->log("Didn't find a rule name in $config.", 'rules');
				return false;
			}
			$p2 = $this->findClose ($config, $p, ')', '(');
		}
		if ($p2 === false) {
			return false;
		}
		$rule_config = trim (substr ($config, $p + 1, $p2 - 1));
		$rule = $this->initRule ($rule_name, $rule_config);
		$config = trim (substr ($config, $p + $p2 + 1));
		return array($rule, $config);
	}

	function findClose($config, $p, $close, $open = null) {
		$count = 1;
		for ($i = $p + 1; $i < strlen ($config) && $count; ++ $i) {
			if ($config[$i] == $open) {
				++ $count;
			} else if ($config[$i] == $close) {
				-- $count;
			}
		}
		if ($count > 0) {
			return false;
		}
		return $i - $p - 1;
	}

	/**
	 * Create a rule object and initialize it with a configuration string
	 *
	 * @param mixed $rule The name of the rule
	 * @param mixed $config The configuration string
	 * @return mixed The created rule object on success, false otherwise
	 *
	 */
	function initRule($rule, $config) {
		$rule_obj = AppController::_getComponent ('Rule', $rule, $this->_controller, true);
		if ($rule_obj) {
			if ($rule_obj->init ($config)) {
				return $rule_obj;
			}
		}
		$this->log("Failed to initialize rule component $rule with $config.", 'rules');
		return null;
	}

	/**
	 * Evaluate the rule chain against an input.
	 *
	 * @param mixed $params An array with parameters used by the various rules
	 * @param mixed $team An array with team information, if applicable
	 * @return mixed True if the rule check passes, false if it fails, null if
	 * there is an error
	 *
	 */
	function evaluate($params, $team = null) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate ($params, $team);
		$this->reason = $this->rule->reason;
		$this->reason_type = $this->rule->reason_type;

		// Do string replacements to make the reason more easily understandable
		while (true) {
			$new_reason = strtr ($this->reason, $this->tr);
			if ($new_reason == $this->reason) {
				break;
			}
			$this->reason = $new_reason;
		}

		return $success;
	}

	/**
	 * Return a description of the rule, not required for all rules
	 *
	 * @return mixed String description
	 *
	 */
	function desc() {
		return null;
	}

	// TODO: Distinguish the boolean rules from helpers that return values?
}

?>
