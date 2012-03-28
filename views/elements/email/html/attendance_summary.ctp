<p>Dear <?php echo $captains; ?>,</p>
<p>This is your attendance summary for the <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> game against <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
echo $this->Html->link($opponent['name'], $url);
?> at <?php
$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
echo $this->Html->link($game['GameSlot']['Field']['long_name'], $url);
?> starting at <?php
$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
echo $this->Html->link($this->ZuluruTime->time($game['GameSlot']['game_start']), $url);
?> on <?php
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
<p>You can <?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']), true);
echo $this->Html->link(__('update this or check up-to-the-minute details', true), $url);
?>. You need to be logged into the website to update this.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
