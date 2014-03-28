<?php
if ($year < date('Y')) {
	$year = date('Y');
}
$year_end = date('Y', strtotime ($event['Event']['membership_ends']));
if ($year_end != $year) {
	$year = "$year/$year_end";
}
?>
<p>Dear <?php echo $person['Person']['first_name']; ?>,</p>
<p>Welcome to <?php echo Configure::read('organization.short_name'); ?>!</p>
<p>If you're renewing a past membership, we welcome you back. If you're new to the Club, we welcome you in! Being a member of our Club is a unique experience and one that we hope that you will enjoy for years to come.</p>
<p>Your membership runs from <?php
echo $this->ZuluruTime->date($event['Event']['membership_begins']); ?> to <?php
echo $this->ZuluruTime->date($event['Event']['membership_ends']); ?>. If you have any questions regarding your membership, league concerns, or otherwise please feel free to contact us at <a href="mailto:<?php echo Configure::read('email.admin_email'); ?>"><?php echo Configure::read('email.admin_email'); ?></a>.</p>
<p>Have a great season in <?php echo $year; ?>!</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
