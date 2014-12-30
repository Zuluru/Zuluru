<p>Dear <?php echo $person['first_name']; ?>,</p>
<p><?php echo $captain; ?> has removed the invitation to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?>.</p>
<?php echo $this->element('email/html/footer'); ?>
