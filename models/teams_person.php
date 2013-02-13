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
}
?>
