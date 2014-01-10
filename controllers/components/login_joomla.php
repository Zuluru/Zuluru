<?php

class LoginJoomlaComponent extends LoginComponent
{
	function login() {
		$user = $this->_controller->Session->read('__default.user');

		// Check if we're running under Joomla
		if ($user) {
			// Hide login/logout menu items
			$this->_controller->Session->write('Zuluru.external_login', true);

			// Check if we're logged in to Joomla
			if ($user->id) {
				// Parameter to Auth->login must be a string
				$this->_controller->Auth->login($user->id . '');
			}
		}

		parent::login();
	}

	// We might have session information but the user has logged out of Joomla
	function expired() {
		$user = $this->_controller->Session->read('__default.user');
		if (!$user || !$user->id) {
			return true;
		}
		return false;
	}
}

?>
