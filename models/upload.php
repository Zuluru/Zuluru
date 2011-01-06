<?php
class Upload extends AppModel {
	var $name = 'Upload';

	var $belongsTo = array(
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'other_id',
			'conditions' => array('type' => 'person'),
			'fields' => '',
			'order' => ''
		)
	);
}
?>
