A user account with this e-mail address has been created for you on the <?php echo Configure::read('organization.name'); ?> web site (<?php
echo $this->Html->url('/', true);
?>).

Your new user name is: <?php echo $user[$user_model][$user_field]; ?>


Your new password is: <?php echo $user[$user_model]['passwd']; ?>


After you login, you can change your user name and other profile details at <?php
echo $this->Html->url(array('controller' => 'people', 'action' => 'edit'), true);
?>, and change your password <?php
echo $this->Html->url(array('controller' => 'users', 'action' => 'change_password'), true);
?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
