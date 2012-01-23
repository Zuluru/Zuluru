<?php

/**
 * A behavior that reformats certain fields
 */
class FormatterBehavior extends ModelBehavior
{
	var $__settings = array();

	function setup(&$model, $settings = array())
	{
		$this->__settings[$model->alias] = $settings;
	}

	function beforeValidate(&$model)
	{
		foreach ($this->__settings[$model->alias]['fields'] as $field => $formatter)
		{
			if (array_key_exists ($field, $model->data[$model->alias]) && ! empty ($model->data[$model->alias][$field]))
			{
				if (function_exists ($formatter))
				{
					$model->set ($field, $formatter ($model->data[$model->alias][$field]));
				}
				elseif (method_exists ($model, $formatter))
				{
					$model->set ($field, $model->$formatter ($model->data[$model->alias][$field]));
				}
				elseif (method_exists ($this, $formatter))
				{
					$model->set ($field, $this->$formatter ($model->data[$model->alias][$field]));
				}
				else
				{
					$this->log ("Formatter $formatter not found for field $field in model {$model->alias}!");
				}
			}
		}
		return true;
	}

	function postal_format ($val)
	{
		if( strlen( $val ) == 6 )
		{
			$val = strtoupper ($val);
			return substr( $val, 0, 3 ) . ' ' . substr( $val, 3, 3 );
		}
		return $val;
	}

	// Reformat a phone number into a standard format.
	// Returns the original input if the input is not something we recognize as being a phone number.
	// This function explicitly does *not* handle extensions.
	function phone_format ($num)
	{
		$num = clean ($num);
		if (empty ($num))
			return '';

		// Trim off leading "1", we all know it
		if (substr ($num, 0, 1) == 1)
			$num = substr ($num, 1);

		// Check for area code
		if (strlen ($num) == 10)
		{
			$new = '(' . substr ($num, 0, 3) . ') ';
			$num = substr ($num, 3);
		}
		else
		{
			$new = '';
		}

		// Check for local number
		if (strlen ($num) == 7)
		{
			$new .= substr ($num, 0, 3) . '-' . substr ($num, 3, 4);
			return $new;
		}

		return $num;
	}

	// Reformat a name to Proper Case
	function proper_case_format ($name)
	{
		// If the input already has both upper and lower case letters,
		// we'll assume that the user entered it correctly.
		if (preg_match('/[A-Z]/', $name) && preg_match('/[a-z]/', $name)) {
			return $name;
		}

		$name = ucwords(strtolower($name));

		// Not perfect
		return preg_replace(
			'/
				(?: ^ | \\b )			# assertion: beginning of string or a word boundary
				( O\' | Ma?c | Van )	# attempt to match common surnames
				( [^\W\d_] )			# match next char; we exclude digits and _ from \w
			/xe',
			"'\$1' . strtoupper('\$2')",
			$name);
	}

}

?>
