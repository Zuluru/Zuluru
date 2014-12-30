<p>Dear <?php echo $person['NominatedBy']['first_name']; ?>,</p>
<p>Your nomination of <?php echo $person['Person']['full_name']; ?> for the <?php echo $person['Badge']['name']; ?> badge has been rejected.</p>
<?php if (!empty($comment)): ?>
<p>The administrator provided this comment:</p>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<?php echo $this->element('email/html/footer'); ?>
