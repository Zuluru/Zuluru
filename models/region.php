<?php
class Region extends AppModel {
	var $name = 'Region';
	var $displayField = 'name';

	var $hasMany = array(
		'Facility' => array(
			'className' => 'Facility',
			'foreignKey' => 'region_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
}
?>
