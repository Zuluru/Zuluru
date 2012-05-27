<?php
class FranchisesTeam extends AppModel {
	var $name = 'FranchisesTeam';

	var $belongsTo = array(
		'Franchise' => array(
			'className' => 'Franchise',
			'foreignKey' => 'franchise_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
}
?>
