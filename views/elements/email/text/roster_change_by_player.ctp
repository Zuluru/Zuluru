Dear <?php echo $captains; ?>,

<?php echo $person['full_name']; ?> has changed their position on the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> from <?php
echo Configure::read("options.roster_position.$old_position"); ?> to <?php
echo Configure::read("options.roster_position.$position"); ?>.

This is a notification only, there is no action required on your part.

If you believe that this has happened in error, please contact <?php echo $reply; ?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team

