<p>The user account <?php echo $user_name; ?> at <?php echo Configure::read('organization.name'); ?> has this e-mail address associated with it.</p>
<p>Someone has just requested a confirmation code to change your password.</p>
<p>You can now create a new password by clicking <?php
echo $this->Html->link(__('here', true), $this->Html->url (array('controller' => 'users', 'action' => 'reset_password', $id, $code), true));
?>.</p>
<p>If you didn't ask for this, don't worry. Just delete this e-mail message and your password will remain unchanged.</p>
