<?php
class LeagueGameslotAvailability extends AppModel {
	var $name = 'LeagueGameslotAvailability';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'League' => array(
			'className' => 'League',
			'foreignKey' => 'league_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'GameSlot' => array(
			'className' => 'GameSlot',
			'foreignKey' => 'game_slot_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>