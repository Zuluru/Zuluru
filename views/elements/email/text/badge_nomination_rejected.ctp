Dear <?php echo $person['NominatedBy']['first_name']; ?>,

Your nomination of <?php echo $person['Person']['full_name']; ?> for the <?php echo $person['Badge']['name']; ?> badge has been rejected.
<?php if (!empty($comment)): ?>

The administrator provided this comment:

<?php echo $comment; ?>

<?php endif; ?>

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
