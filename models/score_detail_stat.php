<?php
class ScoreDetailStat extends AppModel {
	var $name = 'ScoreDetailStat';
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'ScoreDetail' => array(
			'className' => 'ScoreDetail',
			'foreignKey' => 'score_detail_id',
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
		),
		'StatType' => array(
			'className' => 'StatType',
			'foreignKey' => 'stat_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
?>