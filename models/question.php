<?php
class Question extends AppModel {
	var $name = 'Question';
	var $displayField = 'question';
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
		'anonymous' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'required' => false,
				'allowEmpty' => true,
				'message' => 'Indicate whether responses to this question will be anonymous.',
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

	// Return the field name to be used for a questionnaire form element.
	// Questionnaires save their data in a different format from normal
	// forms, and the field names need to match in the form, validation
	// array, and when we load saved data for editing.
	static function _formName($question, $answer = null) {
		$name = "q{$question['id']}";
		if ($answer !== null) {
			$name .= "a{$answer['id']}";
		}
		return $name;
	}

	function affiliate($id) {
		return $this->field('affiliate_id', array('Question.id' => $id));
	}
}
?>