The user account <?php echo $user_name; ?> at <?php echo Configure::read('organization.name'); ?> has this e-mail address associated with it.

Someone has just requested a new password.

Your new password is: <?php echo $password; ?>


After you login, you can change it at <?php
echo $this->Html->url(array('controller' => 'users', 'action' => 'change_password'), true);
?>


If you didn't ask for this, don't worry. You are seeing this message, not 'them'. If this was an error just log in with your new password.
