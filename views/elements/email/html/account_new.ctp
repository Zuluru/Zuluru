<p>A user account with this e-mail address has been created for you on the <?php
echo $this->Html->link(Configure::read('organization.name') . ' web site', $this->Html->url('/', true));
?>.</p>
<p>Your new user name is: <?php echo $user[$user_model][$user_field]; ?></p>
<p>Your new password is: <?php echo $user[$user_model]['passwd']; ?></p>
<p>After you login, you can change your user name and other profile details <?php
echo $this->Html->link (__('here', true), $this->Html->url(array('controller' => 'people', 'action' => 'edit'), true));
?>, and change your password <?php
echo $this->Html->link (__('here', true), $this->Html->url(array('controller' => 'users', 'action' => 'change_password'), true));
?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
