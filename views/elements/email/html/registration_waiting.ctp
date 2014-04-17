<p>Dear <?php echo $registration['Person']['first_name']; ?>,</p>
<p>You registered for <?php echo $event['Event']['name']; ?> on <?php echo $this->ZuluruTime->date($registration['Registration']['created']); ?>, but have not yet paid.</p>
<p>This event has now filled up, and in accordance with <?php echo Configure::read('organization.short_name'); ?> policy, your registration has been moved to the waiting list in case a spot opens up.</p>
<?php if (Configure::read('registration.reservation_time') > 0): ?>
<p>If you are still interested in participating in this event, please monitor your email; if a spot opens up, you will be notified by email, but it will only be held for you for <?php echo Configure::read('registration.reservation_time'); ?> hours.</p>
<?php endif; ?>
<p>If you are no longer interested in participating in this event, please <?php
$url = Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true);
echo $this->Html->link(__('remove yourself from the waiting list', true), $url);
?>. This will help to ensure that those who are still interested get served promptly if and when a spot opens up.</p>
<p>If you have any questions about this, please contact the head office.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
