<?php

class LoginDrupalComponent extends LoginComponent
{
	function login() {
		// Check if we're running under Drupal
		$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
		$session_name = Configure::read('security.auth_session');
		$session_name = $prefix . substr(hash('sha256', $session_name), 0, 32);

		// Hide login/logout menu items
		$this->_controller->Session->write('Zuluru.external_login', true);

		if (!empty($_COOKIE[$session_name])) {
			$user = $this->_controller->Auth->authenticate->find('first', array(
				'conditions' => array(
					'Session.sid' => $_COOKIE[$session_name],
				),
				'contain' => array(
					'Session'  => array('conditions' => array('Session.sid' => $_COOKIE[$session_name])),
					'Person' => 'Group',
				),
			));

			// Check if we're logged in to Drupal
			if ($user && !empty($user['Session']['uid'])) {
				// Parameter to Auth->login must be a string
				$this->_controller->Auth->login($user['Session']['uid'] . '');
				$this->_controller->Session->write('Zuluru.drupal_session', $_COOKIE[$session_name]);
			}
		}

		parent::login();
	}

	// We might have session information but the user has logged out of Drupal
	function expired() {
		if ($this->_controller->Session->read('Zuluru.external_login')) {
			$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
			$session_name = Configure::read('security.auth_session');
			$session_name = $prefix . substr(hash('sha256', $session_name), 0, 32);
			if (empty($_COOKIE[$session_name]) || $_COOKIE[$session_name] != $this->_controller->Session->read('Zuluru.drupal_session')) {
				return true;
			}
			return false;
		}
	}
}

?>
