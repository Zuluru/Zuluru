Dear <?php echo $captains; ?>,

This is your attendance summary for the <?php
echo $team['name']; ?> game against <?php echo $opponent['name']; ?> at <?php
$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
echo "{$game['GameSlot']['Field']['long_name']} ($url)";
?> starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?>.

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
echo Router::url(array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']), true);
?>


You need to be logged into the website to update this.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
