<p>In order to play, you need to be on the roster of a team. There may be a few options for you here:
<ul>
<?php if (Configure::read('feature.registration')): ?>
<li>Check the <?php echo $this->Html->link('registration wizard', array('controller' => 'registration', 'action' => 'wizard')); ?> to see if there are any "Individuals for Teams" events available.
Registering for one of these will reserve you a spot on a "hat" team.
Note that hat teams are typically not set up until the league is almost ready to start, so don't be surprised if you don't show up on a roster right away.</li>
<?php endif; ?>
<li>Check the <?php echo $this->Html->link('team list', array('controller' => 'teams', 'action' => 'index')); ?> for the <?php echo $this->ZuluruHtml->icon('roster_add_24.png'); ?> icon indicating "open roster" teams who are accepting requests.
Note that the prevalence and etiquette of this option may vary from one organization to another.</li>
<li>Create your own team. More information about creating and managing teams is in the <?php echo $this->Html->link (__('captains guide', true), array('controller' => 'help', 'action' => 'guide', 'captain')); ?>.</li>
<li>Your organization may also have forums where you can post messages looking for a team, or read messages from captains looking to fill out their roster.</li>
</ul>
</p>