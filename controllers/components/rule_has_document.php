<?php
/**
 * Rule helper for checking whether the user has a required document.
 */

class RuleHasDocumentComponent extends RuleComponent
{
	var $reason = 'have uploaded the required document';

	function parse($config) {
		$this->config = array_map ('trim', explode (',', $config));
		foreach ($this->config as $key => $val) {
			$this->config[$key] = trim ($val, '"\'');
		}
		return (count($this->config) == 2);
	}

	// Check if the user has uploaded the required document
	function evaluate($params) {
		if (is_array($params) && array_key_exists ('Upload', $params)) {
			$matches = Set::extract ("/Upload[type_id={$this->config[0]}][valid_from<={$this->config[1]}][valid_until>={$this->config[1]}]", $params);
			if (!empty ($matches)) {
				return true;
			}
		}
		return false;
	}

	function desc() {
		return __('have the document', true);
	}
}

?>
