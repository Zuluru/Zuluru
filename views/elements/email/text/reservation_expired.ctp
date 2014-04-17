Dear <?php echo $registration['Person']['first_name']; ?>,

Your reservation for <?php echo $event['Event']['name']; ?> has expired.

<?php if ($status == 'Unpaid'): ?>
There may still be openings available, but these are now on a first-come, first-served basis. You can check availability at
<?php echo Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true); ?>

or to confirm your position, pay for this registration at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'checkout'), true); ?>

Remember that this may fill up at any time.

If you are no longer interested in participating in this event, please unregister at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true); ?>

This will help to ensure that those who are still interested get served promptly.
<?php elseif ($status == 'Waiting'): ?>
This event has now filled up, and your registration has been moved to the waiting list in case a spot opens up.

If you are no longer interested in participating in this event, please remove yourself from the waiting list at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true); ?>

This will help to ensure that those who are still interested get served promptly if and when a spot opens up.
<?php else: ?>
As you did not confirm your position with a payment in time, your registration has been removed. If you wish to be placed on the waiting list in case a spot opens up, you can re-register for this at
<?php echo Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true); ?>

<?php endif; ?>

If you have any questions about this, please contact the head office.

Thanks,
<?php echo Configure::read('email.admin_name'); ?>

<?php echo Configure::read('organization.short_name'); ?> web team
