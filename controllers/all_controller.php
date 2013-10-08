<?php
class AllController extends AppController {

	var $name = 'All';
	var $uses = array();
	var $helpers = array('ZuluruGame');

	function publicActions() {
		return array('cron');
	}

	function isAuthorized() {
		// Anyone that's logged in can perform these operations
		switch ($this->params['action']) {
			case 'splash':
				return true;
		}

		return false;
	}

	function splash() {
	}

	function clear_cache() {
		Cache::clear(false, 'file');
		Cache::clear(false, 'long_term');
		$this->Session->setFlash(__('The cache has been cleared.', true), 'default', array('class' => 'success'));
		$this->redirect('/');
	}

	function cron() {
		$this->layout = 'bare';
		if (!ini_get('safe_mode')) { 
			set_time_limit(1800);
		}
		Configure::write ('debug', 0);
		$controllers = array('people', 'leagues', 'teams', 'games', 'team_events', 'events');
		$this->set(compact('controllers'));
		foreach ($controllers as $controller) {
			$this->set($controller, $this->requestAction(array('controller' => $controller, 'action' => 'cron'), array('return')));
		}
	}
}
?>
