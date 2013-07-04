<?php
class ScoreDetail extends AppModel {
	var $name = 'ScoreDetail';

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