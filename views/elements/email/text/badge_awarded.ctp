Dear <?php echo $person['Person']['first_name']; ?>,

You have been awarded the <?php echo $person['Badge']['name']; ?> badge<?php
if ($person['Badge']['category'] == 'nominated'):
?>, which you were nominated for by <?php echo $person['NominatedBy']['full_name'];
endif; ?>. <?php echo $person['Badge']['description']; ?> This badge is now visible to other members who are logged in to this site.

<?php if (!empty($person['BadgesPerson']['reason'])): ?>
<?php if ($person['Badge']['category'] == 'nominated'): ?>
When they nominated you, <?php echo $person['NominatedBy']['first_name']; ?>
<?php else: ?>
When they assigned this badge, the administrator<?php endif; ?> provided this reason:

<?php echo $person['BadgesPerson']['reason']; ?>

<?php endif; ?>

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
