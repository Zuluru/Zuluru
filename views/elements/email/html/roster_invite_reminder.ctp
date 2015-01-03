<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>This is a reminder that you have been invited to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?>.</p>
<p><?php echo $team['name']; ?> plays in the <?php echo $this->element('email/division'); ?>.</p>
<p>We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire <?php echo $days; ?> days from now.</p>
<p>If you accept the invitation, you will be added to the team's roster and your contact information will be made available to the team coaches and captains.</p>
<p>Note that, before accepting the invitation, you must be a registered member of <?php echo Configure::read('organization.short_name'); ?>.</p>
<p><?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_accept', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Accept the invitation', true), $url);
?></p>
<p>If you decline the invitation you will be removed from this team's roster and your contact information will not be made available to the coaches or captains. This protocol is in accordance with the <?php
echo Configure::read('organization.short_name'); ?> Privacy Policy.</p>
<p><?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_decline', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Decline the invitation', true), $url);
?></p>
<p>Please be advised that players are NOT considered a part of a team roster until they have accepted the invitation to join. The <?php
echo $team['name']; ?> roster must be completed <?php
$min = Configure::read("sport.roster_requirements.{$division['ratio']}");
if ($min > 0): ?>(minimum of <?php echo $min; ?> rostered players) <?php endif; ?>by the team roster deadline (<?php
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have accepted the invitation.</p>
<?php echo $this->element('email/html/footer'); ?>
