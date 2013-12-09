<p>Dear <?php echo $relative['first_name']; ?>,</p>
<p>Your relative request to <?php echo $person['full_name']; ?> on the <?php
echo Configure::read('organization.name'); ?> web site has been approved.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
