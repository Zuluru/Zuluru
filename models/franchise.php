<?php
class Franchise extends AppModel {
	var $name = 'Franchise';
	var $displayField = 'name';
	var $order = 'Franchise.name';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'person_id',
		'auto_bind' => false,
	));

	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Franchise name must not be blank.',
			),
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'There is already a franchise by that name.',
			),
		),
		'website' => array(
			'url' => array(
				'rule' => array('url'),
				'allowEmpty' => true,
				'message' => 'Enter a valid URL, or leave blank.',
			),
		),
	);

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Team' => array(
			'className' => 'Team',
			'joinTable' => 'franchises_teams',
			'with' => 'FranchisesTeam',
			'foreignKey' => 'franchise_id',
			'associationForeignKey' => 'team_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => 'Team.id DESC',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function readByPlayerId($id) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$conditions = array(
			'Franchise.person_id' => $id,
		);

		$this->contain();
		$franchises = $this->find('all', array(
				'conditions' => $conditions,
		));

		return $franchises;
	}
}
?>
