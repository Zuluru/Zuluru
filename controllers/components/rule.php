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
	 * Indication of whether the rule can ever be expected to pass
	 */
	var $invariant = false;

	/**
	 * Where to redirect to for prerequisite completion, if applicable
	 */
	var $redirect = null;

	/**
	 * When building a query, do we need to use HAVING instead of WHERE?
	 */
	var $query_having = false;

	/**
	 * Common string replacements to make reasons more readable
	 */
	var $tr = array(
		'NOT not '										=> '',
		'have a membership type of none'				=> 'not already have a valid membership',
		'have a membership type of intro'				=> 'have an introductory membership',
		'have a membership type of full'				=> 'have a full membership',

		'have an introductory membership OR have a full membership' => 'have a valid membership',
		'have a full membership OR have an introductory membership' => 'have a valid membership',

		'have a past membership type of none'			=> 'not have been a member in the past',
		'have a past membership type of intro'			=> 'have been an introductory member in the past',
		'have a past membership type of full'			=> 'have been a full member in the past',

		'have been an introductory member in the past OR have been a full member in the past' => 'have been a member in the past',
		'have been a full member in the past OR have been an introductory member in the past' => 'have been a member in the past',

		'have an upcoming membership type of none'		=> 'not have a membership for the upcoming year',
		'have an upcoming membership type of intro'		=> 'have an introductory membership for the upcoming year',
		'have an upcoming membership type of full'		=> 'have a full membership for the upcoming year',

		'have an introductory membership for the upcoming year OR have a full membership for the upcoming year' => 'have a valid membership for the upcoming year',
		'have a full membership for the upcoming year OR have an introductory membership for the upcoming year' => 'have a valid membership for the upcoming year',

		'have a birthdate greater than or equal to'		=> 'have been born on or after',
		'have a birthdate greater than'					=> 'have been born after',
		'have a birthdate less than or equal to'		=> 'have been born on or before',
		'have a birthdate less than'					=> 'have been born before',

		'have a team count of 0'						=> 'not be on another roster',
	);

	/**
	 * Some strings to look for to allow links in reasons
	 */
	var $link_tr = array(
		'have an introductory membership'				=> array('controller' => 'events', 'action' => 'wizard', 'membership'),
		'have a full membership'						=> array('controller' => 'events', 'action' => 'wizard', 'membership'),
		'have a valid membership'						=> array('controller' => 'events', 'action' => 'wizard', 'membership'),
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
		if (empty($config)) {
			$this->log('Got an empty rule', 'rules');
			return false;
		}
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
	 * @param mixed $strict If false, we will allow things with prerequisites that are not yet filled but can easily be
	 * @param mixed $text_reason If true, reasons returned will be only text, no links embedded
	 * @param mixed $complete If true, the reason text will include everything, otherwise it will be situation-specific
	 * @param mixed $absolute_url If true, any links in the reason text will include the host and full path, for emails
	 * @return mixed True if the rule check passes, false if it fails, null if
	 * there is an error
	 *
	 */
	function evaluate($affiliate, $params, $team = null, $strict = true, $text_reason = false, $complete = true, $absolute_url = false) {
		if ($this->rule == null)
			return null;
		$success = $this->rule->evaluate($affiliate, $params, $team, $strict, $text_reason, $complete, $absolute_url);
		$this->reason = $this->rule->reason;
		if ($this->reason[0] == '(' && $this->reason[strlen($this->reason) - 1] == ')') {
			$this->reason = substr($this->reason, 1, -1);
		}
		$this->reason_type = $this->rule->reason_type;
		$this->redirect = $this->rule->redirect;

		// Do string replacements to make the reason more easily understandable
		while (true) {
			$new_reason = strtr ($this->reason, $this->tr);
			if ($new_reason == $this->reason) {
				break;
			}
			$this->reason = $new_reason;
		}

		// Maybe do link replacements to make the reason more easily understandable
		if (!$text_reason) {
			App::import('Helper', 'Html');
			$html = new HtmlHelper();
			foreach ($this->link_tr as $text => $url) {
				if (stripos($this->reason, $text) !== false) {
					if ($absolute_url) {
						$url = $html->url($url, true);
					} else {
						$url['return'] = true;
					}
					$this->reason = str_replace ($text, $html->link($text, $url), $this->reason);
				}
			}
		}

		return $success;
	}

	/**
	 * Perform a query that will find all people matching the rule.
	 *
	 * @return mixed Array of conditions, contains, etc. defining the query, or false if something failed
	 *
	 */
	function query($affiliate) {
		if ($this->rule == null)
			return null;
		return $this->rule->query($affiliate);
	}

	function _execute_query($affiliate, $conditions = array(), $joins = array(), $fields = array(), $group = '') {
		if (empty($conditions) && empty($group)) {
			return false;
		}

		// Merge in invariant conditions and fields
		$conditions = array_merge(array(
				'Person.complete' => true,
				'Person.status' => 'active',
				'Person.email !=' => '',
		), $conditions);
		$fields['Person.id'] = 'Person.id';

		if (Configure::read('feature.affiliate')) {
			$conditions['AffiliatePerson.affiliate_id'] = $affiliate;
			$joins['AffiliatePerson'] = array(
				'table' => "{$this->Person->tablePrefix}affiliates_people",
				'alias' => 'AffiliatePerson',
				'type' => 'INNER',
				'foreignKey' => false,
				'conditions' => 'AffiliatePerson.person_id = Person.id',
			);
		}

		// Eliminate the keys on these arrays. They may be required to prevent
		// duplicates during preparation, but mess up CakePHP's query generator
		$joins = array_values($joins);
		$fields = array_values($fields);

		$this->_controller->Person->contain(array());
		$people = $this->_controller->Person->find('all', compact('fields', 'conditions', 'joins', 'group'));
		return Set::extract('/Person/id', $people);
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
