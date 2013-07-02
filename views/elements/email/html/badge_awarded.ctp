<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>You have been awarded the <?php echo $person['Badge']['name']; ?> badge<?php
if ($person['Badge']['category'] == 'nominated'):
?>, which you were nominated for by <?php echo $person['NominatedBy']['full_name'];
endif; ?>. <?php echo $person['Badge']['description']; ?> This badge is now visible to other members who are logged in to this site.</p>
<?php if (!empty($person['BadgesPerson']['reason'])): ?>
<p><?php if ($person['Badge']['category'] == 'nominated'): ?>
When they nominated you, <?php echo $person['NominatedBy']['first_name']; ?>
<?php else: ?>
When they assigned this badge, the administrator
<?php endif; ?> provided this reason:</p>
<p><?php echo $person['BadgesPerson']['reason']; ?></p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
