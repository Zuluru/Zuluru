Dear <?php echo $registration['Person']['first_name']; ?>,

You are first on the waiting list for <?php echo $event['Event']['name']; ?>, and a spot has opened up. This spot has been reserved for you<?php
if (Configure::read('registration.reservation_time') > 0): ?>
 for <?php echo Configure::read('registration.reservation_time'); ?> hours (your reservation will expire and your registration will be deleted at <?php echo $this->Time->format('g:iA', $expiry); ?> on <?php echo $this->Time->format('F j', $expiry); ?>)<?php
endif; ?>.

To confirm your position, simply pay for this registration at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'checkout'), true); ?>


If you are no longer interested in participating in this event, please remove yourself from the waiting list at
<?php echo Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true); ?>

This will help to ensure that those who are still interested get offered the spot promptly.

If you have any questions about this, please contact the head office.

<?php echo $this->element('email/text/footer'); ?>
