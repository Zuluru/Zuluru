<?php
class SettingsController extends AppController {

	var $name = 'Settings';
	var $uses = array('Setting', 'Province', 'Country', 'Configuration');

	function edit($section) {
		if (!empty($this->data)) {
			if ($this->Setting->saveAll ($this->data['Setting'], array('validate' => false))) {
				$this->Session->setFlash(__('The settings have been saved', true));
				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				$this->Configuration->load($this->Auth->user('id'));
				$this->_initMenu();
			} else {
				$this->Session->setFlash(__('Failed to save the settings', true));
			}
		}
		$this->data = $this->Setting->find('all', array(
				'conditions' => array('person_id' => null),
		));
		$this->_loadAddressOptions();

		$this->render ($section);
	}

	function payment_provider_fields() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$provider = $this->params['url']['data']['Setting'];
		$provider = array_shift($provider);
		$provider = $provider['value'];
		$this->set(compact('provider'));
	}
}
?>
