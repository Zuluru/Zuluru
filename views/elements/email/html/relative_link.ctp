<p>Dear <?php echo $relative['first_name']; ?>,</p>
<p><?php echo $person['full_name']; ?> has indicated on the <?php
echo Configure::read('organization.name'); ?> web site that you are related to them. You have the opportunity to accept or decline this.</p>
<p>If you accept, <?php echo $person['first_name']; ?> will be granted access to see your schedule and perform various tasks in the system on your behalf. You can always remove this later on if you change your mind.</p>
<p><?php
$url = Router::url(array('controller' => 'people', 'action' => 'approve_relative', 'person' => $relative['id'], 'relative' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Accept the request', true), $url);
?></p>
<p>If you decline, <?php echo $person['first_name']; ?> will not have any additional access to your account.</p>
<p><?php
$url = Router::url(array('controller' => 'people', 'action' => 'remove_relative', 'person' => $relative['id'], 'relative' => $person['id'], 'code' => $code), true);
echo $this->Html->link(__('Decline the request', true), $url);
?></p>
<?php echo $this->element('email/html/footer'); ?>
