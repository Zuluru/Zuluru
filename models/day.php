<?php
class Day extends AppModel {
	var $name = 'Day';
	var $displayField = 'name';

	var $hasAndBelongsToMany = array(
		'Division' => array(
			'className' => 'Division',
			'joinTable' => 'divisions_days',
			'foreignKey' => 'day_id',
			'associationForeignKey' => 'division_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
	);
}
?>
