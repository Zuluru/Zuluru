<?php
class Message extends AppModel {
	var $name = 'Message';
	var $useTable = false;

	var $validate = array(
		'contact_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Contact', 'id'),
				'message' => 'You must select a valid contact.',
			),
		),
		'subject' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Subject must not be blank.',
			),
		),
		'message' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Message must not be blank.',
			),
		),
	);
}
?>