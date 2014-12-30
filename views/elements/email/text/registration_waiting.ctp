Dear <?php echo $registration['Person']['first_name']; ?>,

You registered for <?php echo $event['Event']['name']; ?> on <?php echo $this->ZuluruTime->date($registration['Registration']['created']); ?>, but have not yet paid.

This event has now filled up, and in accordance with <?php echo Configure::read('organization.short_name'); ?> policy, your registration has been moved to the waiting list in case a spot opens up.

<?php if (Configure::read('registration.reservation_time') > 0): ?>
If you are still interested in participating in this event, please monitor your email; if a spot opens up, you will be notified by email, but it will only be held for you for <?php echo Configure::read('registration.reservation_time'); ?> hours.
<?php endif; ?>

If you are no longer interested in participating in this event, please remove yourself from the waiting list at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true); ?>

This will help to ensure that those who are still interested get served promptly if and when a spot opens up.

If you have any questions about this, please contact the head office.

<?php echo $this->element('email/text/footer'); ?>
