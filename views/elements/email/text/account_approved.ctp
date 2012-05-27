Dear <?php echo $person['Person']['first_name']; ?>,

Your <?php echo Configure::read('organization.short_name'); ?> account has been approved.

You may now log in to the system with the username <?php echo $person['Person']['user_name']; ?> and the password you specified when you created your account.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
