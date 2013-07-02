<?php
class TaskSlot extends AppModel {
	var $name = 'TaskSlot';
	var $validate = array(
		'task_date' => array(
			'date' => array(
				'rule' => array('date'),
				'message' => 'You must provide a valid task date.',
			),
			'range' => array(
				'rule' => array('indateconfig', 'gameslot'),
				'message' => 'You must provide a valid task date.',
			),
		),
		'task_start' => array(
			'time' => array(
				'rule' => array('time'),
				'message' => 'You must select a valid start time.',
			),
		),
		'task_end' => array(
			'time' => array(
				'rule' => array('time'),
				'message' => 'You must select a valid end time.',
			),
		),
		'approved' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Indicate whether the task assignment has been approved.',
			),
		),
		'number_of_slots' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Number of slots must be a number. Use 1 to create a single slot.',
			),
		),
		'days_to_repeat' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Days to repeat must be a number. Use 1 to create slots on a single day.',
			),
		),
	);

	var $belongsTo = array(
		'Task' => array(
			'className' => 'Task',
			'foreignKey' => 'task_id',
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
		),
		'ApprovedBy' => array(
			'className' => 'Person',
			'foreignKey' => 'approved_by',
		),
	);

	function affiliate($id) {
		return $this->Task->affiliate($this->field('task_id', array('TaskSlot.id' => $id)));
	}
}
?>