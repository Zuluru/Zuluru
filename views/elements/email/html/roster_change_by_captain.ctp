<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has changed your role on the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> from <?php
echo Configure::read("options.roster_role.$old_role"); ?> to <?php
echo Configure::read("options.roster_role.$role"); ?>.</p>
<p>This is a notification only, there is no action required on your part.</p>
<p>If you believe that this has happened in error, please contact <?php echo $reply; ?>.</p>
<?php echo $this->element('email/html/footer'); ?>
