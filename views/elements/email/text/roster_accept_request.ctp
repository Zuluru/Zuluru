Dear <?php echo $person['first_name']; ?>,

<?php echo $captain; ?> has accepted your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.$role"); ?>.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
