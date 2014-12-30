Dear <?php echo $registration['Person']['first_name']; ?>,

You registered for <?php echo $event['Event']['name']; ?> on <?php echo $this->ZuluruTime->date($registration['Registration']['created']); ?>, but have not yet paid.

This event has now filled up, and in accordance with <?php echo Configure::read('organization.short_name'); ?> policy, your registration has been removed. If you wish to be placed on the waiting list in case a spot opens up, you can re-register for this at
<?php echo Router::url(array('controller' => 'events', 'action' => 'view', 'event' => $event['Event']['id']), true); ?>


If you have any questions about this, please contact the head office.

<?php echo $this->element('email/text/footer'); ?>
