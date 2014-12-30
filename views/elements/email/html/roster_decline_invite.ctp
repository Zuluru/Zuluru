<p>Dear <?php echo $captains; ?>,</p>
<p>Your invitation for <?php echo $person['full_name']; ?> to join the roster of the <?php
echo Configure::read('organization.name'); ?> team <?php
$url = Router::url(array('controller' => 'teams', 'action' => 'view', 'team' => $team['id']), true);
echo $this->Html->link($team['name'], $url);
?> has been declined.</p>
<p>You need to be logged into the website to update this.</p>
<?php echo $this->element('email/html/footer'); ?>
