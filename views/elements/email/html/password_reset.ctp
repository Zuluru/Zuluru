<p>The user account <?php echo $user_name; ?> at <?php echo Configure::read('organization.name'); ?> has this e-mail address associated with it.</p>
<p>Someone has just requested a confirmation code to change your password.</p>
<p>Click <?php
echo $this->Html->link(__('here', true), $this->Html->url (array('controller' => 'users', 'action' => 'reset_password', $id, $code), true));
?> to confirm this request, and a new password will be created and emailed to you.</p>
<p>If you didn't ask for this, don't worry. Just delete this e-mail message and your password will remain unchanged.</p>
<?php echo $this->element('email/html/footer'); ?>
