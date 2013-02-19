Dear <?php echo $captains; ?>,

<?php echo $person['full_name']; ?> has removed themselves from the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?>. They were previously listed as a <?php
echo Configure::read("options.roster_role.$old_role"); ?>.

This is a notification only, there is no action required on your part.

If you believe that this has happened in error, please contact <?php echo $reply; ?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team

