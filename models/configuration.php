<?php
class Configuration extends AppModel
{
	var $name = 'Configuration';
	var $useTable = 'settings';

	function load($id) {
		// _SchemaVersion variable is not needed here, and has a blank category
		$conditions = array(
			'category !=' => '',
			'affiliate_id' => null,
			'OR' => array(
				'category !=' => 'personal',
			),
		);
		if ($id) {
			$conditions['OR']['person_id'] = $id;
		}

		$settings = $this->find('all', array('conditions' => $conditions));
		$this->_load($settings);
	}

	function loadAffiliate($id) {
		if ($id && Configure::read('feature.affiliates')) {
			$settings = $this->find('all', array('conditions' => array('affiliate_id' => $id)));
			$this->_load($settings);
		}
	}

	function _load($settings) {
		foreach ($settings as $setting)
		{
			// Unserialize is needed only for first load from a Leaguerunner database
			//$value = @unserialize($setting['Configuration']['value']);
			//if ($value === false) {
				$value = $setting['Configuration']['value'];
			//}
			Configure::write (
				"{$setting['Configuration']['category']}.{$setting['Configuration']['name']}", $value
			);
		}
	}
}
?>
