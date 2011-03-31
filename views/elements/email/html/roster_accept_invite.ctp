<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has accepted your invitation to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_position.$position"); ?>.</p>
<p>The <?php echo $team['name']; ?> roster may be accessed at
<?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($url, $url);
?></p>
<p>You need to be logged into the website to update this.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
