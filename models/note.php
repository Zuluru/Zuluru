<?php
class Note extends AppModel {
	var $name = 'Note';
	var $displayField = 'note';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'created_person_id',
		'auto_bind' => false,
	));

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
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Field' => array(
			'className' => 'Field',
			'foreignKey' => 'field_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CreatedTeam' => array(
			'className' => 'Team',
			'foreignKey' => 'created_team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CreatedPerson' => array(
			'className' => 'Person',
			'foreignKey' => 'created_person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>