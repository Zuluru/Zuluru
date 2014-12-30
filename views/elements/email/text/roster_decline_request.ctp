Dear <?php echo $person['first_name']; ?>,

<?php echo $captain; ?> has declined your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?>.

<?php echo $this->element('email/text/footer'); ?>
