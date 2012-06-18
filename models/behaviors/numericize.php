<?php

/**
 * A behavior that strips non-numeric characters from numeric fields
 */
class NumericizeBehavior extends ModelBehavior
{
	function beforeValidate(&$model)
	{
		$data =& $model->data[$model->alias];

		// Swiped most of this from Model::invalidFields
		foreach ($model->validate as $fieldName => $ruleSet)
		{
			$required = false;

			if (!is_array($ruleSet) || (is_array($ruleSet) && isset($ruleSet['rule'])))
			{
				$ruleSet = array($ruleSet);
			}
			$default = array('rule' => 'blank', 'required' => 'false');

			foreach ($ruleSet as $index => $validator) {
				if (!is_array($validator))
				{
					$validator = array('rule' => $validator);
				}
				$validator = array_merge($default, $validator);

				// For any numeric fields where we have data, make sure it's only numeric data.
				if (array_key_exists($fieldName, $data) &&
					! empty ($data[$fieldName]) &&
					$validator['rule'] == 'numeric')
				{
					$model->set($fieldName, preg_replace('/[^0-9\.]/', '', $data[$fieldName]));
				}

				$required |= $validator['required'];
			}

			// Check the schema for numeric types, we may need to put a 0 in for non-required fields
			if (! $required &&
				array_key_exists ($fieldName, $model->_schema) &&
				($model->_schema[$fieldName]['type'] == 'float' ||
				 $model->_schema[$fieldName]['type'] == 'int') &&
				array_key_exists($fieldName, $data) &&
				empty ($data[$fieldName]))
			{
				$model->set($fieldName, '0');
			}
		}

		return true;
	}

}

?>
