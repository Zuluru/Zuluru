<?php
class Questionnaire extends AppModel {
	var $name = 'Questionnaire';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'affiliate_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Affiliate', 'id'),
				'message' => 'You must select a valid affiliate.',
			),
		),
	);

	var $belongsTo = array(
		'Affiliate' => array(
			'className' => 'Affiliate',
			'foreignKey' => 'affiliate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $hasMany = array(
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'questionnaire_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Question' => array(
			'className' => 'Question',
			'joinTable' => 'questionnaires_questions',
			'foreignKey' => 'questionnaire_id',
			'associationForeignKey' => 'question_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => 'QuestionnairesQuestion.sort',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function affiliate($id) {
		return $this->field('affiliate_id', array('Questionnaire.id' => $id));
	}
}
?>
