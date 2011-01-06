<?php

class ZuluruFormHelper extends FormHelper {
	/**
	 * Extend the default input function by allowing for use of a hidden
	 * field instead of a select, if there's only one option.
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
		return parent::input ($fieldName, $options);
	}
}

?>
