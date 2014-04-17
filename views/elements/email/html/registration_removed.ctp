<p>Dear <?php echo $registration['Person']['first_name']; ?>,</p>
<p>You registered for <?php echo $event['Event']['name']; ?> on <?php echo $this->ZuluruTime->date($registration['Registration']['created']); ?>, but have not yet paid.</p>
<p>This event has now filled up, and in accordance with <?php echo Configure::read('organization.short_name'); ?> policy, your registration has been removed. If you wish to be placed on the waiting list in case a spot opens up, you can <?php
$url = Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true);
echo $this->Html->link(__('re-register for this', true), $url);
?>.</p>
<p>If you have any questions about this, please contact the head office.</p>
<p>Thanks,
<br /><?php echo Configure::read('email.admin_name'); ?>
<br /><?php echo Configure::read('organization.short_name'); ?> web team</p>
