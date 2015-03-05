<p><?php
printf(__('Team rosters can be either open or closed. Team rosters typically default to closed, but the desired option can be selected by a coach or captain in the %s page.', true),
	(array_key_exists ('team', $this->passedArgs) ? $this->Html->link(__('edit team', true), array('controller' => 'teams', 'action' => 'edit', 'team' => $this->passedArgs['team'])) : '"' . __('edit team', true) . '"')
); ?></p>
<p><?php __('Players can always be invited to join a team by a coach or captain. Invitations must be accepted by the player before they are officially added to the roster.'); ?>
<?php if (Configure::read('options.roster_email')): ?>

<?php __('When a player is invited to join a team, an email is sent to the player with links and instructions on how to proceed.'); ?>
<?php endif; ?></p>
<p><?php __('If a team\'s roster is open, players may additionally request to join the team. Requests must be accepted by a coach or captain before the player is officially added to the roster.'); ?>
<?php if (Configure::read('options.roster_email')): ?>

<?php __('When a player requests to join a team, an email is sent to all coaches and captains with links and instructions on how to proceed.'); ?>
<?php endif; ?></p>
