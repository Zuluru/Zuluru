<?php
if ($year < date('Y')) {
	$year = date('Y');
}
$year_end = date('Y', strtotime ($event['Event']['membership_ends']));
if ($year_end != $year) {
	$year = "$year/$year_end";
}
?>
Dear <?php echo $person['Person']['first_name']; ?>,

Welcome to <?php echo Configure::read('organization.short_name'); ?>!

If you're renewing a past membership, we welcome you back. If you're new to the Club, we welcome you in! Being a member of our Club is a unique experience and one that we hope that you will enjoy for years to come.

Your membership runs from <?php
echo $this->ZuluruTime->date($event['Event']['membership_begins']); ?> to <?php
echo $this->ZuluruTime->date($event['Event']['membership_ends']); ?>. If you have any questions regarding your membership, league concerns, or otherwise please feel free to contact us at <?php echo Configure::read('email.admin_email'); ?>.

Have a great season in <?php echo $year; ?>!

<?php echo $this->element('email/text/footer'); ?>
