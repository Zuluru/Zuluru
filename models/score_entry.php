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
				'rule' => array('valid_score', 0, 99),
				'message' => 'Scores must be in the range 0-99',
			),
		),
		'score_against' => array(
			'range' => array(
				'rule' => array('valid_score', 0, 99),
				'message' => 'Scores must be in the range 0-99',
			),
		),
		'status' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.game_status'),
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