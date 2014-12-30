<p>Dear <?php echo $captains; ?>,</p>
<p>You invited <?php echo $person['full_name']; ?> to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?>.</p>
<p>This invitation has not yet been responded to by the player, and will expire <?php echo $days; ?> days from now. An email has been sent to remind them, but you might want to get in touch directly as well.</p>
<p>Please be advised that players are NOT considered a part of a team roster until your invitation to join has been accepted. The <?php
echo $team['name']; ?> roster must be completed <?php
$min = Configure::read("sport.roster_requirements.{$division['ratio']}");
if ($min > 0): ?>(minimum of <?php echo $min; ?> rostered players) <?php endif; ?>by the team roster deadline (<?php
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have been accepted by the captain.</p>
<?php echo $this->element('email/html/footer'); ?>
