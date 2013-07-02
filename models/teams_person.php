<?php
class TeamsPerson extends AppModel {
	var $name = 'TeamsPerson';

	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('numeric'),
			),
		),
	);

	var $belongsTo = array(
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	function afterSave() {
		if (Configure::read('feature.badges')) {
			$badge_obj = AppController::_getComponent('Badge');
			if (!$badge_obj->update('team', $this->data['TeamsPerson'])) {
				return false;
			}
		}
	}

	function beforeDelete() {
		if (Configure::read('feature.badges')) {
			$this->contain(array());
			$data = $this->read(null, $this->id);
			$badge_obj = AppController::_getComponent('Badge');
			if (!$badge_obj->update('team', array(
					'team_id' => $data['TeamsPerson']['team_id'],
					'person_id' => $data['TeamsPerson']['person_id'],
					'role' => null,
				)))
			{
				return false;
			}
		}
		return true;
	}
}
?>
