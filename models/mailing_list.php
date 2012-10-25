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
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
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

	function affiliate($id) {
		return $this->field('affiliate_id', array('MailingList.id' => $id));
	}
}
?>