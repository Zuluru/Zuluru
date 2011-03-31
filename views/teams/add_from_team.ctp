<?php
$this->Html->addCrumb (__('Team', true));
$this->Html->addCrumb (__('Add Player', true));
$this->Html->addCrumb ($team['Team']['name']);
?>

<div class="teams add_player">
<h2><?php echo sprintf(__('Add %s', true), __('Player', true)) . ': ' . $team['Team']['name'];?></h2>

<?php
if (empty ($old_team['Person'])) {
	echo $this->Html->para(null, "All players from {$old_team['Team']['name']} ({$old_team['League']['season']}) are already on your roster.");
} else {
	echo $this->Html->para(null, "The following players were on the roster for {$old_team['Team']['name']} in {$old_team['League']['season']} but are not on your current roster:");
	echo $this->Form->create(false, array('url' => array('action' => 'add_from_team', 'team' => $team['Team']['id'])));
	echo $this->Form->hidden('team', array('value' => $old_team['Team']['id']));

	$cannot = array();
	foreach ($old_team['Person'] as $person) {
		if ($person['can_add'] === true) {
			echo $this->Form->input("player.{$person['id']}", array(
					'label' => $this->element('people/block', compact('person')),
					'type' => 'checkbox',
					'hiddenField' => false,
			));
		} else {
			$cannot[] = $this->Html->tag('span', $this->element('people/block', compact('person')), array('title' => $person['can_add']));
		}
	}

	echo $this->Form->end(__('Invite', true));
	if (!empty ($cannot)) {
		echo $this->Html->para(null, __('The following players cannot be added to your roster. Hover your mouse over a name to see the reason why.', true));
		echo $this->Html->para(null, implode (', ', $cannot) . '.');
	}
}
?>

</div>
