Dear <?php echo $person['first_name']; ?>,

You have not yet indicated your attendance for the <?php
echo $team['name']; ?> game against <?php echo $opponent['name']; ?> at <?php
echo $this->ZuluruTime->time($game['GameSlot']['game_start']); ?> on <?php
echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
?>.

<?php if (!in_array($person['TeamsPerson']['position'], Configure::read('playing_roster_positions'))): ?>
The captain has invited you to play in this game. However, when teams are short, captains will often invite a number of people to fill in, so it's possible that even if you confirm now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your captain that you are needed before the game.

<?php endif; ?>
If you are able to play:
<?php
echo Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
?>


If you are unavailable to play:
<?php
echo Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
?>


Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
