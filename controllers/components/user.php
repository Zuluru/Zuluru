<?php
/**
 * Base class for user maintenance callback functionality.  This class defines
 * default no-op functions for all operations that any user system needs, as well
 * as providing some common utility functions that derived classes need.
 */

class UserComponent extends Object
{
	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function configure($config) {
		$this->config = $config;
	}

	function onAdd($record) {
		return true;
	}

	function onEdit($record, $old_record) {
		return true;
	}

	function onDelete($record) {
		return true;
	}
}

?>
