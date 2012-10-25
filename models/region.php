<?php
class Region extends AppModel {
	var $name = 'Region';
	var $displayField = 'name';

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

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

	function affiliate($id) {
		return $this->field('affiliate_id', array('Region.id' => $id));
	}
}
?>
