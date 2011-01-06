<?php
class Day extends AppModel {
	var $name = 'Day';
	var $displayField = 'name';

	var $hasAndBelongsToMany = array(
		'League' => array(
			'className' => 'League',
			'joinTable' => 'leagues_days',
			'foreignKey' => 'day_id',
			'associationForeignKey' => 'league_id',
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
