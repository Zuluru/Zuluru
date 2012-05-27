<?php
class DivisionGameslotAvailability extends AppModel {
	var $name = 'DivisionGameslotAvailability';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'Division' => array(
			'className' => 'Division',
			'foreignKey' => 'division_id',
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