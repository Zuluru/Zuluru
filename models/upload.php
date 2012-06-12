<?php
class Upload extends AppModel {
	var $name = 'Upload';

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'UploadType' => array(
			'className' => 'UploadType',
			'foreignKey' => 'type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $validate = array(
		'valid_from' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'event'),
				'message' => 'You must provide a valid date.',
			),
		),
		'valid_until' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'event'),
				'message' => 'You must provide a valid date.',
			),
			'later' => array(
				'rule' => array('later'),
				'message' => 'End date must be after the start date',
			),
		),
	);

	function later() {
		if($this->data[$this->alias]['valid_from'] > $this->data[$this->alias]['valid_until']) {
			$this->invalidate('end');   
			return false;
		}
		return true;
	}
}
?>
