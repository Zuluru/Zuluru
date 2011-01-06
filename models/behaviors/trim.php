<?php

/**
 * A behavior that trims spaces from the ends of text fields
 */
class TrimBehavior extends ModelBehavior
{
	function beforeValidate(&$model)
	{
		$data =& $model->data[$model->alias];
		foreach ($model->_schema as $fieldName => $schema)
		{
			// Check the schema for text types
			if ($schema['type'] == 'string' &&
				array_key_exists($fieldName, $data) &&
				! empty ($data[$fieldName]))
			{
				$new = trim ($data[$fieldName]);
				if ($new !== $data[$fieldName])
				{
					$model->set($fieldName, $new);
				}
			}
		}

		return true;
	}

}

?>