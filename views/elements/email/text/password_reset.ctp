The user account <?php echo $user_name; ?> at <?php echo Configure::read('organization.name'); ?> has this e-mail address associated with it.

Someone has just requested a confirmation code to change your password.

Click <?php
echo $this->Html->url(array('controller' => 'users', 'action' => 'reset_password', $id, $code), true);
?> to confirm this request, and a new password will be created and emailed to you.

If you didn't ask for this, don't worry. Just delete this e-mail message and your password will remain unchanged.

<?php echo $this->element('email/text/footer'); ?>
