Dear <?php echo $relative['first_name']; ?>,

<?php echo $person['full_name']; ?> has removed you as a relative on the <?php
echo Configure::read('organization.name'); ?> web site.

This is a notification only, there is no action required on your part.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
