<?php
class Category extends AppModel {
	var $name = 'Category';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'The name cannot be blank.',
			),
		),
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
			),
		),
	);

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
		),
	);

	var $hasMany = array(
		'Task' => array(
			'className' => 'Task',
			'foreignKey' => 'category_id',
			'dependent' => false,
		)
	);

	function affiliate($id) {
		return $this->field('affiliate_id', array('Category.id' => $id));
	}
}
?>