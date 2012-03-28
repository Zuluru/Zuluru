<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has indicated that you are <?php
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
<?php if (isset($note)): ?>
<p><?php echo $note; ?></p>
<?php endif; ?>
<p><b>If this correctly reflects your current status, you do not need to take any action at this time.</b> To update your status, use one of the links below, or visit the web site at any time.</p>
<?php if ($status == ATTENDANCE_INVITED): ?>
<p>Keep in mind that when teams are short, captains will often invite a number of people to fill in, so it's possible that even if you confirm attendance now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your captain that you are needed before the game.</p>
<?php elseif ($status == ATTENDANCE_AVAILABLE): ?>
<p>Remember that just because you are available for this game doesn't mean that the team will need you. The captain should indicate this by changing you to "attending" or "absent" once they know for sure, at which time you will receive another email from the system. If you do not receive this email, you may want to check with your captain through other channels.</p>
<?php endif; ?>
<?php
$url_array = array(
	'controller' => 'games', 'action' => 'attendance_change',
	'team' => $team['id'], $arg => $val, 'person' => $person['id'], 'code' => $code);
foreach (Configure::read('attendance_verb') as $check_status => $check_verb):
	if ($status != $check_status && array_key_exists($check_status, $player_options)):
?>
<p>If you are <?php echo $check_verb; ?> this game, <?php
$url_array['status'] = $check_status;
$url = Router::url($url_array, true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<?php
	endif;
endforeach;
?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
