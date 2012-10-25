<?php
class Franchise extends AppModel {
	var $name = 'Franchise';
	var $displayField = 'name';
	var $order = 'Franchise.name';

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
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
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

	var $hasAndBelongsToMany = array(
		'Person' => array(
			'className' => 'Person',
			'joinTable' => 'franchises_people',
			'foreignKey' => 'franchise_id',
			'associationForeignKey' => 'person_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
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

	function readByPlayerId($id, $conditions = array()) {
		// Check for invalid users
		if ($id === null) {
			return array();
		}

		$this->Person->contain(array('Franchise' => compact('conditions')));
		$person = $this->Person->read(null, $id);

		return $person['Franchise'];
	}

	function affiliate($id) {
		return $this->field('affiliate_id', array('Franchise.id' => $id));
	}
}
?>
