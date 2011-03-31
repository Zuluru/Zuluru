<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has declined your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
