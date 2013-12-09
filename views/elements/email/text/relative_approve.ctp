Dear <?php echo $relative['first_name']; ?>,

Your relative request to <?php echo $person['full_name']; ?> on the <?php
echo Configure::read('organization.name'); ?> web site has been approved.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
