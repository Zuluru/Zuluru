Dear <?php echo $person['first_name']; ?>,

Your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?> was not responded to by a captain within the allotted time, and has been removed.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
