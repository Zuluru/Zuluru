<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has invited you to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> as a <?php
echo Configure::read("options.roster_role.$role"); ?>.</p>
<p><?php echo $team['name']; ?> plays in the <?php echo $this->element('email/division'); ?>.</p>
<p>We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire after a couple of weeks.</p>
<p>If you accept the invitation, you will be added to the team's roster and your contact information will be made available to the team captain.</p>
<p>Note that, before accepting the invitation, you must be a registered member of <?php echo Configure::read('organization.short_name'); ?>.</p>
<?php if (isset($accept_warning)): ?>
<p>The system has also generated this warning which must be resolved before you can accept this invitation:
<br /><?php echo $accept_warning; ?></p>
<?php endif; ?>
<p><?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_accept', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Accept the invitation', true), $url);
?></p>
<p>If you decline the invitation you will be removed from this team's roster and your contact information will not be made available to the captain. This protocol is in accordance with the <?php
echo Configure::read('organization.short_name'); ?> Privacy Policy.</p>
<p><?php
$url = Router::url(array('controller' => 'teams', 'action' => 'roster_decline', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Decline the invitation', true), $url);
?></p>
<p>Please be advised that players are NOT considered a part of a team roster until they have accepted a captain's invitation to join. The <?php
echo $team['name']; ?> roster must be completed (minimum of <?php
echo Configure::read("sport.roster_requirements.{$division['ratio']}"); ?> rostered players) by the team roster deadline (<?php
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have accepted the captain's invitation.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
