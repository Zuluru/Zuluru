<p>Dear <?php echo $captains; ?>,</p>
<p><?php echo $person['full_name']; ?> has indicated that <?php
echo ($person['gender'] == 'Male' ? 'he' : 'she'); ?> will be <?php
echo Configure::read("event_attendance_verb.$status");
?> the <?php echo $team['name']; ?> event "<?php
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
<?php if (isset($comment)): ?>
<p><?php echo $comment; ?></p>
<?php endif; ?>
<?php if ($status == ATTENDANCE_AVAILABLE): ?>
<p>If you would like <?php echo $person['first_name']; ?> to attend this event:
<?php
$url = Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
echo $this->Html->link($url, $url);
?></p>
<p>If you know <b>for sure</b> that you don't want <?php echo $person['first_name']; ?> to attend this event:
<?php
$url = Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
echo $this->Html->link($url, $url);
?></p>
<p>Either of these actions will generate an automatic email to <?php echo $person['first_name']; ?> indicating your selection. If you are unsure whether you will want <?php echo $person['first_name']; ?> to attend this event, it's best to leave <?php echo ($person['gender'] == 'Male' ? 'him' : 'her'); ?> listed as available, and take action later when you know for sure. You can always update <?php echo ($person['gender'] == 'Male' ? 'his' : 'her'); ?> status on the web site, there is no need to keep this email for that purpose.</p>
<?php endif; ?>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
