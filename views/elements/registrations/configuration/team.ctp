<?php
// Get the list of divisions
$division = ClassRegistry::init ('Division');
$divisions = $division->find('all', array(
		'conditions' => array(
			'Division.close > NOW()',
			'League.affiliate_id' => array_keys($affiliates),
		),
		'contain' => 'League',
));

echo $this->Form->input('Event.division_id', array(
		'label' => 'Division',
		'options' => Set::combine ($divisions, '{n}.Division.id', '{n}.Division.full_league_name'),
		'empty' => 'Create no team records',
		'after' => $this->Html->para (null, __('Registrations performed through this event will create team records in this division.', true)),
));
echo $this->Form->input('Event.level_of_play', array(
		'size' => 70,
		'after' => $this->Html->para (null, __('Indicate the expected level(s) of play in this division.', true)),
));
echo $this->Form->input('Event.ask_status', array(
		'label' => 'Team status',
		'type' => 'checkbox',
		'after' => $this->Html->para (null, __('Ask whether team rosters will be open or closed during registration?', true)),
));

if (Configure::read('feature.region_preference')) {
	echo $this->Form->input('Event.ask_region', array(
			'label' => 'Region preference',
			'type' => 'checkbox',
			'after' => $this->Html->para (null, __('Ask teams for their regional preference during registration?', true)),
	));
}

echo $this->Form->input('Event.ask_attendance', array(
		'label' => 'Attendance tracking',
		'type' => 'checkbox',
		'after' => $this->Html->para (null, __('Ask teams whether they want to use attendance tracking during registration?', true)),
));
?>
