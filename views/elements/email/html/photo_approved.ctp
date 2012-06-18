<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>Your photo has been approved and is now visible to other members who are logged in to this site.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
