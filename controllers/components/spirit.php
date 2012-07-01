<?php
/**
 * Base class for spirit scoring functionality.  This class defines default
 * no-op functions for all operations that any spirit system needs, as well
 * as providing some common utility functions that derived classes need.
 */

class SpiritComponent extends Object
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'basic';

	var $questions = array();

	/**
	 * Set up data entry validation rules. These vary per type,
	 * so we define them in components, and the controller copies
	 * them into the model when required.
	 */
	var $validate = array();

	/**
	 * Default mappings from ratios to symbol filenames. These may
	 * be overridden by specific implementations.
	 */
	var $ratios = array(
		'perfect' => 0.9,
		'ok' => 0.7,
		'caution' => 0.4,
		'not_ok' => 0,
	);

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function getValidate($league) {
		$validate = $this->validate;
		if ($league['numeric_sotg']) {
			$validate['entered_sotg'] = array(
				'range' => array(
					'rule' => array('inclusive_range', 0, $this->max()),
					'message' => 'Spirit scores must be in the range 0-' . $this->max(),
				),
			);
		}

		return $validate;
	}

	/**
	 * Calculate the assigned spirit based on answers to the questions.
	 * This default implementation just adds up the scores for each question.
	 *
	 * @param mixed $entry The record with answers
	 * @return mixed The assigned spirit calculated
	 *
	 */
	function calculate($entry) {
		$score = 0;
		foreach ($this->questions as $key => $question) {
			if ($question['type'] != 'text') {
				$score += $entry[$key];
			}
		}
		$score += $entry['score_entry_penalty'];
		return $score;
	}

	/**
	 * Return an array with expected scores
	 */
	function expected() {
		$expected = array('entered_sotg' => $this->maxs());
		foreach ($this->questions as $key => $question) {
			if ($question['type'] != 'text') {
				$expected[$key] = $this->maxq($key);
			}
		}
		return $expected;
	}
	
	/**
	 * Return the max value for a question, or the entire survey
	 *
	 * @param mixed $q Question to check, or null for the entire survey
	 * @return mixed Maximum value
	 */
	function max($q = null) {
		if (array_key_exists ($q, $this->questions))
			return $this->maxq ($q);
		else
			return $this->maxs();
	}

	/**
	 * Return the maximum possible spirit score for the entire survey.
	 * This default implementation just adds up the max scores for each question.
	 *
	 * @return mixed The maximum possible spirit score for the entire survey
	 */
	function maxs() {
		static $max = null;
		if ($max === null) {
			$max = 0;
			foreach ($this->questions as $key => $question) {
				if ($question['type'] != 'text') {
					$max += $this->maxq($key);
				}
			}
		}
		return $max;
	}

	/**
	 * Return the maximum possible spirit score for a question.
	 *
	 * @param mixed $q Question to check
	 * @return mixed Maximum value
	 */
	function maxq($q) {
		static $max = array();
		if (!array_key_exists ($q, $max)) {
			$question = $this->questions[$q];
			if ($question['type'] != 'text') {
				$max[$q] = 0;
				foreach ($question['options'] as $option) {
					$max[$q] = max ($max[$q], $option['value']);
				}
			}
		}
		return $max[$q];
	}

	/**
	 * Return an array with scores for a defaulted game
	 */
	function defaulted() {
		$default = array('entered_sotg' => $this->mins());
		foreach ($this->questions as $key => $question) {
			if ($question['type'] != 'text') {
				$default[$key] = $this->minq($key);
			}
		}
		return $default;
	}
	
	/**
	 * Return the min value for a question, or the entire survey
	 *
	 * @param mixed $q Question to check, or null for the entire survey
	 * @return mixed Minimum value
	 */
	function min($q = null) {
		if (array_key_exists ($q, $this->questions))
			return $this->minq ($q);
		else
			return $this->mins();
	}

	/**
	 * Return the minimum possible spirit score for the entire survey.
	 * This default implementation just adds up the min scores for each question.
	 *
	 * @return mixed The minimum possible spirit score for the entire survey
	 */
	function mins() {
		static $min = null;
		if ($min === null) {
			$min = 0;
			foreach ($this->questions as $key => $question) {
				if ($question['type'] != 'text') {
					$min += $this->minq($key);
				}
			}
		}
		return $min;
	}

	/**
	 * Return the minimum possible spirit score for a question.
	 *
	 * @param mixed $q Question to check
	 * @return mixed Minimum value
	 */
	function minq($q) {
		static $min = array();
		if (!array_key_exists ($q, $min)) {
			$question = $this->questions[$q];
			if ($question['type'] != 'text') {
				$min[$q] = 0;
				foreach ($question['options'] as $option) {
					$min[$q] = min ($min[$q], $option['value']);
				}
			}
		}
		return $min[$q];
	}

	function symbol($value) {
		foreach ($this->ratios as $file => $ratio) {
			if ($value >= $ratio) {
				return $file;
			}
		}
	}
}

?>