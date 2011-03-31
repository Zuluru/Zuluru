<p>Help is available in the following areas:</p>
<ul>
	<li><?php echo $this->Html->link (__('People', true), array('controller' => 'help', 'action' => 'people')); ?></li>
	<li><?php echo $this->Html->link (__('Teams', true), array('controller' => 'help', 'action' => 'teams')); ?></li>
	<li><?php echo $this->Html->link (__('Games', true), array('controller' => 'help', 'action' => 'games')); ?></li>
<?php if ($is_admin): ?>
	<li><?php echo $this->Html->link (__('Rules Engine', true), array('controller' => 'help', 'action' => 'rules')); ?></li>
<?php endif; ?>
</ul>
