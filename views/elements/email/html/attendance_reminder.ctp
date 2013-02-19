<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED): ?>
You have not yet indicated your attendance<?php else: ?>
You are currently listed as <?php echo Configure::read("attendance.$status"); ?>
<?php endif; ?> for the <?php
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
<?php if ($status == ATTENDANCE_INVITED): ?>
<p>The captain has invited you to play in this game. However, when teams are short, captains will often invite a number of people to fill in, so it's possible that even if you confirm now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your captain that you are needed before the game.</p>
<?php endif; ?>
<?php if ($status == ATTENDANCE_INVITED || in_array($person['TeamsPerson']['role'], Configure::read('regular_roster_roles'))): ?>
<p>If you are able to play, <?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<?php elseif ($status != ATTENDANCE_ATTENDING && !in_array($person['TeamsPerson']['role'], Configure::read('regular_roster_roles'))): ?>
<p>If you are available to play, <?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_AVAILABLE), true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<?php endif; ?>
<p>If you are unavailable to play, <?php
$url = Router::url(array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id'], 'game' => $game['Game']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<p>Note that you can <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'attendance', 'team' => $team['id']), true);
echo $this->Html->link(__('set your attendance in advance', true), $url);
?>, giving your captain advance notice of vacations or other planned absences. You need to be logged into the website to update this.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
