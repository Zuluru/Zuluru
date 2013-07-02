<?php
class Task extends AppModel {
	var $name = 'Task';
	var $displayField = 'name';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'The name cannot be blank.',
			),
		),
		'category_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Category', 'id'),
				'message' => 'You must select a valid category.',
			),
		),
		'description' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'The description cannot be blank.',
			),
		),
		'person_id' => array(
			'inlist' => array(
				'rule' => array('inquery', 'Person', 'id'),
				'message' => 'You must select a valid person.',
			),
		),
	);

	var $belongsTo = array(
		'Category' => array(
			'className' => 'Category',
			'foreignKey' => 'category_id',
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		)
	);

	var $hasMany = array(
		'TaskSlot' => array(
			'className' => 'TaskSlot',
			'foreignKey' => 'task_id',
			'dependent' => true,
		)
	);

	function affiliate($id) {
		return $this->Category->affiliate($this->field('category_id', array('Task.id' => $id)));
	}
}
?>