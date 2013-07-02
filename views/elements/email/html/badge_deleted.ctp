<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>Your <?php echo $person['Badge']['name']; ?> badge has been removed.</p>
<?php if (!empty($comment)): ?>
<p>The administrator provided this comment:</p>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<p>If you believe that this happened in error, please contact us.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
