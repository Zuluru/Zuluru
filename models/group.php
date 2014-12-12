<?php
class Group extends AppModel {
	var $name = 'Group';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'level' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'groups_people',
			'foreignKey' => 'group_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
		),
	);

}
?>