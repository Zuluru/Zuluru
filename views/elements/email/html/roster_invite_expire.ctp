<p>Dear <?php echo $captains; ?>,</p>
<p>Your invitation to <?php echo $person['full_name']; ?> to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?> was not responded to by the player in the allotted time, and has been removed.</p>
<?php echo $this->element('email/html/footer'); ?>
