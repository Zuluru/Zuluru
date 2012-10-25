<?php
class Newsletter extends AppModel {
	var $name = 'Newsletter';
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
		'mailing_list_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'MailingList', 'id'),
				'message' => 'You must select a valid mailing list.',
			),
		),
		'from' => array(
			'email' => array(
				'rule' => array('email'),
				'allowEmpty' => false,
				'message' => 'You must supply a valid email address.',
			),
		),
		'to' => array(
			'email' => array(
				'rule' => array('email'),
				'allowEmpty' => true,
				'message' => 'You must supply a valid email address.',
			),
		),
		'reply_to' => array(
			'email' => array(
				'rule' => array('email'),
				'allowEmpty' => true,
				'message' => 'You must supply a valid email address.',
			),
		),
		'subject' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'target' => array(
			'date' => array(
				'rule' => array('date'),
			),
		),
		'delay' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 60),
				'message' => 'Delay must be between 0 and 60 minutes.',
			),
		),
		'batch_size' => array(
			'range' => array(
				'rule' => array('inclusive_range', 1, 1000),
				'message' => 'Batch size must be between 1 and 1000.',
			),
		),
		'personalize' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'Indicate whether this newsletter will be personalized.',
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
		'MailingList' => array(
			'className' => 'MailingList',
			'foreignKey' => 'mailing_list_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasMany = array(
		'Delivery' => array(
			'className' => 'ActivityLog',
			'foreignKey' => 'newsletter_id',
			'dependent' => true,
			'conditions' => array('type' => 'newsletter'),
		),
	);

	function affiliate($id) {
		return $this->MailingList->affiliate($this->field('mailing_list_id', array('Newsletter.id' => $id)));
	}
}
?>