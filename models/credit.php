<?php
class Credit extends AppModel {
	var $name = 'Credit';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'created_person_id',
		'auto_bind' => false,
	));

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		),
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
		),
	);
}
?>