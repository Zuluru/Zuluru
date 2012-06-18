Dear <?php echo $captains; ?>,

This is your attendance summary for the <?php
echo $team['name']; ?> event "<?php echo $event['TeamEvent']['name'];
?>" at <?php echo $event['TeamEvent']['location_name'] .
" ({$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']})";
?> starting at <?php echo $this->ZuluruTime->time($event['TeamEvent']['start']);
?> on <?php
echo $this->ZuluruTime->date($event['TeamEvent']['date']); ?>.

<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty ($players)) {
			$text .= "\n" . count($players) . ' ' . $gender . ': ' . implode(', ', $players);
		}
	}
	if (!empty ($text)) {
		echo Configure::read("attendance.$status") . $text . "\n\n";
	}
}
?>
You can update this or check up-to-the-minute details here:
<?php
echo Router::url(array('controller' => 'team_events', 'action' => 'view', 'event' => $event['TeamEvent']['id']), true);
?>


You need to be logged into the website to update this.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
