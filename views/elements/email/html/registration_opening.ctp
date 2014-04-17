<p>Dear <?php echo $registration['Person']['first_name']; ?>,</p>
<p>You are first on the waiting list for <?php echo $event['Event']['name']; ?>, and a spot has opened up. This spot has been reserved for you<?php
if (Configure::read('registration.reservation_time') > 0): ?>
 for <?php echo Configure::read('registration.reservation_time'); ?> hours (your reservation will expire and your registration will be deleted at <?php echo $this->Time->format('g:iA', $expiry); ?> on <?php echo $this->Time->format('F j', $expiry); ?>)<?php
endif; ?>.</p>
<p>To confirm your position, simply <?php
$url = Router::url(array('controller' => 'registrations', 'action' => 'checkout'), true);
echo $this->Html->link(__('pay for this registration', true), $url);
?>.</p>
<p>If you are no longer interested in participating in this event, please <?php
$url = Router::url(array('controller' => 'registrations', 'action' => 'unregister', 'registration' => $registration['Registration']['id']), true);
echo $this->Html->link(__('remove yourself from the waiting list', true), $url);
?>. This will help to ensure that those who are still interested get offered the spot promptly.</p>
<p>If you have any questions about this, please contact the head office.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
