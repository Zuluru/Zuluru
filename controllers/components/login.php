<?php

class LoginComponent extends Object
{
	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function login() {
		$cookie = $this->_controller->Cookie->read('Auth.User');
		if (!is_null($cookie)) {
			if ($this->_controller->Auth->login($cookie)) {
				//  Clear auth message, just in case we use it.
				$this->_controller->Session->delete('Message.auth');
			} else {
				// Delete invalid cookie
				$this->_controller->Cookie->delete('Auth.User');
			}
		}
	}

	function expired() {
		return false;
	}
}

?>
