<p>Dear <?php echo $person['NominatedBy']['first_name']; ?>,</p>
<p>Your nomination of <?php echo $person['Person']['full_name']; ?> for the <?php echo $person['Badge']['name']; ?> badge has been approved and is now visible to other members who are logged in to this site.</p>
<?php echo $this->element('email/html/footer'); ?>
