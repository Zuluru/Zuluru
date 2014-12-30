<p>Dear <?php echo $relative['first_name']; ?>,</p>
<p><?php echo $person['full_name']; ?> has removed you as a relative on the <?php
echo Configure::read('organization.name'); ?> web site.</p>
<p>This is a notification only, there is no action required on your part.</p>
<?php echo $this->element('email/html/footer'); ?>
