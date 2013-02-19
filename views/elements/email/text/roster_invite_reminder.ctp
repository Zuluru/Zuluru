Dear <?php echo $person['first_name']; ?>,

This is a reminder that you have been invited to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?>.

<?php echo $team['name']; ?> plays in the <?php echo $this->element('email/division'); ?>.

More details about <?php echo $team['name']; ?> may be found at
<?php echo Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true); ?>


We ask that you please accept or decline this invitation at your earliest convenience. The invitation will expire <?php echo $days; ?> days from now.

If you accept the invitation, you will be added to the team's roster and your contact information will be made available to the team captain.

Note that, before accepting the invitation, you must be a registered member of <?php echo Configure::read('organization.short_name'); ?>.

Accept the invitation here:
<?php echo Router::url(array('controller' => 'teams', 'action' => 'roster_accept', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true); ?>


If you decline the invitation you will be removed from this team's roster and your contact information will not be made available to the captain. This protocol is in accordance with the <?php
echo Configure::read('organization.short_name'); ?> Privacy Policy.

Decline the invitation here:
<?php echo Router::url(array('controller' => 'teams', 'action' => 'roster_decline', 'team' => $team['id'], 'person' => $person['id'], 'code' => $code), true); ?>


Please be advised that players are NOT considered a part of a team roster until they have accepted a captain's invitation to join. The <?php
echo $team['name']; ?> roster must be completed (minimum of <?php
echo Configure::read("sport.roster_requirements.{$division['ratio']}"); ?> rostered players) by the team roster deadline (<?php
$date_format = array_shift (Configure::read('options.date_formats'));
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have accepted the captain's invitation.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
