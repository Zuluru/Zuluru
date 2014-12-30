<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>Your <?php echo $person['Badge']['name']; ?> badge has been removed.</p>
<?php if (!empty($comment)): ?>
<p>The administrator provided this comment:</p>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<p>If you believe that this happened in error, please contact us.</p>
<?php echo $this->element('email/html/footer'); ?>
