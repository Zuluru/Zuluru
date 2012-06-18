Dear <?php echo $person['first_name']; ?>,

<?php echo $captain; ?> has removed the invitation to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
