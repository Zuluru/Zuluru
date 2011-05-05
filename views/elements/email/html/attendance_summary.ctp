<p>Dear <?php echo $captains; ?>,</p>
<p>This is your attendance summary for the <?php
echo $team['name']; ?> game against <?php echo $opponent['name']; ?> at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?>.</p>
<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty ($players)) {
			$text .= '<br />' . count($players) . ' ' . $gender . ': ' . implode(', ', $players);
		}
	}
	if (!empty ($text)) {
		echo $this->Html->para(null, Configure::read("attendance.$status") . $text);
	}
}
?>
<p>You can update this or check up-to-the-minute details here:
<?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']), true);
echo $this->Html->link($url, $url);
?></p>
<p>You need to be logged into the website to update this.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
