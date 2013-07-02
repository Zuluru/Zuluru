Dear <?php echo $person['NominatedBy']['first_name']; ?>,

Your nomination of <?php echo $person['Person']['full_name']; ?> for the <?php echo $person['Badge']['name']; ?> badge has been approved and is now visible to other members who are logged in to this site.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
