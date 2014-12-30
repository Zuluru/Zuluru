<p>Dear <?php echo $relative['first_name']; ?>,</p>
<p>Your relative request to <?php echo $person['full_name']; ?> on the <?php
echo Configure::read('organization.name'); ?> web site has been approved.</p>
<?php echo $this->element('email/html/footer'); ?>
