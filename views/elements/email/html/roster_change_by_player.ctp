<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has changed their role on the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> from <?php
echo Configure::read("options.roster_role.$old_role"); ?> to <?php
echo Configure::read("options.roster_role.$role"); ?>.</p>
<p>This is a notification only, there is no action required on your part.</p>
<p>If you believe that this has happened in error, please contact <?php echo $reply; ?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>

