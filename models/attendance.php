<?php
class Attendance extends AppModel {
	var $name = 'Attendance';
	var $displayField = 'id';

	var $belongsTo = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
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
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'TeamEvent' => array(
			'className' => 'TeamEvent',
			'foreignKey' => 'team_event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
}
?>
