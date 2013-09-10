<?php
class Contact extends AppModel {
	var $name = 'Contact';
	var $displayField = 'name';
	var $order = 'Contact.name';

	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Contact name must not be blank.',
			),
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'There is already a contact by that name.',
			),
		),
		'email' => array(
			'rule' => 'email',
			'message' => 'Please provide a valid email address.',
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
	);

	function affiliate($id) {
		return $this->field('affiliate_id', array('Contact.id' => $id));
	}
}
?>