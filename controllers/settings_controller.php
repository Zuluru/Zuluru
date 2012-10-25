<?php
class SettingsController extends AppController {

	var $name = 'Settings';
	var $uses = array('Setting', 'Province', 'Country', 'Configuration');

	function isAuthorized() {
		if ($this->is_manager) {
			// Managers can perform these operations in affiliates they manage
			if (in_array ($this->params['action'], array(
					'edit',
			)))
			{
				// If an affiliate id is specified, check if we're a manager of that affiliate
				$affiliate = $this->_arg('affiliate');
				if ($affiliate && in_array($affiliate, $this->Session->read('Zuluru.ManagedAffiliateIDs'))) {
					return true;
				}
			}
		}

		return false;
	}

	function edit($section) {
		$affiliate = $this->_arg('affiliate');
		$affiliates = $this->_applicableAffiliates();

		if (!empty($this->data)) {
			$to_delete = array();

			foreach ($this->data['Setting'] as $key => $value) {
				$this->data['Setting'][$key]['affiliate_id'] = $affiliate;
				if (is_array($value['value'])) {
					// There may be dates that need to be deconstructed
					if ($affiliate && (empty($value['value']['day']) || empty($value['value']['month']))) {
						// If we're editing affiliate settings, anything blank should be removed so the system default applies
						unset($this->data['Setting'][$key]);
						if ($key < MIN_FAKE_ID) {
							$to_delete[] = $key;
						}
					} else if (array_key_exists ('year', $value['value'])) {
						$this->data['Setting'][$key]['value'] = $value['value']['year'] . '-' . $value['value']['month'] . '-' . $value['value']['day'];
					} else if (array_key_exists ('month', $value['value'])) {
						$this->data['Setting'][$key]['value'] = '0-' . $value['value']['month'] . '-' . $value['value']['day'];
					}
				} else if ($affiliate && ((empty($value['value']) && $value['value'] !== '0') || $value['value'] == MIN_FAKE_ID)) {
					// If we're editing affiliate settings, anything blank should be removed so the system default applies
					unset($this->data['Setting'][$key]);
					if ($key < MIN_FAKE_ID) {
						$to_delete[] = $key;
					}
				}
			}

			if ((empty($this->data['Setting']) || $this->Setting->saveAll ($this->data['Setting'], array('validate' => false))) &&
				(empty($to_delete) || $this->Setting->deleteAll(array('id' => $to_delete))))
			{
				$this->Session->setFlash(sprintf(__('The %s have been saved', true), __('settings', true)), 'default', array('class' => 'success'));
				// Reload the configuration right away, so it affects any rendering we do now,
				// and rebuild the menu based on any changes.
				if ($affiliate) {
					$this->Configuration->loadAffiliate($affiliate);
				} else {
					$this->Configuration->load($this->Auth->user('id'));
				}
				$this->_initMenu();
			} else {
				$this->Session->setFlash(__('Failed to save the settings', true), 'default', array('class' => 'warning'));
			}
		}
		$this->data = $this->Setting->find('all', array(
				'conditions' => array(
					'person_id' => null,
					'affiliate_id' => $affiliate,
				),
		));
		$this->_loadAddressOptions();

		$defaults = $this->Setting->find('all', array(
				'conditions' => array(
					'person_id' => null,
					'affiliate_id' => null,
				),
		));
		AppModel::_reindexOuter($defaults, 'Setting', 'id');

		if (Configure::read('feature.tiny_mce')) {
			$this->helpers[] = 'TinyMce.TinyMce';
		}

		$this->set(compact('affiliate', 'affiliates', 'defaults'));
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
