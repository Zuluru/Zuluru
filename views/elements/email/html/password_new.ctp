<p>The user account <?php echo $user_name; ?> at <?php echo Configure::read('organization.name'); ?> has this e-mail address associated with it.</p>
<p>Someone has just requested a new password.</p>
<p>Your new password is: <?php echo $password; ?></p>
<p>After you login, you can change it <?php
echo $this->Html->link (__('here', true), $this->Html->url(array('controller' => 'users', 'action' => 'change_password'), true));
?>.</p>
<p>If you didn't ask for this, don't worry. You are seeing this message, not 'them'. If this was an error just log in with your new password.</p>
