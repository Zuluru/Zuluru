<?php
class SettingsController extends AppController {

	var $name = 'Settings';
	var $uses = array('Setting', 'Province', 'Country', 'Configuration');

	function edit($section) {
		if (!empty($this->data)) {
			// There may be dates that need to be deconstructed
			foreach ($this->data['Setting'] as $key => $value) {
				if (is_array($value['value'])) {
					if (array_key_exists ('year', $value['value'])) {
						$this->data['Setting'][$key]['value'] = $value['value']['year'] . '-' . $value['value']['month'] . '-' . $value['value']['day'];
					} else if (array_key_exists ('month', $value['value'])) {
						$this->data['Setting'][$key]['value'] = '0-' . $value['value']['month'] . '-' . $value['value']['day'];
					}
				}
			}

			if ($this->Setting->saveAll ($this->data['Setting'], array('validate' => false))) {
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('settings', true)), 'default', array('class' => 'success'));
				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				$this->Configuration->load($this->Auth->user('id'));
				$this->_initMenu();
			} else {
				$this->Session->setFlash(__('Failed to save the settings', true), 'default', array('class' => 'warning'));
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
		$this->data = $this->Setting->find('all', array(
				'conditions' => array('person_id' => null),
		));
		$provider = $this->params['url']['data']['Setting'];
		$provider = array_shift($provider);
		$provider = $provider['value'];
		$this->set(compact('provider'));
	}
}
?>
