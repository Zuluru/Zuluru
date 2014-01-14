<?php

class LoginZikulaComponent extends LoginComponent
{
	function login() {
		// Check if we're running under Zikula
		if ($this->_controller->Session->read('PNSVrand')) {
			// Hide login/logout menu items
			$this->_controller->Session->write('Zuluru.external_login', true);
		}

		// Check if we're logged in to Zikula
		$uid = $this->_controller->Session->read('PNSVuid');
		if ($uid) {
			// Parameter to Auth->login must be a string
			$this->_controller->Auth->login($uid . '');
			$this->_controller->Session->write('Zuluru.zikula_session', $uid);
		}

		parent::login();
	}

	// We might have session information but the user has logged out of Zikula
	function expired() {
		$uid = $this->_controller->Session->read('PNSVuid');
		if (!$uid || $uid != $this->_controller->Session->read('Zuluru.zikula_session')) {
			return true;
		}
	}
}

?>
