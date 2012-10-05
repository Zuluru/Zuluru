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
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
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
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
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
}
?>
