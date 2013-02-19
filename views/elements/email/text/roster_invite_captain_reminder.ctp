Dear <?php echo $captains; ?>,

You invited <?php echo $person['full_name']; ?> to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?>.

This invitation has not yet been responded to by the player, and will expire <?php echo $days; ?> days from now. An email has been sent to remind them, but you might want to get in touch directly as well.

Please be advised that players are NOT considered a part of a team roster until your invitation to join has been accepted. The <?php
echo $team['name']; ?> roster must be completed (minimum of <?php
echo Configure::read("sport.roster_requirements.{$division['ratio']}"); ?> rostered players) by the team roster deadline (<?php
$date_format = array_shift (Configure::read('options.date_formats'));
echo $this->ZuluruTime->date(Division::rosterDeadline($division));
?>), and all team members must have been accepted by the captain.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
