<?php
class Affiliate extends AppModel {
	var $name = 'Affiliate';
	var $displayField = 'name';
	var $order = 'name';

	var $validate = array(
	);

	var $hasMany = array(
		'Badge' => array(
			'className' => 'Badge',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Franchise' => array(
			'className' => 'Franchise',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Holiday' => array(
			'className' => 'Holiday',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'League' => array(
			'className' => 'League',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'MailingList' => array(
			'className' => 'MailingList',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Questionnaire' => array(
			'className' => 'Questionnaire',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Question' => array(
			'className' => 'Question',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Region' => array(
			'className' => 'Region',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'UploadType' => array(
			'className' => 'UploadType',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
		'Waiver' => array(
			'className' => 'Waiver',
			'foreignKey' => 'affiliate_id',
			'dependent' => true,
			'conditions' => '',
		),
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'affiliates_people',
			'foreignKey' => 'affiliate_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
		),
	);

	function readByPlayerId($id) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$affiliates = $this->find('all', array(
			'conditions' => array(
				'AffiliatesPerson.person_id' => $id,
			),
			'contain' => array(),
			'fields' => array('Affiliate.*', 'AffiliatesPerson.*'),
			'joins' => array(
				array(
					'table' => "{$this->tablePrefix}affiliates_people",
					'alias' => 'AffiliatesPerson',
					'type' => 'LEFT',
					'foreignKey' => false,
					'conditions' => 'AffiliatesPerson.affiliate_id = Affiliate.id',
				),
			),
			'order' => 'Affiliate.name',
		));

		return $affiliates;
	}
}
?>