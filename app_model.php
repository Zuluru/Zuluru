<?php
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Application model for Cake.
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.model
 */
class AppModel extends Model {
	// Make all models containable; we make a lot of use of this feature for limiting
	// which related data is loaded in any given find.
	// TODO: Add trim, ProperCase behaviours where appropriate
	var $actsAs = array('Containable');

	// Some common-but-non-standard regexes we need in multiple models
	const NAME_REGEX = '/^[ a-z0-9\-\.\',]*$/i';
	const EXTENDED_NAME_REGEX = '/^[ 0-9a-z\-\.\'",\!\?@&()]*$/i';
	const ADDRESS_REGEX = '/^[ 0-9a-z\-\.\',#&]*$/i';

	//
	// Generic afterFind function, which handles data in the many different
	// layouts it might appear in, and calls the model-specific _afterFind
	// method (if it exists) on the individual records for any adjustments
	// that are required.
	//
	// The records passed into _afterFind will *always* have the alias as an
	// index, and may have other indices as well for related models, or related
	// models may be "under" the main record, depending on the query.  Trying
	// to generically handle all of those situations is just too much!
	//
	function afterFind ($results) {
		if (method_exists ($this, '_afterFind') && !empty ($results)) {
			// The data can come in many forms
			if (array_key_exists(0, $results)) {
				foreach ($results as $key => $result) {
					$results[$key] = $this->afterFind ($result);
				}
			} else if (array_key_exists($this->alias, $results)) {
				if (empty ($results[$this->alias])) {
					// Don't do anything with empty records
				} else if (array_key_exists(0, $results[$this->alias])) {
					$results = $this->afterFind ($results[$this->alias]);
				} else {
					$results = $this->_afterFind ($results);
				}
			} else if (count($results) == 1 && array_key_exists ('count', $results)) {
				// Don't do anything with records that are just pagination counts
			} else {
				$results = $this->_afterFind (array($this->alias => $results));
				$results = $results[$this->alias];
			}
		}
		return $results;
	}

	//
	// Use model associations to determine whether a record can be deleted.
	//
	function dependencies($id, $ignore = array()) {
		$dependencies = array();

		foreach ($this->hasMany as $class => $association) {
			if (!in_array($class, $ignore)) {
				$conditions = array("$class.{$association['foreignKey']}" => $id);
				if (!empty($association['conditions'])) {
					$conditions += $association['conditions'];
				}

				$dependent = $this->$class->find('count', array(
						'conditions' => $conditions,
						'contain' => false,
				));
				if ($dependent > 0) {
					$dependencies[] = __(Inflector::pluralize($class), true) . ': ' . $dependent;
				}
			}
		}

		foreach ($this->hasAndBelongsToMany as $class => $association) {
			if (!in_array($class, $ignore)) {
				$class = $association['with'];

				$conditions = array("$class.{$association['foreignKey']}" => $id);
				if (!empty($association['conditions'])) {
					$conditions += $association['conditions'];
				}

				$dependent = $this->$class->find('count', array(
						'conditions' => $conditions,
						'contain' => false,
				));
				if ($dependent > 0) {
					$dependencies[] = __(Inflector::pluralize($association['className']), true) . ': ' . $dependent;
				}
			}
		}

		if (!empty($dependencies)) {
			return implode(', ', $dependencies);
		}
		return false;
	}

	/**
	 * Adjust the indices of the provided array, so that the
	 * array is indexed by a specified id instead of from zero.
	 *
	 * This version is for data like
	 * array(
	 *		0 => array(
	 *			'ParentModel' => array(...),
	 *			'Model' => array(
	 *				0 => array(fields),
	 *				1 => array(fields),
	 *				2 => array(fields),
	 *			),
	 *		),
	 *		1 => array(
	 *			...
	 *		),
	 * );
	 */
	static function _reindexInner(&$data, $model, $field) {
		if (empty ($data)) {
			return;
		}
		if (Set::numeric (array_keys ($data))) {
			foreach (array_keys ($data) as $i) {
				self::_reindexInner($data[$i], $model, $field);
			}
			return;
		}
		if (array_key_exists ($model, $data)) {
			$new = array();
			foreach (array_keys ($data[$model]) as $key) {
				$id = $data[$model][$key][$field];
				$new[$id] = $data[$model][$key];
			}
			$data[$model] = $new;
		}
	}

	/**
	 * Adjust the indices of the provided array, so that the
	 * array is indexed by a specified id instead of from zero.
	 *
	 * This version is for data like
	 * array(
	 *		0 => array('Model' => array(fields)),
	 *		1 => array('Model' => array(fields)),
	 *		2 => array('Model' => array(fields)),
	 * );
	 *
	 * or
	 *
	 * array(
	 *		0 => array(array(fields)),
	 *		1 => array(array(fields)),
	 *		2 => array(array(fields)),
	 * );
	 */
	static function _reindexOuter(&$data, $model, $field) {
		if (empty ($data)) {
			return;
		}
		if (!Set::numeric (array_keys ($data))) {
			return;
		}
		$new = array();
		foreach (array_keys ($data) as $key) {
			if (array_key_exists($model, $data[$key])) {
				$id = $data[$key][$model][$field];
			} else {
				$id = $data[$key][$field];
			}
			$new[$id] = $data[$key];
		}
		$data = $new;
	}

	//
	// Validation helpers
	//

	function mustmatch($check, $field1, $field2) {
		$data = current($this->data);
		return $data[$field1] === $data[$field2];
	}

	function matchpassword($check) {
		$value = array_values($check);
		$value = $value[0];
		if (Configure::read ('security.salted_hash')) {
			$compare = Security::hash($value, null, true);
		} else {
			$compare = Security::hash($value, null, '');
		}

		return ($compare == $this->data['User']['password']);
	}

	function mustnotmatch($check, $field1, $field2) {
		$data = current($this->data);
		if (!array_key_exists ($field1, $data) || !array_key_exists ($field2, $data)) {
			return true;
		}
		return $data[$field1] !== $data[$field2];
	}

	function inconfig($check, $config) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		return array_key_exists($value, Configure::read($config));
	}

	function indateconfig($check, $config) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		if (!is_array($value)) {
			$year = date ('Y', strtotime ($value));
		} else {
			if (!array_key_exists ('year', $value)) {
				return false;
			}
			$year = $value['year'];
		}

		$min = Configure::read("options.year.$config.min");
		$max = Configure::read("options.year.$config.max");
		if ($min === null || $max === null) {
			return false;
		}

		return ($min <= $year && $year <= $max);
	}

	function greaterdate($check, $field) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		$data = current($this->data);
		return ($value > $data[$field]);
	}

	// Check a combined date and time, using standard separate date and time validators
	function datetime($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		list ($date, $time) = explode (' ', $value, 2);
		$Validation =& Validation::getInstance();
		return ($Validation->date ($date) && $Validation->time ($time));
	}

	function positive($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		return (is_numeric($value) && $value >= 0);
	}

	function inquery($check, $model, $field, $conditions = array()) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		// This needs to work for questionnaire submissions
		if (is_array($value)) {
			if (array_key_exists('answer_id', $value)) {
				$value = $value['answer_id'];
			} else if (array_key_exists('answer', $value)) {
				$value = $value['answer'];
			}
		}

		// The validation array is always passed at the end of the arguments;
		// if no conditions were passed, that will be in this array, so we
		// need to get rid of that.
		if (array_key_exists ('allowEmpty', $conditions)) {
			$conditions = array();
		}

		$model_obj = ClassRegistry::init($model);
		$values = $model_obj->find('list', array('fields' => $field, 'conditions' => $conditions));

		return in_array ($value, $values);
	}

	function notinquery($check, $model, $field, $conditions = array()) {
		return (!$this->inquery($check, $model, $field, $conditions));
	}

	/**
	 * Validate that a number is in specified range.
	 * if $lower and $upper are not set, will return true if
	 * $check is a legal finite on this platform.
	 * Copied from the main "range" validation function, but
	 * altered to be an inclusive range instead of exclusive.
	 *
	 * @param string $check Value to check
	 * @param integer $lower Lower limit
	 * @param integer $upper Upper limit
	 * @return boolean Success
	 * @access public
	 */
	function inclusive_range($check, $lower = null, $upper = null) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		if (!is_numeric($value)) {
			return false;
		}
		if (isset($lower) && isset($upper)) {
			return ($value >= $lower && $value <= $upper);
		}
		return is_finite($value);
	}

	/**
	 * Handle validation of a questionnaire response
	 *
	 * @param mixed $check The data to check for validity
	 * @param mixed $rule The rule to check with
	 * @return mixed true if the data is valid, false otherwise
	 *
	 */
	function response($check, $rule) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		if (array_key_exists('answer', $value)) {
			$value = $value['answer'];
		} else {
			$value = $value['answer_id'];
		}

		$Validation =& Validation::getInstance();
		if (method_exists($Validation, $rule)) {
			return $Validation->dispatchMethod($rule, array($value));
		} elseif (!is_array($rule)) {
			return preg_match($rule, $value);
		} elseif (Configure::read('debug') > 0) {
			trigger_error(sprintf(__('Could not find validation handler %s for %s', true), $rule, 'response'), E_USER_WARNING);
		}

		return false;
	}

	function response_select($check, $opts, $required) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		$value = $value['answer_id'];

		// A value from the provided list of options is okay
		if (in_array ($value, $opts))
			return true;
		// If the question is not required, a blank value is okay
		if ($value === '' && !$required)
			return true;
		// Nothing else is okay
		return false;
	}

	/**
	 * Enforce unique team names within leagues instead of divisions,
	 * but not in a way that messes with playoff divisions.
	 */
	function team_unique($check, $team_id, $division_id) {
		$value = array_values($check);
		$value = $value[0];

		if ($division_id) {
			// Find the list of divisions in the same league
			$division_obj = ClassRegistry::init('Division');
			$division_obj->contain();
			$division = $division_obj->read(null, $division_id);
			$division_obj->addPlayoffs($division);
			$division_conditions = $division['Division']['sister_divisions'];
		} else {
			$division_conditions = $division_id;
		}

		$team_obj = ClassRegistry::init('Team');
		$duplicate = $team_obj->find('count', array(
			'conditions' => array(
				'division_id' => $division_conditions,
				'id !=' => $team_id,
				'name' => $value,
			),
			'contain' => false,
		));

		return ($duplicate == 0);
	}

	function franchise_owner($check, $owner, $is_admin) {
		if ($is_admin) {
			return true;
		}

		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		$value = $value['answer_id'];

		// -1 means make a new one with the same name as the team
		if ($value != -1) {
			$franchise_obj = ClassRegistry::init('Franchise');
			$franchise_obj->contain('Person');
			$franchise = $franchise_obj->read(null, $value);
			$owners = Set::extract('/Person/id', $franchise);
			return in_array($owner, $owners);
		}

		return true;
	}

	function franchise_unique($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];
		$value = $value['answer_id'];

		// -1 means make a new one with the same name as the team
		if ($value == -1) {
			$name = EventTypeComponent::_extractAnswer($this->data, TEAM_NAME);
			return $this->notinquery (array(array('answer' => $name)), 'Franchise', 'name');
		}

		return true;
	}

	function rule($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		$rule_obj = AppController::_getComponent ('Rule');
		return $rule_obj->init ($value);
	}

	function valid_score($check, $lower, $upper) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		$data = current($this->data);
		if (in_array($data['status'], Configure::read('unplayed_status'))) {
			return ($value === null);
		}
		return $this->inclusive_range($check, $lower, $upper);
	}

	function valid_play($check) {
		// $check array is passed using the form field name as the key
		// have to extract the value to make the function generic
		$value = array_values($check);
		$value = $value[0];

		$options = array_merge(make_options(array_merge(array_keys(Configure::read('sport.score_options')), array('Start', 'Timeout'))), Configure::read('sport.other_options'));
		return array_key_exists($value, $options);
	}
}
?>
