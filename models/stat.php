<?php
class Stat extends AppModel {
	var $name = 'Stat';
	var $validate = array(
		'value' => array(
			'numeric' => array(
				'rule' => array('positive'),
				'message' => 'Stats cannot be negative',
			),
		),
	);

	var $belongsTo = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
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
		'StatType' => array(
			'className' => 'StatType',
			'foreignKey' => 'stat_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>