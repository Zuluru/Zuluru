<?php
class ScoreEntry extends AppModel {
	var $name = 'ScoreEntry';
	var $displayField = 'team_id';
	var $actsAs = array('WhoDidIt' => array(
		'created_by_field' => 'person_id',
		'auto_bind' => false,
	));

	var $validate = array(
		'score_for' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 99),
				'message' => 'Scores must be in the range 0-99',
			),
		),
		'score_against' => array(
			'range' => array(
				'rule' => array('inclusive_range', 0, 99),
				'message' => 'Scores must be in the range 0-99',
			),
		),
		'defaulted' => array(
			'inlist' => array(
				'rule' => array('inlist', array('no', 'us', 'them')),
			),
		),
	);

	var $belongsTo = array(
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>