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
		'empty' => 'Not associated with any division',
		'after' => $this->Html->para (null, __('This is only used internally to improve event/division linkage.', true)),
));
echo $this->Form->input('Event.level_of_play', array(
		'size' => 70,
		'after' => $this->Html->para (null, __('Indicate the expected level(s) of play in this division.', true)),
));
?>
