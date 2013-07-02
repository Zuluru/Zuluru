<?php
class Badge extends AppModel {
	var $name = 'Badge';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'A valid badge name must be entered.',
			),
		),
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'The description cannot be blank.',
			),
		),
		'category' => array(
			'inconfig' => array(
				'rule' => array('inconfig', 'options.category'),
				'message' => 'You must select a valid category.',
			),
		),
		'active' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Select whether or not this badge will be active in your system.',
			),
		),
		'visibility' => array(
			'inconfig' => array(
				'rule' => array('inconfig', 'options.visibility'),
				'message' => 'Select where this badge will be visible.',
			),
		),
		'icon' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'You must provide the file name of the badge icon, relative to the icons folder.',
			),
		),
	);

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
		),
	);

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'badges_people',
			'with' => 'BadgesPerson',
			'foreignKey' => 'badge_id',
			'associationForeignKey' => 'person_id',
			'unique' => false,
		),
	);

	function affiliate($id) {
		return $this->field('affiliate_id', array('Badge.id' => $id));
	}
}
?>