<p>Team rosters can be either open or closed. Team rosters typically default to closed, but the desired option can be selected by a captain in the <?php
if (array_key_exists ('team', $this->passedArgs)) {
	echo $this->Html->link('edit team', array('controller' => 'teams', 'action' => 'edit', 'team' => $this->passedArgs['team']));
} else {
	echo '"edit team"';
}
?> page.</p>
<p>Players can always be invited to join a team by a captain. Invitations must be accepted by the player before they are officially added to the roster.
<?php if (Configure::read('options.roster_email')): ?>
When a player is invited to join a team, an email is sent to the player with links and instructions on how to proceed.
<?php endif; ?></p>
<p>If a team's roster is open, players may additionally request to join the team. Requests must be accepted by the captain before the player is officially added to the roster.
<?php if (Configure::read('options.roster_email')): ?>
When a player requests to join a team, an email is sent to the captain(s) with links and instructions on how to proceed.
<?php endif; ?></p>
