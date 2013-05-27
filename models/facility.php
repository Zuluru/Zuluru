<?php
class Facility extends AppModel {
	var $name = 'Facility';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Name cannot be empty',
			),
		),
		'code' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'required' => false,
				'allowEmpty' => false,
				'message' => 'Code cannot be empty',
			),
		),
		'location_province' => array(
			'inquery' => array(
				'rule' => array('inquery', 'Province', 'name'),
				'required' => false,
				'message' => 'Select a province from the list',
			),
		),
	);

	var $belongsTo = array(
		'Region' => array(
			'className' => 'Region',
			'foreignKey' => 'region_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Field' => array(
			'className' => 'Field',
			'foreignKey' => 'facility_id',
			'dependent' => true,
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

	function affiliate($id) {
		return $this->Region->affiliate($this->field('region_id', array('Facility.id' => $id)));
	}
}
?>
