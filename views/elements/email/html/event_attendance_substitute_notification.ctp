<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has indicated that you are <?php
echo Configure::read("event_attendance_verb.$status");
?> the <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> event "<?php
$url = Router::url(array('controller' => 'team_events', 'action' => 'view', 'event' => $event['TeamEvent']['id']), true);
echo $this->Html->link($event['TeamEvent']['name'], $url);
?>" at <?php echo $event['TeamEvent']['location_name'];
$address = "{$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']}";
$link_address = strtr ($address, ' ', '+');
echo ' (' . $this->Html->link($address, "http://maps.google.com/maps?q=$link_address") . ')';
?> starting at <?php echo $this->ZuluruTime->time($event['TeamEvent']['start']);
?> on <?php
echo $this->ZuluruTime->date($event['TeamEvent']['date']);
?>.</p>
<?php if (!empty($event['TeamEvent']['description'])): ?>
<p><?php echo $event['TeamEvent']['description']; ?></p>
<?php endif; ?>
<?php if (isset($note)): ?>
<p><?php echo $note; ?></p>
<?php endif; ?>
<p><b>If this correctly reflects your current status, you do not need to take any action at this time.</b> To update your status, use one of the links below, or visit the web site at any time.</p>
<?php
$url_array = array(
	'controller' => 'team_events', 'action' => 'attendance_change',
	'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code);
foreach (Configure::read('event_attendance_verb') as $check_status => $check_verb):
	if ($status != $check_status && array_key_exists($check_status, $player_options)):
?>
<p>If you are <?php echo $check_verb; ?> this event:
<?php
$url_array['status'] = $check_status;
$url = Router::url($url_array, true);
echo $this->Html->link($url, $url);
?></p>
<?php
	endif;
endforeach;
?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
