<?php

class ZuluruFormHelper extends FormHelper {
	var $helpers = array('Html', 'ZuluruHtml');

	function create($model = null, $options = array()) {
		$options = array_merge (array(
			'inputDefaults' => array(
				'format' => array('before', 'label', 'between', 'input', 'after', 'error'),
			),
		), $options);
		return parent::create($model, $options);
	}

	/**
	 * Extend the default input function by allowing for use of a hidden
	 * field instead of a select, if there's only one option.
	 * Also, add popup help link, if available.
	 */
	function input($fieldName, $options = array()) {
		$this->setEntity($fieldName);
		$model = Inflector::tableize($this->model());
		$shortFieldName = $this->field();

		// Check for HTML5 field types, and revert them to text.
		// TODO: CakePHP 2 should handle this natively; remove return value mangling too.
		$html5types = array('number', 'email', 'tel', 'url');
		if (array_key_exists ('type', $options) && in_array($options['type'], $html5types)) {
			$html5type = $options['type'];
			$options['type'] = 'text';
		}

		// If no options were provided, check if there's some configured
		if (!array_key_exists ('type', $options) && !array_key_exists ('options', $options) &&
			Configure::read("options.$model.$shortFieldName") !== null)
		{
			$options['options'] = Configure::read("options.$model.$shortFieldName");
		}

		if (is_array ($options) && array_key_exists ('hide_single', $options) && $options['hide_single']) {
			unset ($options['hide_single']);
			$is_select = (array_key_exists ('type', $options) && $options['type'] == 'select') ||
							(!array_key_exists ('type', $options));

			if ($is_select) {
				if (!isset($options['options'])) {
					$view =& ClassRegistry::getObject('view');
					$varName = Inflector::variable(
						Inflector::pluralize(preg_replace('/_id$/', '', $this->field()))
					);
					$varOptions = $view->getVar($varName);
					if (is_array($varOptions)) {
						$options['options'] = $varOptions;
					}
				}

				if (array_key_exists ('options', $options) && count ($options['options']) == 1) {
					$value = array_shift (array_keys ($options['options']));
					if (!is_array($options['options'][$value])) {
						return parent::hidden ($fieldName, array('value' => $value, /*'id' => null,*/ 'secure' => false));
					}
				}
			}
		}

		// Check if we need to allow a larger date range
		if (!empty($options['looseYears']) && !empty($options['minYear'])) {
			$value = $this->value($fieldName);
			if (!empty($value)) {
				if (is_array($value)) {
					$year = $value['year'];
				} else {
					$year = date('Y', strtotime($value));
				}
				$options['minYear'] = min($options['minYear'], $year - 1);
				$options['maxYear'] = max($options['maxYear'], $year + 1);
			}
		}

		// Check if there's online help for this field
		$help_file = VIEWS . 'elements' . DS . 'help' . DS . $model . DS . 'edit' . DS . low($shortFieldName) . '.ctp';
		if (file_exists($help_file)) {
			$help = ' ' . $this->ZuluruHtml->help(array('action' => $model, 'edit', low($shortFieldName)));

			// For text boxes or multiple selects, the help icon should go at the end of the label
			if (array_key_exists ('cols', $options) ||
				(array_key_exists ('type', $options) && $options['type'] == 'textarea') ||
				(array_key_exists ('multiple', $options) && ($options['multiple'] == 'checkbox' || $options['multiple'] === true))
			)
			{
				// If we don't have a label specified, figure it out.
				if (array_key_exists ('label', $options)) {
					if ($options['label'] === false) {
						$options['label'] = $help;
					} else {
						$options['label'] = $options['label'] . ' ' . $help;
					}
				} else {
					// This code copied from FormHelper->label
					if (strpos($fieldName, '.') !== false) {
						$text = array_pop(explode('.', $fieldName));
					} else {
						$text = $fieldName;
					}
					if (substr($text, -3) == '_id') {
						$text = substr($text, 0, strlen($text) - 3);
					}
					$text = __(Inflector::humanize(Inflector::underscore($text)), true);
					$options['label'] = $text . ' ' . $help;
				}
			} else {
				if (array_key_exists ('multiple', $options) && $options['multiple'] == 'checkbox') {
					$location = 'between';
				} else {
					$location = 'after';
				}
				if (array_key_exists ($location, $options)) {
					$options[$location] = $help . $options[$location];
				} else {
					$options[$location] = $help;
				}
			}
		}

		$ret = parent::input ($fieldName, $options);
		if (isset($html5type)) {
			$ret = str_replace('type="text"', "type=\"$html5type\"", $ret);
		}
		return $ret;
	}
}

?>
