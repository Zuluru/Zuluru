Dear <?php echo $person['first_name']; ?>,

<?php if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED): ?>
You have not yet indicated your attendance<?php else: ?>
You are currently listed as <?php echo Configure::read("attendance.$status"); ?>
<?php endif; ?> for the <?php
echo $team['name']; ?> event "<?php echo $event['TeamEvent']['name'];
?>" at <?php echo $event['TeamEvent']['location_name'] .
" ({$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']})";
?> starting at <?php echo $this->ZuluruTime->time($event['TeamEvent']['start']);
?> on <?php
echo $this->ZuluruTime->date($event['TeamEvent']['date']);
?>.

<?php if (!empty($event['TeamEvent']['description'])): ?>
<?php echo $event['TeamEvent']['description']; ?>

<?php endif; ?>

If you are able to attend:
<?php
echo Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ATTENDING), true);
?>


If you are unavailable to attend:
<?php
echo Router::url(array('controller' => 'team_events', 'action' => 'attendance_change', 'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code, 'status' => ATTENDANCE_ABSENT), true);
?>


Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
