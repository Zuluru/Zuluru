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
				$this->_controller->Session->write('Zuluru.joomla_session', $user->id);
			}
		}

		parent::login();
	}

	// We might have session information but the user has logged out of Joomla
	function expired() {
		if ($this->_controller->Session->read('Zuluru.external_login')) {
			$user = $this->_controller->Session->read('__default.user');
			if (!$user || !$user->id || $user->id != $this->_controller->Session->read('Zuluru.joomla_session')) {
				return true;
			}
			return false;
		}
	}
}

?>
