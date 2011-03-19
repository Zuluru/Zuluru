<?php

class LoginComponent extends object
{
	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	function login() {
		// TODO: Use Auth->userModel instead of User?
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

		if ($this->_controller->Auth->user() && !$this->_controller->Session->check('Zuluru.login_time')) {
			$this->_controller->Session->write('Zuluru.login_time', time());
		}
	}

	function expire() {
		// We must expire the Zuluru data from time to time and force a refresh,
		// so that things like changes to groups or player status will be correct.
		$login = $this->_controller->Session->read('Zuluru.login_time');
		if ($login) {
			// TODO: Make the expiry time configurable
			if (time() < $login + 30 * 60) {
				return false;
			}
		}

		return true;
	}
}

?>
