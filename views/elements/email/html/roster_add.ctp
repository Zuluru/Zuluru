<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>You have been added to the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.$role"); ?>.</p>
<p><?php echo $team['name']; ?> plays in the <?php echo $this->element('email/division'); ?>.</p>
<p>If you believe that this has happened in error, please contact <?php echo $reply; ?>.</p>
<?php echo $this->element('email/html/footer'); ?>
