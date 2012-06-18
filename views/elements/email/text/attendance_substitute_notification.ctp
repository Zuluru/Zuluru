Dear <?php echo $person['first_name']; ?>,

<?php echo $captain; ?> has indicated that you are <?php
echo Configure::read("attendance_verb.$status");
?> the <?php echo $team['name']; ?> game<?php
if (isset($game)) {
	$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
	echo ' against ' . $opponent['name'] .
		" at {$game['GameSlot']['Field']['long_name']} ($url)" .
		' starting at ' . $this->ZuluruTime->time($game['GameSlot']['game_start']);
	$arg = 'game';
	$val = $game['Game']['id'];
} else {
	$arg = 'date';
	$val = $date;
}
?> on <?php
echo $this->ZuluruTime->date($date);
?>.

<?php if (isset($note)): ?>
<?php echo $note; ?>

<?php endif; ?>

IF THIS CORRECTLY REFLECTS YOUR CURRENT STATUS, YOU DO NOT NEED TO TAKE ANY ACTION AT THIS TIME. To update your status, use one of the links below, or visit the web site at any time.

<?php if ($status == ATTENDANCE_INVITED): ?>
Keep in mind that when teams are short, captains will often invite a number of people to fill in, so it's possible that even if you confirm attendance now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your captain that you are needed before the game.

<?php elseif ($status == ATTENDANCE_AVAILABLE): ?>
Remember that just because you are available for this game doesn't mean that the team will need you. The captain should indicate this by changing you to "attending" or "absent" once they know for sure, at which time you will receive another email from the system. If you do not receive this email, you may want to check with your captain through other channels.

<?php endif; ?>
<?php
$url_array = array(
	'controller' => 'games', 'action' => 'attendance_change',
	'team' => $team['id'], $arg => $val, 'person' => $person['id'], 'code' => $code);
foreach (Configure::read('attendance_verb') as $check_status => $check_verb):
	if ($status != $check_status && array_key_exists($check_status, $player_options)):
?>
If you are <?php echo $check_verb; ?> this game:
<?php
$url_array['status'] = $check_status;
echo Router::url($url_array, true);
?>


<?php
	endif;
endforeach;
?>
Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
