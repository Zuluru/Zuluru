<?php
class MailingList extends AppModel {
	var $name = 'MailingList';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'opt_out' => array(
			'boolean' => array(
				'rule' => array('boolean'),
			),
		),
		'rule' => array(
			'valid' => array(
				'rule' => array('rule'),
				'message' => 'There is an error in the rule syntax.',
			),
		),
	);

	var $hasMany = array(
		'Newsletter' => array(
			'className' => 'Newsletter',
			'foreignKey' => 'mailing_list_id',
			'dependent' => false,
		),
		'Subscription' => array(
			'className' => 'Subscription',
			'foreignKey' => 'mailing_list_id',
			'dependent' => true,
		),
	);
}
?>