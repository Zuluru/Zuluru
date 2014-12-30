Dear <?php echo $person['Person']['first_name']; ?>,

Your <?php echo Configure::read('organization.short_name'); ?> account has been approved.

<?php if (!empty($person['Person']['user_name'])): ?>
You may now log in to the system with the username <?php echo $person['Person']['user_name']; ?> and the password you specified when you created your account.

<?php endif; ?>
<?php echo $this->element('email/text/footer'); ?>
