<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>You requested to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?>.</p>
<p>This request has not yet been responded to by a captain, and will expire <?php echo $days; ?> days from now. An email has been sent to remind them, but you might want to get in touch directly as well.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
