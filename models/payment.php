<?php
class Payment extends AppModel {
	var $name = 'Payment';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'created_person_id',
		'modified_by_field' => 'updated_person_id',
		'auto_bind' => false,
	));
	var $validate = array(
		'payment_method' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.payment_method'),
				'message' => 'Select a valid payment method.',
			),
		),
	);

	var $belongsTo = array(
		'Registration' => array(
			'className' => 'Registration',
			'foreignKey' => 'registration_id',
		),
		'RegistrationAudit' => array(
			'className' => 'RegistrationAudit',
			'foreignKey' => 'registration_audit_id',
		),
	);

	function affiliate($id) {
		return $this->Registration->affiliate($this->field('registration_id', array('Payment.id' => $id)));
	}
}
?>