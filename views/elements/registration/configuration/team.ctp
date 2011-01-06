<?php
// Get the list of leagues
$league = ClassRegistry::init ('League');
$leagues = $league->find('list', array(
		'conditions' => array('OR' => array(
				'close > NOW()',
				'id' => 1,
		)),
));

echo $this->Form->input('team_league', array(
		'label' => 'League',
		'options' => $leagues,
		'empty' => 'Create no team records',
		'after' => $this->Html->para (null, __('Registrations performed through this event will create team records in this league.', true)),
		'required' => true,	// Since this is not in the model validation list, we must force this
));
echo $this->Form->input('level_of_play', array(
		'size' => 70,
		'after' => $this->Html->para (null, __('Indicate the expected level(s) of play in this league.', true)),
));
echo $this->Form->input('ask_status', array(
		'label' => 'Team status',
		'type' => 'checkbox',
		'after' => $this->Html->para (null, __('Ask whether team rosters will be open or closed during registration?', true)),
));

if (Configure::read('feature.region_preference')) {
	echo $this->Form->input('ask_region', array(
			'label' => 'Region preference',
			'type' => 'checkbox',
			'after' => $this->Html->para (null, __('Ask teams for their regional preference during registration?', true)),
	));
}
?>