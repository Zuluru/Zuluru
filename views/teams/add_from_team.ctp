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
			$cannot[] = $this->Form->input("player.{$person['id']}", array(
					'label' => $this->element('people/block', compact('person')),
					'type' => 'checkbox',
					'hiddenField' => false,
					'after' => ' ' . $this->Html->image('help.png', array('title' => $person['can_add'], 'alt' => '?')),
			));
		}
	}

	if (!empty ($cannot)) {
		echo $this->Html->para('error-message',
				sprintf(__('Notice: The following players are currently INELIGIBLE to participate on this roster. This is typically because they do not have a current membership. They are not allowed to play with this team until this is corrected. Hover your mouse over the %s to see the specific reason why.', true),
				$this->Html->image('help.png', array('alt' => '?'))));
		echo $this->Html->para('error-message', __('They can still be invited to join, but will not be able to accept the invitation until this is resolved.', true));
		echo implode ('', $cannot);
	}

	echo $this->Form->end(__('Invite', true));
}
?>

</div>
