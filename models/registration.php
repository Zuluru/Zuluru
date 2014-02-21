<?php
class Registration extends AppModel {
	var $name = 'Registration';
	var $displayField = 'id';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'person_id',
		'auto_bind' => false,
	));

	var $validate = array(
//		'payment' => array(
//			'inlist' => array(
//				'rule' => array('inlist'),
//				'message' => 'TODO',
//			),
//		),
	);

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
		),
	);

	var $hasMany = array(
		'Payment' => array(
			'className' => 'Payment',
			'foreignKey' => 'registration_id',
			'dependent' => true,
		),
		'Response' => array(
			'className' => 'Response',
			'foreignKey' => 'registration_id',
			'dependent' => true,
		),
	);

	function affiliate($id) {
		return $this->Event->affiliate($this->field('event_id', array('Registration.id' => $id)));
	}
}
?>