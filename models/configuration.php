<?php
class Configuration extends AppModel
{
	var $name = 'Configuration';
	var $useTable = 'settings';

	function load($id)
	{
		// _SchemaVersion variable is not needed here, and has a blank category
		$conditions = array(
			'category !=' => '',
			'OR' => array(array(
				'person_id' => null,
			)),
		);
		if ($id) {
			$conditions['OR'][] = array('person_id' => $id);
		}

		$settings = $this->find('all', array('conditions' => $conditions));

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
