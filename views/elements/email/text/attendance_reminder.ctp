Dear <?php echo $person['first_name']; ?>,

<?php if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED): ?>
You have not yet indicated your attendance<?php else: ?>
You are currently listed as <?php echo Configure::read("attendance.$status"); ?>
<?php endif; ?> for the <?php
echo $team['name']; ?> game against <?php echo $opponent['name']; ?> at <?php
$url = Router::url(array('controller' => 'fields', 'action' => 'view', 'field' => $game['GameSlot']['Field']['id']), true);
echo "{$game['GameSlot']['Field']['long_name']} ($url)";
?> starting at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?>.

<?php if ($status == ATTENDANCE_INVITED): ?>
The captain has invited you to play in this game. However, when teams are short, captains will often invite a number of people to fill in, so it's possible that even if you confirm now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your captain that you are needed before the game.

<?php endif; ?>
<?php if ($status == ATTENDANCE_INVITED || in_array($person['TeamsPerson']['role'], Configure::read('regular_roster_roles'))): ?>
If you are able to play:
<?php
echo Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
?>


<?php elseif ($status != ATTENDANCE_ATTENDING && !in_array($person['TeamsPerson']['role'], Configure::read('regular_roster_roles'))): ?>
If you are available to play:
<?php
echo Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_AVAILABLE), true);
?>


<?php endif; ?>
If you are unavailable to play:
<?php
echo Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
?>


Note that you can set your attendance in advance, giving your captain advance notice of vacations or other planned absences. You need to be logged into the website to update this.
<?php
echo Router::url(array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['id']), true);
?>


Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
