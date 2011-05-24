<?php

class ZuluruFormHelper extends FormHelper {
	var $helpers = array('Html', 'ZuluruHtml');

	/**
	 * Extend the default input function by allowing for use of a hidden
	 * field instead of a select, if there's only one option.
	 * Also, add popup help link, if available.
	 */
	function input($fieldName, $options = array()) {
		if (is_array ($options) && array_key_exists ('hide_single', $options) && $options['hide_single']) {
			unset ($options['hide_single']);
			$is_select = (array_key_exists ('type', $options) && $options['type'] == 'select') ||
							(!array_key_exists ('type', $options) && array_key_exists ('options', $options));
			if ($is_select && count ($options['options']) == 1)
			{
				$value = array_shift (array_keys ($options['options']));
				return parent::hidden ($fieldName, array('value' => $value, /*'id' => null,*/ 'secure' => false));
			}
		}

		// Check if there's online help for this field
		$model = Inflector::tableize($this->model());
		$help_file = VIEWS . 'elements' . DS . 'help' . DS . $model . DS . 'edit' . DS . $fieldName . '.ctp';
		if (file_exists($help_file)) {
			$help = ' ' . $this->ZuluruHtml->help(array('action' => $model, 'edit', $fieldName));
			if (array_key_exists ('after', $options)) {
				$options['after'] = $help . $options['after'];
			} else {
				$options['after'] = $help;
			}
		}

		return parent::input ($fieldName, $options);
	}
}

?>
