<?php
class Skill extends AppModel {
	var $name = 'Skill';
	var $displayField = 'skill_level';
	var $validate = array(
		'skill_level' => array(
			'inlist' => array(
				'rule' => array('inconfig_ifenabled', 'options.skill'),
				'message' => 'You must select a skill level between 1 and 10.',
			),
		),
		'year_started' => array(
			'range' => array(
				'rule' => array('indateconfig_ifenabled', 'started'),
				'message' => 'Year started must be after 1986. If you started before then, just use 1986!',
			),
		),
	);
}
?>