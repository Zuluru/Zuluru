<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has indicated that <?php
echo ($person['gender'] == 'Male' ? 'he' : 'she'); ?> will be <?php
echo Configure::read("attendance_verb.$status");
?> the <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> game<?php
if (isset($game)) {
	$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $opponent['id']), true);
	echo ' against ' . $this->Html->link($opponent['name'], $url);

	$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
	echo ' at ' . $this->Html->link($game['GameSlot']['Field']['long_name'], $url);

	$url = Router::url(array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']), true);
	echo ' starting at ' . $this->Html->link($this->ZuluruTime->time($game['GameSlot']['game_start']), $url);

	$arg = 'game';
	$val = $game['Game']['id'];
} else {
	$arg = 'date';
	$val = $date;
}
?> on <?php
echo $this->ZuluruTime->date($date);
?>.</p>
<?php if (isset($comment)): ?>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<?php if ($status == ATTENDANCE_AVAILABLE): ?>
<p>If you need <?php echo $person['first_name']; ?> for this game:
<?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], $arg => $val, 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
echo $this->Html->link($url, $url);
?></p>
<p>If you know <b>for sure</b> that you don't need <?php echo $person['first_name']; ?> for this game:
<?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], $arg => $val, 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
echo $this->Html->link($url, $url);
?></p>
<p>Either of these actions will generate an automatic email to <?php echo $person['first_name']; ?> indicating your selection. If you are unsure whether you will need <?php echo $person['first_name']; ?> for this game, it's best to leave <?php echo ($person['gender'] == 'Male' ? 'him' : 'her'); ?> listed as available, and take action later when you know for sure. You can always update <?php echo ($person['gender'] == 'Male' ? 'his' : 'her'); ?> status on the web site, there is no need to keep this email for that purpose.</p>
<?php endif; ?>
<p>You can also <?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']), true);
echo $this->Html->link(__('check up-to-the-minute details', true), $url);
?>. You need to be logged into the website to update this.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
