<?php
class LockComponent extends Object
{
	var $locked = false;

	function initialize(&$controller, $settings = array()) {
		// Save the controller reference for later use
		$this->controller =& $controller;
	}

	function __destruct() {
		$this->unlock();
	}

	function lock($key, $affiliate = null, $text = null) {
		$this->locked = false;
		$this->Lock = ClassRegistry::init ('Lock');
		$conditions = array('key' => $key);
		if ($affiliate !== null) {
			$conditions['affiliate_id'] = $affiliate;
		}
		$locks = $this->Lock->find ('all', compact('conditions'));
		if (!empty ($locks)) {
			$age = (time() - strtotime ($locks[0]['Lock']['created'])) / 60;
			if ($age > 15) {
				$this->Lock->delete ($locks[0]['Lock']['id']);
			} else {
				if ($text === null) {
					$text = $key;
				}
				$this->controller->Session->setFlash(sprintf(__('There is currently a %s in progress. If unsuccessful, it will expire in 15 minutes.', true), __($text, true)), 'default', array('class' => 'info'));
				return false;
			}
		}
		$this->Lock->save (array('key' => $key, 'affiliate' => $affiliate, 'user_id' => $this->controller->Auth->user('id')));
		$this->locked = true;
		return true;
	}

	function unlock() {
		if ($this->locked) {
			$this->Lock->delete ($this->Lock->id);
			$this->locked = false;
		}
	}
}
?>