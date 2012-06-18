<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Add Player', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams add_player">
<h2><?php echo sprintf(__('Add %s', true), __('Player', true)) . ': ' . $team['Team']['name'];?></h2>

<?php
if (empty ($event['Registration'])) {
	echo $this->Html->para(null, "All players registered for {$event['Event']['name']} are already on this roster.");
} else {
	echo $this->Html->para(null, "The following players have registered and paid for {$event['Event']['name']} but are not on the current roster:");
	echo $this->Form->create(false, array('url' => array('action' => 'add_from_event', 'team' => $team['Team']['id'])));
	echo $this->Form->hidden('event', array('value' => $event['Event']['id']));

	$cannot = array();
	foreach ($event['Registration'] as $registration) {
		if ($registration['can_add'] === true) {
			echo $this->Form->input("player.{$registration['Person']['id']}", array(
					'label' => $this->element('people/block', array('person' => $registration['Person'])),
					'type' => 'checkbox',
					'hiddenField' => false,
			));
		} else {
			$cannot[] = $this->Html->tag('span', $this->element('people/block', array('person' => $registration['Person'])), array('title' => $registration['can_add']));
		}
	}

	echo $this->Form->end(__('Add', true));
	if (!empty ($cannot)) {
		echo $this->Html->para(null, __('The following players cannot be added to the roster. Hover your mouse over a name to see the reason why.', true));
		echo $this->Html->para(null, implode (', ', $cannot) . '.');
	}
}
?>

</div>
