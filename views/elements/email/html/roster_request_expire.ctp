<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>Your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?> was not responded to by a coach or captain within the allotted time, and has been removed.</p>
<?php echo $this->element('email/html/footer'); ?>
