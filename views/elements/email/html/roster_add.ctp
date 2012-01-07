<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>You have been added to the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_position.$position"); ?>.</p>
<p><?php echo $team['name']; ?> plays in the <?php echo $this->element('email/division'); ?>.</p>
<p>More details about <?php echo $team['name']; ?> may be found at
<?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($url, $url);
?></p>
<p>If you believe that this has happened in error, please contact <?php echo $reply; ?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
