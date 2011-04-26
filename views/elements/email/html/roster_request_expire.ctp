<p>Dear <?php echo $person['first_name']; ?>,</p>
<p>Your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_position.${roster['position']}"); ?> was not responded to by a captain within the allotted time, and has been removed.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
