<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED): ?>
You have not yet indicated your attendance<?php else: ?>
You are currently listed as <?php echo Configure::read("attendance.$status"); ?>
<?php endif; ?> for the <?php
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
<p>If you are able to attend, <?php
$url = Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<p>If you are unavailable to attend, <?php
$url = Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
echo $this->Html->link(__('click here', true), $url);
?>.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
