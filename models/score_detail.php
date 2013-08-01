<?php
class ScoreDetail extends AppModel {
	var $name = 'ScoreDetail';
	var $validate = array(
		'team_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Team', 'id'),
				'message' => 'You must select a valid team.',
			),
		),
		'play' => array(
			'valid' => array(
				'rule' => array('valid_play'),
				'message' => 'You must select a valid play.',
			),
		),
	);

	var $belongsTo = array(
		'Game' => array(
			'className' => 'Game',
			'foreignKey' => 'game_id',
		),
		'Team' => array(
			'className' => 'Team',
			'foreignKey' => 'team_id',
		)
	);

	var $hasMany = array(
		'ScoreDetailStat' => array(
			'className' => 'ScoreDetailStat',
			'foreignKey' => 'score_detail_id',
			'dependent' => true,
		)
	);

}
?>