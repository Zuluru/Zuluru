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

	var $hasOne = array(
		'RegistrationAudit' => array(
			'className' => 'RegistrationAudit',
			'foreignKey' => 'registration_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Response' => array(
			'className' => 'Response',
			'foreignKey' => 'registration_id',
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
		return $this->Event->affiliate($this->field('event_id', array('Registration.id' => $id)));
	}
}
?>