<p>Dear <?php echo $registration['Person']['first_name']; ?>,</p>
<p>Your reservation for <?php echo $event['Event']['name']; ?> has expired.</p>
<?php if ($status == 'Unpaid'): ?>
<p>There may still be openings available, but these are now on a first-come, first-served basis. You can <?php
$url = Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true);
echo $this->Html->link(__('check availability', true), $url);
?> or to confirm your position, <?php
$url = Router::url(array('controller' => 'registrations', 'action' => 'checkout'), true);
echo $this->Html->link(__('pay for this registration', true), $url);
?>. Remember that this may fill up at any time.</p>
<?php elseif ($status == 'Waiting'): ?>
<p>This event has now filled up, and your registration has been moved to the waiting list in case a spot opens up.</p>
<p>If you are no longer interested in participating in this event, please <?php
$url = Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true);
echo $this->Html->link(__('remove yourself from the waiting list', true), $url);
?>. This will help to ensure that those who are still interested get served promptly if and when a spot opens up.</p>
<?php else: ?>
<p>As you did not confirm your position with a payment in time, your registration has been removed. If you wish to be placed on the waiting list in case a spot opens up, you can <?php
$url = Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true);
echo $this->Html->link(__('re-register for this', true), $url);
?>.</p>
<?php endif; ?>
<p>If you have any questions about this, please contact the head office.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
