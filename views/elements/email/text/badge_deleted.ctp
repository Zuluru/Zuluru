Dear <?php echo $person['Person']['first_name']; ?>,

Your <?php echo $person['Badge']['name']; ?> badge has been removed.
<?php if (!empty($comment)): ?>

The administrator provided this comment:

<?php echo $comment; ?>

<?php endif; ?>

If you believe that this happened in error, please contact us.

<?php echo $this->element('email/text/footer'); ?>
