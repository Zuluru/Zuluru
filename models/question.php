<?php
class Question extends AppModel {
	var $name = 'Question';
	var $displayField = 'question';
	var $validate = array(
		'question' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'type' => array(
			'inlist' => array(
				'rule' => array('inconfig', 'options.question_types'),
			),
		),
	);

	var $hasMany = array(
		'Answer' => array(
			'className' => 'Answer',
			'foreignKey' => 'question_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => 'Answer.sort',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Questionnaire' => array(
			'className' => 'Questionnaire',
			'joinTable' => 'questionnaires_questions',
			'foreignKey' => 'question_id',
			'associationForeignKey' => 'questionnaire_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

}
?>