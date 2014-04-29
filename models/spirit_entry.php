<?php
class SpiritEntry extends AppModel {
	var $name = 'SpiritEntry';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'person_id',
		'auto_bind' => false,
	));

	var $belongsTo = array(
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
		),
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		),
		'MostSpirited' => array(
			'className' => 'Person',
			'foreignKey' => 'most_spirited',
		)
	);
}
?>