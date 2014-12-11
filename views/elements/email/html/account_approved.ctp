<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>Your <?php echo Configure::read('organization.short_name'); ?> account has been approved.</p>
<?php if (!empty($person['Person']['user_name'])): ?>
<p>You may now log in to the system with the username <?php echo $person['Person']['user_name']; ?> and the password you specified when you created your account.</p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
