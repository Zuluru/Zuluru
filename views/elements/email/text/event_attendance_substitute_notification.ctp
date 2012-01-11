Dear <?php echo $person['first_name']; ?>,

<?php echo $captain; ?> has indicated that you are <?php
echo Configure::read("event_attendance_verb.$status");
?> the <?php echo $team['name']; ?> event "<?php echo $event['TeamEvent']['name'];
?>" at <?php echo $event['TeamEvent']['location_name'] .
" ({$event['TeamEvent']['location_street']}, {$event['TeamEvent']['location_city']}, {$event['TeamEvent']['location_province']})";
?> starting at <?php echo $this->ZuluruTime->time($event['TeamEvent']['start']);
?> on <?php
echo $this->ZuluruTime->date($event['TeamEvent']['date']);
?>.

<?php if (!empty($event['TeamEvent']['description'])): ?>
<?php echo $event['TeamEvent']['description']; ?>

<?php endif; ?>

<?php if (isset($note)): ?>
<?php echo $note; ?>

<?php endif; ?>

IF THIS CORRECTLY REFLECTS YOUR CURRENT STATUS, YOU DO NOT NEED TO TAKE ANY ACTION AT THIS TIME. To update your status, use one of the links below, or visit the web site at any time.

<?php
$url_array = array(
	'controller' => 'team_events', 'action' => 'attendance_change',
	'event' => $event['TeamEvent']['id'], 'person' => $person['id'], 'code' => $code);
foreach (Configure::read('event_attendance_verb') as $check_status => $check_verb):
	if ($status != $check_status && array_key_exists($check_status, $player_options)):
?>
If you are <?php echo $check_verb; ?> this event:
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
