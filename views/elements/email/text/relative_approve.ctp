Dear <?php echo $relative['first_name']; ?>,

Your relative request to <?php echo $person['full_name']; ?> on the <?php
echo Configure::read('organization.name'); ?> web site has been approved.

<?php echo $this->element('email/text/footer'); ?>
