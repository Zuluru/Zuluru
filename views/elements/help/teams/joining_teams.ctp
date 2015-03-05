<p><?php __('In order to play, you need to be on the roster of a team. There may be a few options for you here:'); ?>
<ul>
<?php if (Configure::read('feature.registration')): ?>
<li><?php
printf(__('Check the %s to see if there are any "Individuals for Teams" events available. Registering for one of these will reserve you a spot on a "hat" team. Note that hat teams are typically not set up until the league is almost ready to start, so don\'t be surprised if you don\'t show up on a roster right away.', true),
	$this->Html->link(__('registration wizard', true), array('controller' => 'registration', 'action' => 'wizard'))
); ?></li>
<?php endif; ?>
<li><?php
printf(__('Check the %s for the %s icon indicating "open roster" teams who are accepting requests. Note that the prevalence and etiquette of this option may vary from one organization to another.', true),
	$this->Html->link(__('team list', true), array('controller' => 'teams', 'action' => 'index')),
	$this->ZuluruHtml->icon('roster_add_24.png')
); ?></li>
<li><?php
printf(__('Create your own team. More information about creating and managing teams is in the %s.', true),
	$this->Html->link (__('captains guide', true), array('controller' => 'help', 'action' => 'guide', 'captain'))
);
?></li>
<li><?php __('Your organization may also have forums where you can post messages looking for a team, or read messages from captains looking to fill out their roster.'); ?></li>
</ul>
</p>
