<?php
class BadgesPerson extends AppModel {
	var $name = 'BadgesPerson';

	var $belongsTo = array(
		'Badge' => array(
			'className' => 'Badge',
			'foreignKey' => 'badge_id',
			'conditions' => '',
		),
		'Person' => array(
			'className' => 'Person',
			'foreignKey' => 'person_id',
			'conditions' => '',
		),
		'NominatedBy' => array(
			'className' => 'Person',
			'foreignKey' => 'nominated_by',
			'conditions' => '',
		),
		'ApprovedBy' => array(
			'className' => 'Person',
			'foreignKey' => 'approved_by',
			'conditions' => '',
		),
	);

	/**
	 * Overridden paginateCount method
	 */
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$this->recursive = $recursive;
		return $this->find('count', array_merge(
			array(
				'conditions' => $conditions,
				'fields' => 'DISTINCT BadgesPerson.person_id',
			), $extra));
	}

	function affiliate($id) {
		return $this->Badge->affiliate($this->field('badge_id', array('BadgesPerson.id' => $id)));
	}
}
?>
