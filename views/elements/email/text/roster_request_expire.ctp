Dear <?php echo $person['first_name']; ?>,

Your request to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php echo $team['name']; ?> as a <?php
echo Configure::read("options.roster_role.${roster['role']}"); ?> was not responded to by a coach or captain within the allotted time, and has been removed.

<?php echo $this->element('email/text/footer'); ?>
